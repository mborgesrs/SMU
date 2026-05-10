<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/PortadorModel.php';

checkAuth();

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'ID não informado']);
    exit;
}

try {
    $model = new PortadorModel();
    $portador = $model->getById($id);
    
    if ($portador) {
        echo json_encode($portador);
    } else {
        echo json_encode(['error' => 'Portador não encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
