<?php
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../helpers.php';

checkAuth();
if (!isSuperAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null;

if (!$id || !$action) {
    header('Location: companies.php');
    exit;
}

try {
    $db = getDB();
    
    switch ($action) {
        case 'toggle_setup':
            $status = $_GET['status'] ?? 0;
            $stmt = $db->prepare("UPDATE companies SET setup_paid = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            break;

        case 'update_billing':
            $status = $_GET['status'] ?? 'active';
            $stmt = $db->prepare("UPDATE companies SET billing_status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            break;
    }

    header('Location: companies.php?success=updated');
    exit;

} catch (Exception $e) {
    header('Location: companies.php?error=' . urlencode($e->getMessage()));
    exit;
}
