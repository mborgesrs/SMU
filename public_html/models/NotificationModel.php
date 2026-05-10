<?php
require_once __DIR__ . '/../db.php';

class NotificationModel {
    private $db;
    private $company_id;
    private $user_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
        $this->user_id = $_SESSION['user_id'] ?? null;
    }

    public function getActiveNotifications() {
        if (!$this->company_id) return [];

        $notifications = [];
        $today = date('Y-m-d');
        $next15 = date('Y-m-d', strtotime('+15 days'));

        // 1. Birthdays (Today)
        $stmt = $this->db->prepare("
            SELECT id, nome, dt_nascimento 
            FROM clientes 
            WHERE company_id = ? 
            AND MONTH(dt_nascimento) = MONTH(CURRENT_DATE) 
            AND DAY(dt_nascimento) = DAY(CURRENT_DATE)
        ");
        $stmt->execute([$this->company_id]);
        while ($row = $stmt->fetch()) {
            $hash = "bday_" . $row['id'] . "_" . date('Y');
            if (!$this->isDismissed($hash)) {
                $notifications[] = [
                    'tipo' => 'aniversario',
                    'titulo' => 'Aniversário Hoje! 🎂',
                    'mensagem' => 'Hoje é aniversário de ' . $row['nome'],
                    'hash' => $hash,
                    'link' => APP_URL . 'views/clientes/form.php?id=' . $row['id']
                ];
            }
        }

        // 2. Contracts Ending (Next 15 days)
        $stmt = $this->db->prepare("
            SELECT c.id, c.nf_contrato, cl.nome as cliente_nome, c.data_fim 
            FROM contratos c
            JOIN clientes cl ON c.id_cliente = cl.id
            WHERE c.company_id = ? AND c.status = 'ativo'
            AND c.data_fim BETWEEN ? AND ?
        ");
        $stmt->execute([$this->company_id, $today, $next15]);
        while ($row = $stmt->fetch()) {
            $hash = "contract_" . $row['id'] . "_" . $row['data_fim'];
            if (!$this->isDismissed($hash)) {
                $notifications[] = [
                    'tipo' => 'contrato',
                    'titulo' => 'Contrato Vencendo 📄',
                    'mensagem' => 'Contrato ' . ($row['nf_contrato'] ?: $row['id']) . ' de ' . $row['cliente_nome'] . ' vence em ' . date('d/m/Y', strtotime($row['data_fim'])),
                    'hash' => $hash,
                    'link' => APP_URL . 'views/contratos/form.php?id=' . $row['id']
                ];
            }
        }

        // 3. Titles Overdue
        $stmt = $this->db->prepare("
            SELECT f.id, f.valor, cl.nome as cliente_nome, f.dt_vencimento
            FROM financeiro f
            LEFT JOIN clientes cl ON f.id_cliente_forn = cl.id
            WHERE f.company_id = ? AND f.situacao = 'Aberto'
            AND f.dt_vencimento < ?
        ");
        $stmt->execute([$this->company_id, $today]);
        while ($row = $stmt->fetch()) {
            $hash = "overdue_" . $row['id'];
            if (!$this->isDismissed($hash)) {
                $notifications[] = [
                    'tipo' => 'financeiro_atraso',
                    'titulo' => 'Título em Atraso ⚠️',
                    'mensagem' => 'Título de R$ ' . number_format($row['valor'], 2, ',', '.') . ' (' . ($row['cliente_nome'] ?: 'S/C') . ') venceu em ' . date('d/m/Y', strtotime($row['dt_vencimento'])),
                    'hash' => $hash,
                    'link' => APP_URL . 'views/financeiro/form.php?id=' . $row['id']
                ];
            }
        }

        // 4. Titles Due Today
        $stmt = $this->db->prepare("
            SELECT f.id, f.valor, cl.nome as cliente_nome
            FROM financeiro f
            LEFT JOIN clientes cl ON f.id_cliente_forn = cl.id
            WHERE f.company_id = ? AND f.situacao = 'Aberto'
            AND f.dt_vencimento = ?
        ");
        $stmt->execute([$this->company_id, $today]);
        while ($row = $stmt->fetch()) {
            $hash = "due_" . $row['id'];
            if (!$this->isDismissed($hash)) {
                $notifications[] = [
                    'tipo' => 'financeiro_hoje',
                    'titulo' => 'Título Vence Hoje 📅',
                    'mensagem' => 'Título de R$ ' . number_format($row['valor'], 2, ',', '.') . ' (' . ($row['cliente_nome'] ?: 'S/C') . ') vence hoje',
                    'hash' => $hash,
                    'link' => APP_URL . 'views/financeiro/form.php?id=' . $row['id']
                ];
            }
        }

        return $notifications;
    }

    private function isDismissed($hash) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM notificacoes_lidas WHERE hash = ? AND company_id = ? AND user_id = ?");
        $stmt->execute([$hash, $this->company_id, $this->user_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function dismiss($hash) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO notificacoes_lidas (company_id, user_id, hash) VALUES (?, ?, ?)");
        return $stmt->execute([$this->company_id, $this->user_id, $hash]);
    }

    public function dismissAll($hashes) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT IGNORE INTO notificacoes_lidas (company_id, user_id, hash) VALUES (?, ?, ?)");
            foreach ($hashes as $hash) {
                $stmt->execute([$this->company_id, $this->user_id, $hash]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
