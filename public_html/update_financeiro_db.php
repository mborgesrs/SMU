<?php
require_once __DIR__ . '/db.php';
$db = getDB();
try {
    // Check if column exists first
    $stmt = $db->query("SHOW COLUMNS FROM financeiro LIKE 'nf_contrato'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE financeiro ADD COLUMN nf_contrato VARCHAR(255) NULL AFTER id_origem;");
        echo "Success: Column added.";
    } else {
        echo "Column already exists.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
