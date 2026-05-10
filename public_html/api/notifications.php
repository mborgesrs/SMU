<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/NotificationModel.php';

checkAuth();

$action = $_GET['action'] ?? 'get';
$model = new NotificationModel();

try {
    if ($action === 'get') {
        $notifications = $model->getActiveNotifications();
        echo json_encode(['success' => true, 'data' => $notifications]);
    } elseif ($action === 'clear') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['hashes']) && is_array($data['hashes'])) {
            $model->dismissAll($data['hashes']);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Hashes não informados']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
