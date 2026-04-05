<?php
require_once __DIR__ . '/db.php';
$db = getDB();
try {
    $rows = $db->exec("UPDATE financeiro SET nf_contrato = id_origem WHERE nf_contrato IS NULL AND id_origem IS NOT NULL");
    echo "Success: $rows rows updated with ID Origem.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
