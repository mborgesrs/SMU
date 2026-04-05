<?php
require_once __DIR__ . '/../db.php';

class ObjetoModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM objetos WHERE company_id = ? ORDER BY descricao ASC");
        $stmt->execute([$this->company_id]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM objetos WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO objetos (company_id, descricao, objeto) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['descricao'],
            $data['objeto'] ?? null
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE objetos SET descricao = ?, objeto = ? WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['descricao'],
            $data['objeto'] ?? null,
            $id,
            $this->company_id
        ]);
    }

    public function checkUsage($id) {
        $stmtCont = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE id_objeto = ? AND company_id = ?");
        $stmtCont->execute([$id, $this->company_id]);
        return $stmtCont->fetchColumn() > 0;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM objetos WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
