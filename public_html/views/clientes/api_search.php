<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../models/ClienteModel.php';

$search = $_GET['q'] ?? '';
$model = new ClienteModel();
$clientes = $model->getAll($search);

$results = array_map(function($c) {
    return [
        'id' => $c['id'],
        'nome' => $c['nome'],
        'fantasia' => $c['fantasia'] ?? '',
        'cpf_cnpj' => $c['cpf_cnpj'] ?? ''
    ];
}, array_slice($clientes, 0, 10)); // Limitar a 10 resultados para performance

echo json_encode($results);
