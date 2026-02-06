<?php
require_once __DIR__ . '/../db.php';

class ConfiguracaoModel {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getConfig($company_id = null) {
        if (!$company_id && isset($_SESSION['company_id'])) {
            $company_id = $_SESSION['company_id'];
        }
        if (!$company_id) return null;

        $stmt = $this->db->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        return $stmt->fetch();
    }

    public function update($data, $company_id = null) {
        if (!$company_id && isset($_SESSION['company_id'])) {
            $company_id = $_SESSION['company_id'];
        }
        if (!$company_id) return false;

        $sql = "UPDATE companies SET 
                razao_social = :razao_social,
                nome_fantasia = :nome_fantasia,
                cnpj = :cnpj,
                responsavel = :responsavel,
                telefone = :telefone,
                email = :email,
                cep = :cep,
                logradouro = :logradouro,
                numero = :numero,
                complemento = :complemento,
                bairro = :bairro,
                cidade = :cidade,
                uf = :uf,
                primary_color = :primary_color,
                secondary_color = :secondary_color,
                asaas_api_key = :asaas_api_key,
                asaas_environment = :asaas_environment,
                zapsign_api_token = :zapsign_api_token,
                zapsign_environment = :zapsign_environment";
        
        $params = [
            ':razao_social' => $data['razao_social'],
            ':nome_fantasia' => $data['nome_fantasia'],
            ':cnpj' => $data['cnpj'],
            ':responsavel' => $data['responsavel'],
            ':telefone' => $data['telefone'],
            ':email' => $data['email'],
            ':cep' => $data['cep'],
            ':logradouro' => $data['logradouro'],
            ':numero' => $data['numero'],
            ':complemento' => $data['complemento'],
            ':bairro' => $data['bairro'],
            ':cidade' => $data['cidade'],
            ':uf' => $data['uf'],
            ':primary_color' => $data['primary_color'] ?? '#1e293b',
            ':secondary_color' => $data['secondary_color'] ?? '#334155',
            ':asaas_api_key' => $data['asaas_api_key'] ?? null,
            ':asaas_environment' => $data['asaas_environment'] ?? 'sandbox',
            ':zapsign_api_token' => $data['zapsign_api_token'] ?? null,
            ':zapsign_environment' => $data['zapsign_environment'] ?? 'sandbox'
        ];

        if (isset($data['logotipo'])) {
            $sql .= ", logotipo = :logotipo";
            $params[':logotipo'] = $data['logotipo'];
        }

        $sql .= " WHERE id = :id";
        $params[':id'] = $company_id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
}
