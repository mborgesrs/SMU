<?php
require_once __DIR__ . '/../../models/ContratoModel.php';
require_once __DIR__ . '/../../models/FinanceiroModel.php';
require_once __DIR__ . '/../../models/ObjetoModel.php';

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$contratoModel = new ContratoModel();
$financeiroModel = new FinanceiroModel();
$objetoModel = new ObjetoModel();

$contrato = $contratoModel->getById($_GET['id']);

if (!$contrato) {
    header('Location: list.php?error=not_found');
    exit;
}

// Prepare data for Financeiro
$dataLancamento = date('Y-m-d');
$dataVencimento = date('Y-m-d', strtotime('+45 days'));

// Get object description
$objeto = $objetoModel->getById($contrato['id_objeto']);
$objetoDesc = $objeto ? $objeto['descricao'] : 'Não informado';

$tipoFinanceiro = ($contrato['natureza'] === 'pagamento') ? 'Pagar' : 'Receber';

$dataFinanceiro = [
    'data' => $dataLancamento,
    'id_cliente_forn' => $contrato['id_contratante'],
    'observacao' => "Contrato #" . $contrato['id'] . " - " . $objetoDesc . " | " . $contrato['observacoes'],
    'valor' => $contrato['valor_total'],
    'tipo' => $tipoFinanceiro,
    'id_portador' => $contrato['id_portador'],
    'id_conta' => $contrato['id_conta'],
    'id_tipopgto' => null,
    'saldo' => $contrato['valor_total'],
    'situacao' => 'Aberto',
    'dt_vencimento' => $dataVencimento,
    'id_origem' => $contrato['id'] // Linking to contract (optional but good)
];

if ($financeiroModel->create($dataFinanceiro)) {
    // Optionally update contract status or mark as "financial generated"
    // $contratoModel->updateStatus($contrato['id'], 'financeiro_gerado');
    header('Location: ../financeiro/list.php?success=created_from_contract');
} else {
    header('Location: list.php?id=' . $contrato['id'] . '&error=finance_failed');
}
exit;
