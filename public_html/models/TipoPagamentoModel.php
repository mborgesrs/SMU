<?php
require_once __DIR__ . '/../db.php';

class TipoPagamentoModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM tipos_pagamento WHERE company_id = ? ORDER BY descricao ASC");
        $stmt->execute([$this->company_id]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM tipos_pagamento WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO tipos_pagamento (company_id, descricao, ativo) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['descricao'],
            isset($data['ativo']) ? 1 : 0
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE tipos_pagamento SET descricao = ?, ativo = ? WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['descricao'],
            isset($data['ativo']) ? 1 : 0,
            $id,
            $this->company_id
        ]);
    }

    public function checkUsage($id) {
        $stmtFin = $this->db->prepare("SELECT COUNT(*) FROM financeiro WHERE id_tipopgto = ? AND company_id = ?");
        $stmtFin->execute([$id, $this->company_id]);
        return $stmtFin->fetchColumn() > 0;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM tipos_pagamento WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
