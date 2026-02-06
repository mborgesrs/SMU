<?php
$config = [
    'title' => 'Tipos de Pagamento',
    'model' => 'TipoPagamentoModel',
    'columns' => [
        ['label' => 'Descrição', 'field' => 'descricao'],
        ['label' => 'Status', 'field' => 'ativo', 'format' => 'boolean']
    ]
];
require_once __DIR__ . '/../_templates/generic_list.php';
?>
