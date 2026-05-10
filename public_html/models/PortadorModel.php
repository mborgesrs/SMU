<?php
require_once __DIR__ . '/../db.php';

class PortadorModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM portadores WHERE company_id = ? ORDER BY nome ASC");
        $stmt->execute([$this->company_id]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM portadores WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO portadores (company_id, nome, conta, agencia, numero, perc_juros) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['conta'] ?? null,
            $data['agencia'] ?? null,
            $data['numero'] ?? null,
            $data['perc_juros'] ?? 0
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE portadores SET nome = ?, conta = ?, agencia = ?, numero = ?, perc_juros = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['conta'] ?? null,
            $data['agencia'] ?? null,
            $data['numero'] ?? null,
            $data['perc_juros'] ?? 0,
            $id,
            $this->company_id
        ]);
    }

    public function checkUsage($id) {
        $stmtFin = $this->db->prepare("SELECT COUNT(*) FROM financeiro WHERE id_portador = ? AND company_id = ?");
        $stmtFin->execute([$id, $this->company_id]);
        if ($stmtFin->fetchColumn() > 0) return true;

        $stmtCont = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE id_portador = ? AND company_id = ?");
        $stmtCont->execute([$id, $this->company_id]);
        if ($stmtCont->fetchColumn() > 0) return true;

        return false;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM portadores WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
