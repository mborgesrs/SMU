<?php
/**
 * Redirect old PDF requests to the new unified HTML print page
 * to avoid "Template not found" errors and fix the 36-page issue.
 */
require_once __DIR__ . '/../config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    header("Location: /views/clientes/imprimir_cliente.php?contrato_id=$id");
    exit;
} else {
    header("Location: /views/dashboard.php");
    exit;
}
