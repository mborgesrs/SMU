<?php
require_once __DIR__ . '/../db.php';

class ClienteModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll($search = '') {
        $sql = "SELECT * FROM clientes WHERE company_id = :company_id";
        $params = [':company_id' => $this->company_id];

        if (!empty($search)) {
            $sql .= " AND (nome LIKE :s1 OR fantasia LIKE :s2 OR cpf_cnpj LIKE :s3 OR rg LIKE :s4)";
            $searchParam = "%$search%";
            $params[':s1'] = $searchParam;
            $params[':s2'] = $searchParam;
            $params[':s3'] = $searchParam;
            $params[':s4'] = $searchParam;
        }

        $sql .= " ORDER BY nome ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $sql = "INSERT INTO clientes (
            company_id, nome, fantasia, estado_civil, contato, cep, endereco, numero, complemento, 
            bairro, municipio, uf, telefone, celular, email, cpf_cnpj, rg, dt_nascto, tipo_pessoa, 
            ie, ativo, divisao, cd_pais, insc_mun, perc_comissao,
            nit_pis_pasep, nome_pai, nome_mae, naturalidade, observacoes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);
        if ($stmt->execute([
            $this->company_id,
            $data['nome'],
            $data['fantasia'] ?? null,
            $data['estado_civil'] ?? null,
            $data['contato'] ?? null,
            $data['cep'] ?? null,
            $data['endereco'] ?? null,
            $data['numero'] ?? null,
            $data['complemento'] ?? null,
            $data['bairro'] ?? null,
            $data['municipio'] ?? null,
            $data['uf'] ?? null,
            $data['telefone'] ?? null,
            $data['celular'] ?? null,
            $data['email'] ?? null,
            $data['cpf_cnpj'] ?? null,
            $data['rg'] ?? null,
            $data['dt_nascto'] ?? null,
            $data['tipo_pessoa'] ?? 'Fisica',
            $data['ie'] ?? null,
            isset($data['ativo']) ? 1 : 0,
            $data['divisao'] ?? 'clientes',
            $data['cd_pais'] ?? 'BR',
            $data['insc_mun'] ?? null,
            $data['perc_comissao'] ?? 0,
            $data['nit_pis_pasep'] ?? null,
            $data['nome_pai'] ?? null,
            $data['nome_mae'] ?? null,
            $data['naturalidade'] ?? null,
            $data['observacoes'] ?? null
        ])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        $sql = "UPDATE clientes SET 
            nome = ?, fantasia = ?, estado_civil = ?, contato = ?, cep = ?, endereco = ?, 
            numero = ?, complemento = ?, bairro = ?, municipio = ?, uf = ?, 
            telefone = ?, celular = ?, email = ?, cpf_cnpj = ?, rg = ?, dt_nascto = ?, 
            tipo_pessoa = ?, ie = ?, ativo = ?, divisao = ?, cd_pais = ?, 
            insc_mun = ?, perc_comissao = ?,
            nit_pis_pasep = ?, nome_pai = ?, nome_mae = ?, naturalidade = ?, observacoes = ?
            WHERE id = ? AND company_id = ?";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['nome'],
            $data['fantasia'] ?? null,
            $data['estado_civil'] ?? null,
            $data['contato'] ?? null,
            $data['cep'] ?? null,
            $data['endereco'] ?? null,
            $data['numero'] ?? null,
            $data['complemento'] ?? null,
            $data['bairro'] ?? null,
            $data['municipio'] ?? null,
            $data['uf'] ?? null,
            $data['telefone'] ?? null,
            $data['celular'] ?? null,
            $data['email'] ?? null,
            $data['cpf_cnpj'] ?? null,
            $data['rg'] ?? null,
            $data['dt_nascto'] ?? null,
            $data['tipo_pessoa'] ?? 'Fisica',
            $data['ie'] ?? null,
            isset($data['ativo']) ? 1 : 0,
            $data['divisao'] ?? 'clientes',
            $data['cd_pais'] ?? 'BR',
            $data['insc_mun'] ?? null,
            $data['perc_comissao'] ?? 0,
            $data['nit_pis_pasep'] ?? null,
            $data['nome_pai'] ?? null,
            $data['nome_mae'] ?? null,
            $data['naturalidade'] ?? null,
            $data['observacoes'] ?? null,
            $id,
            $this->company_id
        ]);
    }

    public function checkUsage($id) {
        $stmtFin = $this->db->prepare("SELECT COUNT(*) FROM financeiro WHERE id_cliente_forn = ? AND company_id = ?");
        $stmtFin->execute([$id, $this->company_id]);
        if ($stmtFin->fetchColumn() > 0) return true;

        $stmtCont = $this->db->prepare("SELECT COUNT(*) FROM contratos WHERE id_contratante = ? AND company_id = ?");
        $stmtCont->execute([$id, $this->company_id]);
        if ($stmtCont->fetchColumn() > 0) return true;

        return false;
    }

    public function delete($id) {
        if ($this->checkUsage($id)) {
            throw new Exception("Cadastro não pode ser excluído, existem movimentações para esse Id.");
        }
        $stmt = $this->db->prepare("DELETE FROM clientes WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }

    public function toggleStatus($id) {
        $stmt = $this->db->prepare("UPDATE clientes SET ativo = NOT ativo WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }
}
