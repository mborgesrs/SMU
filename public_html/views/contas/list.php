<?php
$config = [
    'title' => 'Contas Financeiras',
    'model' => 'ContaModel',
    'rowClass' => function($item) {
        return ($item['tipo'] ?? '') === 'Sintetica' ? 'font-bold bg-gray-50' : '';
    },
    'columns' => [
        ['label' => 'Código', 'field' => 'codigo'],
        ['label' => 'Descrição', 'field' => 'descricao'],
        ['label' => 'Tipo', 'field' => 'tipo'],
        ['label' => 'Saldo', 'field' => 'saldo', 'format' => 'currency'],
        ['label' => 'Status', 'field' => 'ativo', 'format' => 'boolean']
    ]
];
require_once __DIR__ . '/../_templates/generic_list.php';
?>
