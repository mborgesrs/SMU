<?php
require_once __DIR__ . '/../../models/FinanceiroModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/ContaModel.php';
require_once __DIR__ . '/../../models/TipoPagamentoModel.php';

$model = new FinanceiroModel();
$tipoPgtoModel = new TipoPagamentoModel();
$contaModel = new ContaModel();

if (!isset($_GET['id'])) {
    header('Location: list.php');
    exit;
}

$origem = $model->getById($_GET['id']);
if (!$origem) {
    header('Location: list.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valorLiquidado = (float)$_POST['valor_liquidado'];
    $dataLiquidação = $_POST['data_liquidacao'];
    
    $saldoAtual = (float)($origem['saldo'] ?? $origem['valor']);
    $novoSaldo = $saldoAtual - $valorLiquidado;
    
    // Create the liquidation entry (Entrada or Saida)
    $tipoLiquidador = ($origem['tipo'] === 'Receber') ? 'Entrada' : 'Saida';
    
    $dadosLiquidação = [
        'data' => $dataLiquidação,
        'id_cliente_forn' => $origem['id_cliente_forn'],
        'observacao' => "Liquidação parcial/total do lançamento #" . $origem['id'] . ". " . ($_POST['observacao'] ?? ''),
        'valor' => $valorLiquidado,
        'tipo' => $tipoLiquidador,
        'id_portador' => $origem['id_portador'],
        'id_conta' => $_POST['id_conta'] ?? $origem['id_conta'],
        'id_tipopgto' => $_POST['id_tipopgto'] ?? $origem['id_tipopgto'],
        'saldo' => 0, // Movements are always liquidated
        'situacao' => 'Liquidado',
        'dt_vencimento' => $origem['dt_vencimento'],
        'id_origem' => $origem['id'],
        'nf_contrato' => $origem['nf_contrato'] ?? null
    ];
    
    $model->create($dadosLiquidação);
    
    // Update the original entry
    $origem['saldo'] = $novoSaldo;
    if ($novoSaldo <= 0) {
        $origem['saldo'] = 0;
        
        // Update type and status based on the rule
        if ($origem['tipo'] === 'Receber') {
            $origem['tipo'] = 'cRecebido';
            $origem['situacao'] = 'Liquidado';
        } elseif ($origem['tipo'] === 'Pagar') {
            $origem['tipo'] = 'dPago';
            $origem['situacao'] = 'Liquidado';
        } else {
            $origem['situacao'] = 'Liquidado';
        }
    } else {
        $origem['situacao'] = 'Aberto';
    }
    
    $model->update($origem['id'], $origem);
    
    header('Location: list.php?success=liquidated');
    exit;
}

$tiposPgto = $tipoPgtoModel->getAll();
$contas = $contaModel->getAll();

$pageTitle = 'Liquidar Lançamento';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-2xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">Liquidar Lançamento</h2>

    <div class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100">
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Origem</p>
                <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($origem['tipo']); ?> #<?php echo $origem['id']; ?></p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Valor Original</p>
                <p class="text-sm font-medium text-gray-900">R$ <?php echo number_format($origem['valor'], 2, ',', '.'); ?></p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Saldo Restante</p>
                <p class="text-sm font-bold text-blue-600">R$ <?php echo number_format($origem['saldo'] ?? $origem['valor'], 2, ',', '.'); ?></p>
            </div>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Vencimento</p>
                <p class="text-sm font-medium text-gray-900"><?php echo $origem['dt_vencimento'] ? date('d/m/Y', strtotime($origem['dt_vencimento'])) : '-'; ?></p>
            </div>
        </div>

        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data da Liquidação *</label>
                    <input type="date" name="data_liquidacao" required value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor a Liquidar *</label>
                    <input type="number" name="valor_liquidado" step="0.01" required 
                        max="<?php echo $origem['saldo'] ?? $origem['valor']; ?>"
                        value="<?php echo $origem['saldo'] ?? $origem['valor']; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 font-bold">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Pagamento</label>
                    <select name="id_tipopgto" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <?php foreach ($tiposPgto as $tipo): ?>
                            <option value="<?php echo $tipo['id']; ?>" <?php echo ($origem['id_tipopgto'] ?? '') == $tipo['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tipo['descricao']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Conta / Destino</label>
                    <select name="id_conta" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?php echo $conta['id']; ?>" <?php echo ($origem['id_conta'] ?? '') == $conta['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conta['descricao']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
                <textarea name="observacao" rows="2" placeholder="Ex: Pagamento via PIX"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"></textarea>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        onclick="return confirmAction(event, 'Deseja confirmar a liquidação deste lançamento?', 'question', 'Sim, liquidar!', '#059669')"
                        class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex-1">
                    <i class="fas fa-check mr-2"></i>Confirmar Liquidação
                </button>

                <a href="form.php?id=<?php echo $origem['id']; ?>" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
