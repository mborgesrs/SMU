<?php
require_once __DIR__ . '/../db.php';

class UserModel {
    private $db;
    private $company_id;

    public function __construct($company_id = null) {
        $this->db = getDB();
        $this->company_id = $company_id ?? ($_SESSION['company_id'] ?? null);
    }

    public function getAll($search = '') {
        $sql = "SELECT * FROM users WHERE 1=1";
        $params = [];
        
        if ($this->company_id) {
            $sql .= " AND company_id = :company_id";
            $params[':company_id'] = $this->company_id;
        }

        if (!empty($search)) {
            $sql .= " AND (username LIKE :search OR email LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY username ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO users (company_id, username, password, email, role) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['email'],
            $data['role'] ?? 'admin'
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE users SET username = ?, email = ?, role = ? WHERE id = ? AND company_id = ?";
        $params = [
            $data['username'],
            $data['email'],
            $data['role'] ?? 'admin',
            $id,
            $this->company_id
        ];

        // If password is being changed
        if (!empty($data['password'])) {
            $sql = "UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ? AND company_id = ?";
            $params = [
                $data['username'],
                $data['email'],
                $data['role'] ?? 'admin',
                password_hash($data['password'], PASSWORD_DEFAULT),
                $id,
                $this->company_id
            ];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        // Prevent deleting yourself
        if ($id == $_SESSION['user_id']) return false;

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
