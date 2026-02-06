<?php
require_once __DIR__ . '/../db.php';

class ContratoModel {
    private $db;
    private $company_id;

    public function __construct() {
        $this->db = getDB();
        $this->company_id = $_SESSION['company_id'] ?? null;
    }

    public function getAll($search = '') {
        $sql = "SELECT c.*, cl.nome as contratante_nome, o.descricao as objeto_descricao
                FROM contratos c
                LEFT JOIN clientes cl ON c.id_contratante = cl.id
                LEFT JOIN objetos o ON c.id_objeto = o.id
                WHERE c.company_id = :company_id";
        
        $params = [':company_id' => $this->company_id];
        
        if (!empty($search)) {
            $sql .= " AND cl.nome LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY c.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM contratos WHERE id = ? AND company_id = ?");
        $stmt->execute([$id, $this->company_id]);
        return $stmt->fetch();
    }

    public function getItems($contratoId) {
        $sql = "SELECT ci.*, p.nome as produto_nome
                FROM contrato_items ci
                INNER JOIN contratos c ON ci.id_contrato = c.id
                LEFT JOIN products p ON ci.id_produto = p.id
                WHERE ci.id_contrato = ? AND c.company_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$contratoId, $this->company_id]);
        return $stmt->fetchAll();
    }

    public function create($data, $items) {
        try {
            $this->db->beginTransaction();

            // Sanitize currency value (R$ 1.234,56 -> 1234.56)
            $valorTotal = str_replace(['R$', ' ', '.'], '', $data['valor_total']);
            $valorTotal = str_replace(',', '.', $valorTotal);

            // Insert contract
            $sql = "INSERT INTO contratos (company_id, id_objeto, natureza, tipo, modalidade, id_contratante, 
                    dt_inicio, dt_termino, id_conta, id_portador, observacoes, valor_total, status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $this->company_id,
                $data['id_objeto'] ?? null,
                $data['natureza'],
                $data['tipo'],
                $data['modalidade'],
                $data['id_contratante'],
                $data['dt_inicio'],
                $data['dt_termino'] ?? null,
                $data['id_conta'] ?? null,
                $data['id_portador'] ?? null,
                $data['observacoes'] ?? null,
                $valorTotal,
                'pendente'
            ]);

            $contratoId = $this->db->lastInsertId();

            // Insert items
            if (!empty($items)) {
                $sqlItem = "INSERT INTO contrato_items (id_contrato, id_produto, quantidade, preco_unitario, subtotal) 
                            VALUES (?, ?, ?, ?, ?)";
                $stmtItem = $this->db->prepare($sqlItem);

                foreach ($items as $item) {
                    $stmtItem->execute([
                        $contratoId,
                        $item['id_produto'],
                        $item['quantidade'],
                        $item['preco_unitario'],
                        $item['subtotal']
                    ]);
                }
            }

            $this->db->commit();
            return $contratoId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $data) {
        // Sanitize currency value (R$ 1.234,56 -> 1234.56)
        $valorTotal = str_replace(['R$', ' ', '.'], '', $data['valor_total']);
        $valorTotal = str_replace(',', '.', $valorTotal);

        $sql = "UPDATE contratos SET id_objeto = ?, natureza = ?, tipo = ?, modalidade = ?, 
                id_contratante = ?, dt_inicio = ?, dt_termino = ?, id_conta = ?, id_portador = ?, 
                observacoes = ?, valor_total = ?, status = ? 
                WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $data['id_objeto'] ?? null,
            $data['natureza'],
            $data['tipo'],
            $data['modalidade'],
            $data['id_contratante'],
            $data['dt_inicio'],
            $data['dt_termino'] ?? null,
            $data['id_conta'] ?? null,
            $data['id_portador'] ?? null,
            $data['observacoes'] ?? null,
            $valorTotal,
            $data['status'] ?? 'criado',
            $id,
            $this->company_id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM contratos WHERE id = ? AND company_id = ?");
        return $stmt->execute([$id, $this->company_id]);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE contratos SET status = ? WHERE id = ? AND company_id = ?");
        return $stmt->execute([$status, $id, $this->company_id]);
    }
}
