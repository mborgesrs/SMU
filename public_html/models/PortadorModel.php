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
        $sql = "INSERT INTO portadores (company_id, nome, conta, agencia, numero) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['conta'] ?? null,
            $data['agencia'] ?? null,
            $data['numero'] ?? null
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE portadores SET nome = ?, conta = ?, agencia = ?, numero = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['conta'] ?? null,
            $data['agencia'] ?? null,
            $data['numero'] ?? null,
            $id,
            $this->company_id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM portadores WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
