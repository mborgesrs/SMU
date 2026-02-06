<?php
$config = [
    'title' => 'Portadores',
    'model' => 'PortadorModel',
    'columns' => [
        ['label' => 'Nome', 'field' => 'nome'],
        ['label' => 'Conta', 'field' => 'conta'],
        ['label' => 'Agência', 'field' => 'agencia'],
        ['label' => 'Número', 'field' => 'numero']
    ]
];
require_once __DIR__ . '/../_templates/generic_list.php';
?>
