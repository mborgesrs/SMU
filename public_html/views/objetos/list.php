<?php
$config = [
    'title' => 'Objetos',
    'model' => 'ObjetoModel',
    'columns' => [
        ['label' => 'Descrição', 'field' => 'descricao'],
        ['label' => 'Objeto', 'field' => 'objeto']
    ]
];
require_once __DIR__ . '/../_templates/generic_list.php';
?>
