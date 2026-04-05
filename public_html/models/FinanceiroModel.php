<?php
require_once __DIR__ . '/../db.php';

class FinanceiroModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll($filters = []) {
        $sql = "SELECT f.*, c.nome as cliente_nome, p.nome as portador_nome, 
                       ct.descricao as conta_descricao, tp.descricao as tipo_pagamento
                FROM financeiro f
                LEFT JOIN clientes c ON f.id_cliente_forn = c.id
                LEFT JOIN portadores p ON f.id_portador = p.id
                LEFT JOIN contas ct ON f.id_conta = ct.id
                LEFT JOIN tipos_pagamento tp ON f.id_tipopgto = tp.id
                WHERE f.company_id = ?";
        
        $params = [$this->company_id];
        
        if (!empty($filters['data_inicio'])) {
            $sql .= " AND f.data >= ?";
            $params[] = $filters['data_inicio'];
        }
        
        if (!empty($filters['data_fim'])) {
            $sql .= " AND f.data <= ?";
            $params[] = $filters['data_fim'];
        }
        
        if (!empty($filters['venc_inicio'])) {
            $sql .= " AND f.dt_vencimento >= ?";
            $params[] = $filters['venc_inicio'];
        }
        
        if (!empty($filters['venc_fim'])) {
            $sql .= " AND f.dt_vencimento <= ?";
            $params[] = $filters['venc_fim'];
        }

        if (!empty($filters['tipo'])) {
            $sql .= " AND f.tipo = ?";
            $params[] = $filters['tipo'];
        }

        if (!empty($filters['situacao'])) {
            if ($filters['situacao'] === 'Liquidado') {
                $sql .= " AND (f.tipo IN ('Entrada', 'Saida'))";
            } else {
                $sql .= " AND f.situacao = ?";
                $params[] = $filters['situacao'];
            }
        }

        if (!empty($filters['nf_contrato'])) {
            $sql .= " AND f.nf_contrato = ?";
            $params[] = $filters['nf_contrato'];
        }


        $sql .= " ORDER BY f.data DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM financeiro WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // Logic: Receber/Pagar defaults to Aberto, others (Entrada/Saida) are ALWAYS Liquidado
        if ($data['tipo'] === 'Receber' || $data['tipo'] === 'Pagar') {
            $situacao = $data['situacao'] ?? 'Aberto';
        } else {
            $situacao = 'Liquidado';
        }

        // If liquidated, balance must be 0
        $saldo = ($situacao === 'Liquidado') ? 0 : ($data['saldo'] ?? $data['valor']);

        $sql = "INSERT INTO financeiro (company_id, data, id_cliente_forn, observacao, valor, tipo, id_portador, id_conta, id_tipopgto, saldo, situacao, dt_vencimento, id_origem, nf_contrato) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $this->company_id,
            $data['data'],
            $data['id_cliente_forn'] ?? null,
            $data['observacao'] ?? null,
            $data['valor'],
            $data['tipo'],
            $data['id_portador'] ?? null,
            $data['id_conta'] ?? null,
            $data['id_tipopgto'] ?? null,
            $saldo,
            $situacao,
            $data['dt_vencimento'] ?? null,
            $data['id_origem'] ?? null,
            $data['nf_contrato'] ?? null
        ]);

        if ($result) {
            $this->refreshAccountBalances();
        }

        return $result;
    }

    public function update($id, $data) {
        // Logic: Receber/Pagar defaults to Aberto, others (Entrada/Saida) are ALWAYS Liquidado
        if ($data['tipo'] === 'Receber' || $data['tipo'] === 'Pagar') {
            $situacao = $data['situacao'] ?? 'Aberto';
        } else {
            $situacao = 'Liquidado';
        }

        // If liquidated, balance must be 0
        $saldo = ($situacao === 'Liquidado') ? 0 : ($data['saldo'] ?? $data['valor']);

        $sql = "UPDATE financeiro SET data = ?, id_cliente_forn = ?, observacao = ?, valor = ?, 
                tipo = ?, id_portador = ?, id_conta = ?, id_tipopgto = ?, saldo = ?, situacao = ?, dt_vencimento = ?, id_origem = ?, nf_contrato = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['data'],
            $data['id_cliente_forn'] ?? null,
            $data['observacao'] ?? null,
            $data['valor'],
            $data['tipo'],
            $data['id_portador'] ?? null,
            $data['id_conta'] ?? null,
            $data['id_tipopgto'] ?? null,
            $saldo,
            $situacao,
            $data['dt_vencimento'] ?? null,
            $data['id_origem'] ?? null,
            $data['nf_contrato'] ?? null,
            $id,
            $this->company_id
        ]);

        if ($result) {
            $this->refreshAccountBalances();
        }

        return $result;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM financeiro WHERE id = ? AND company_id = ?");
        $result = $stmt->execute([$id, $this->company_id]);
        
        if ($result) {
            $this->refreshAccountBalances();
        }
        
        return $result;
    }

    private function refreshAccountBalances() {
        // Reset all account balances for this company
        $stmt = $this->db->prepare("UPDATE contas SET saldo = 0 WHERE company_id = ?");
        $stmt->execute([$this->company_id]);

        // Calculate direct balances from financeiro (Entrada/Saida only)
        $sql = "SELECT id_conta, SUM(CASE WHEN tipo IN ('Entrada', 'cRecebido') THEN valor WHEN tipo IN ('Saida', 'dPago') THEN -valor ELSE 0 END) as total 
                FROM financeiro 
                WHERE id_conta IS NOT NULL AND company_id = ?
                GROUP BY id_conta";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->company_id]);
        $directBalances = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($directBalances as $id_conta => $valor) {
            if ($id_conta) {
                $stmtUpdate = $this->db->prepare("UPDATE contas SET saldo = ? WHERE id = ? AND company_id = ?");
                $stmtUpdate->execute([$valor, $id_conta, $this->company_id]);
            }
        }

        // Propagate balances up the hierarchy (Level 3 -> Level 2 -> Level 1)
        $sqlNames = "SELECT id, codigo FROM contas WHERE codigo IS NOT NULL AND company_id = ? ORDER BY LENGTH(codigo) DESC";
        $stmtNames = $this->db->prepare($sqlNames);
        $stmtNames->execute([$this->company_id]);
        $accounts = $stmtNames->fetchAll();

        foreach ($accounts as $acc) {
            $codigo = $acc['codigo'];
            if (substr_count($codigo, '.') >= 1) {
                $parts = explode('.', $codigo);
                array_pop($parts);
                $parentCode = implode('.', $parts);

                $stmtBalance = $this->db->prepare("SELECT saldo FROM contas WHERE id = ?");
                $stmtBalance->execute([$acc['id']]);
                $currentBalance = $stmtBalance->fetchColumn();

                if ($currentBalance != 0) {
                    $stmtAdd = $this->db->prepare("UPDATE contas SET saldo = saldo + ? WHERE codigo = ? AND company_id = ?");
                    $stmtAdd->execute([$currentBalance, $parentCode, $this->company_id]);
                }
            }
        }
    }
}
