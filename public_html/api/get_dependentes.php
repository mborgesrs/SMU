<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../models/DependenteModel.php';

$clienteId = $_GET['cliente_id'] ?? '';

if (empty($clienteId)) {
    http_response_code(400);
    echo json_encode(['erro' => true, 'message' => 'ID do cliente não fornecido']);
    exit;
}

try {
    $model = new DependenteModel();
    $dependentes = $model->getByClienteId($clienteId);
    echo json_encode($dependentes);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => true, 'message' => 'Erro ao buscar dependentes: ' . $e->getMessage()]);
}
