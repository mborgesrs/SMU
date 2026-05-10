<?php
require_once __DIR__ . '/db.php';
$db = getDB();
$res = $db->query("SELECT * FROM financeiro WHERE id = 9")->fetch(PDO::FETCH_ASSOC);
print_r($res);
