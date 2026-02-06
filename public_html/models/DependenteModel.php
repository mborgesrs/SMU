<?php
require_once __DIR__ . '/../db.php';

class DependenteModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll($search = '') {
        $sql = "SELECT d.*, c.nome as cliente_nome 
                FROM dependentes d 
                LEFT JOIN clientes c ON d.id_cliente = c.id 
                WHERE d.company_id = :company_id";
        $params = [':company_id' => $this->company_id];

        if (!empty($search)) {
            $sql .= " AND (d.nome LIKE :s1 OR d.cpf LIKE :s2 OR d.rg LIKE :s3 OR d.matricula LIKE :s4 OR c.nome LIKE :s5)";
            $searchParam = "%$search%";
            $params[':s1'] = $searchParam;
            $params[':s2'] = $searchParam;
            $params[':s3'] = $searchParam;
            $params[':s4'] = $searchParam;
            $params[':s5'] = $searchParam;
        }

        $sql .= " ORDER BY d.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM dependentes WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function getByClienteId($clienteId) {
        $sql = "SELECT d.*, c.nome as cliente_nome 
                FROM dependentes d 
                LEFT JOIN clientes c ON d.id_cliente = c.id 
                WHERE d.id_cliente = ? AND d.company_id = ? 
                ORDER BY d.nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$clienteId, $this->company_id]);
        return $stmt->fetchAll();
    }

    public function create($data) {
        $sql = "INSERT INTO dependentes (company_id, nome, id_cliente, dt_nascto, matricula, cpf, rg, dt_licenca, dt_registro, livro_civil, termo_registro, tipo_licenca, folha_registro, dt_certidao_reg_civil) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['id_cliente'],
            $data['dt_nascto'] ?? null,
            $data['matricula'] ?? null,
            $data['cpf'] ?? null,
            $data['rg'] ?? null,
            $data['dt_licenca'] ?? null,
            $data['dt_registro'] ?? null,
            $data['livro_civil'] ?? null,
            $data['termo_registro'] ?? null,
            $data['tipo_licenca'] ?? null,
            $data['folha_registro'] ?? null,
            $data['dt_certidao_reg_civil'] ?? null
        ]);
    }

    public function update($id, $data) {
        $sql = "UPDATE dependentes SET nome = ?, id_cliente = ?, dt_nascto = ?, matricula = ?, cpf = ?, rg = ?, dt_licenca = ?, dt_registro = ?, livro_civil = ?, termo_registro = ?, tipo_licenca = ?, folha_registro = ?, dt_certidao_reg_civil = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['id_cliente'],
            $data['dt_nascto'] ?? null,
            $data['matricula'] ?? null,
            $data['cpf'] ?? null,
            $data['rg'] ?? null,
            $data['dt_licenca'] ?? null,
            $data['dt_registro'] ?? null,
            $data['livro_civil'] ?? null,
            $data['termo_registro'] ?? null,
            $data['tipo_licenca'] ?? null,
            $data['folha_registro'] ?? null,
            $data['dt_certidao_reg_civil'] ?? null,
            $id,
            $this->company_id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM dependentes WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
