<?php
require_once __DIR__ . '/../db.php';

class ProductModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE company_id = ? ORDER BY nome ASC");
        $stmt->execute([$this->company_id]);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO products (company_id, nome, descricao, preco_unitario) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['descricao'] ?? null,
            $data['preco_unitario']
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE products SET nome = ?, descricao = ?, preco_unitario = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['descricao'] ?? null,
            $data['preco_unitario'],
            $id,
            $this->company_id
        ]);
    }

    public function checkUsage($id) {
        // Check if product is used in any contract items (via its contract company_id linkage check)
        $sql = "SELECT COUNT(*) FROM contrato_items ci 
                INNER JOIN contratos c ON ci.id_contrato = c.id 
                WHERE ci.id_produto = ? AND c.company_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetchColumn() > 0;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM products WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
