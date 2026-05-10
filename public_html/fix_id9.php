<?php
require_once __DIR__ . '/db.php';
$db = getDB();
$db->query("UPDATE financeiro SET saldo = 100.00, valor_juros = 0.20 WHERE id = 9");
echo "Fixed ID 9";
