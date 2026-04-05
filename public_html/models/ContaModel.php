<?php
require_once __DIR__ . '/../db.php';

class ContaModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll() {
        // Check if codigo column exists
        try {
            $stmt = $this->db->prepare("SELECT * FROM contas WHERE company_id = ? ORDER BY codigo ASC, descricao ASC");
            $stmt->execute([$this->company_id]);
        } catch (PDOException $e) {
            // Fallback if codigo column doesn't exist yet
            $stmt = $this->db->prepare("SELECT * FROM contas WHERE company_id = ? ORDER BY descricao ASC");
            $stmt->execute([$this->company_id]);
        }
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM contas WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        // Check if codigo column exists
        try {
            $sql = "INSERT INTO contas (company_id, codigo, descricao, tipo, ativo) VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->company_id,
                $data['codigo'] ?? null,
                $data['descricao'],
                $data['tipo'],
                isset($data['ativo']) ? 1 : 0
            ]);
        } catch (PDOException $e) {
            // Fallback if codigo column doesn't exist yet
            $sql = "INSERT INTO contas (company_id, descricao, tipo, ativo) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $this->company_id,
                $data['descricao'],
                $data['tipo'],
                isset($data['ativo']) ? 1 : 0
            ]);
        }
    }

    public function update($id, $data) {
        // Check if codigo column exists
        try {
            $sql = "UPDATE contas SET codigo = ?, descricao = ?, tipo = ?, ativo = ? WHERE id = ? AND company_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['codigo'] ?? null,
                $data['descricao'],
                $data['tipo'],
                isset($data['ativo']) ? 1 : 0,
                $id,
                $this->company_id
            ]);
        } catch (PDOException $e) {
            // Fallback if codigo column doesn't exist yet
            $sql = "UPDATE contas SET descricao = ?, tipo = ?, ativo = ? WHERE id = ? AND company_id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                $data['descricao'],
                $data['tipo'],
                isset($data['ativo']) ? 1 : 0,
                $id,
                $this->company_id
            ]);
        }
    }

    public function checkUsage($id) {
        $stmtFin = $this->db->prepare("SELECT COUNT(*) FROM financeiro WHERE id_conta = ? AND company_id = ?");
        $stmtFin->execute([$id, $this->company_id]);
        if ($stmtFin->fetchColumn() > 0) return true;

        $stmtCont = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE id_conta = ? AND company_id = ?");
        $stmtCont->execute([$id, $this->company_id]);
        if ($stmtCont->fetchColumn() > 0) return true;

        return false;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM contas WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
