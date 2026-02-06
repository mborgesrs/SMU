<?php
require_once __DIR__ . '/../db.php';

class CompanyModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll($search = '') {
        $sql = "SELECT c.*, (SELECT COUNT(*) FROM users WHERE company_id = c.id) as user_count 
                FROM companies c";
        $params = [];

        if (!empty($search)) {
            $sql .= " WHERE razao_social LIKE :search OR nome_fantasia LIKE :search OR cnpj LIKE :search";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY c.razao_social ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO companies (razao_social, nome_fantasia, cnpj, responsavel, email, telefone, plan, status, billing_status, setup_paid, plan_price, plan_interval) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['razao_social'],
            $data['nome_fantasia'],
            $data['cnpj'],
            $data['responsavel'],
            $data['email'],
            $data['telefone'],
            $data['plan'] ?? 'Pro',
            $data['status'] ?? 'active',
            $data['billing_status'] ?? 'active',
            $data['setup_paid'] ?? 0,
            $data['plan_price'] ?? 0,
            $data['plan_interval'] ?? 'MONTHLY'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE companies SET 
                razao_social = ?, 
                nome_fantasia = ?, 
                cnpj = ?, 
                responsavel = ?, 
                email = ?, 
                telefone = ?, 
                plan = ?, 
                status = ?,
                billing_status = ?,
                setup_paid = ?,
                plan_price = ?,
                plan_interval = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['razao_social'],
            $data['nome_fantasia'],
            $data['cnpj'],
            $data['responsavel'],
            $data['email'],
            $data['telefone'],
            $data['plan'],
            $data['status'],
            $data['billing_status'] ?? 'active',
            $data['setup_paid'] ?? 0,
            $data['plan_price'] ?? 0,
            $data['plan_interval'] ?? 'MONTHLY',
            $id
        ]);
    }

    public function updateBillingStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE companies SET billing_status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function toggleStatus($id) {
        $company = $this->getById($id);
        $newStatus = ($company['status'] === 'active') ? 'inactive' : 'active';
        $stmt = $this->db->prepare("UPDATE companies SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    public function delete($id) {
        // Prevent deleting company 1 (platform owner)
        if ($id == 1) return false;

        $stmt = $this->db->prepare("DELETE FROM companies WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
