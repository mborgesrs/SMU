<?php
require_once __DIR__ . '/../db.php';

class ContratoModeloModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll($search = '') {
        $sql = "SELECT * FROM contrato_modelos WHERE company_id = :company_id";
        $params = [':company_id' => $this->company_id];

        if (!empty($search)) {
            $sql .= " AND (nome LIKE :s1)";
            $searchParam = "%$search%";
            $params[':s1'] = $searchParam;
        }

        $sql .= " ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM contrato_modelos WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO contrato_modelos (company_id, nome, conteudo, ativo) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['conteudo'] ?? '',
            isset($data['ativo']) ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE contrato_modelos SET nome = ?, conteudo = ?, ativo = ? WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['conteudo'] ?? '',
            isset($data['ativo']) ? 1 : 0,
            $id,
            $this->company_id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM contrato_modelos WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
