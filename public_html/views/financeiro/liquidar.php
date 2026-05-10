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
    $tipoLiquidador = (in_array($origem['tipo'], ['Receber', 'Entrada', 'cRecebido'])) ? 'Entrada' : 'Saida';
    
    $dadosLiquidação = [
        'data' => $dataLiquidação,
        'id_cliente_forn' => $origem['id_cliente_forn'],
        'observacao' => "Liquidação parcial/total do lançamento #" . $origem['id'] . ". " . ($_POST['observacao'] ?? ''),
        'valor' => $valorLiquidado,
        'tipo' => $tipoLiquidador,
        'id_portador' => $origem['id_portador'],
        'id_conta' => $_POST['id_conta'] ?? $origem['id_conta'],
        'id_tipopgto' => $_POST['id_tipopgto'] ?? $origem['id_tipopgto'],
        'saldo' => 0,
        'situacao' => 'Liquidado',
        'dt_vencimento' => $origem['dt_vencimento'],
        'id_origem' => $origem['id'],
        'nf_contrato' => $origem['nf_contrato'] ?? null
    ];
    
    $model->create($dadosLiquidação);

    // Create Interest Entry if applicable
    $valorJuros = (float)($origem['valor_juros'] ?? 0);
    if ($valorJuros > 0 && $valorLiquidado >= $saldoAtual) {
        $dadosJuros = [
            'data' => $dataLiquidação,
            'id_cliente_forn' => $origem['id_cliente_forn'],
            'observacao' => "Juros/Multa referente ao lançamento #" . $origem['id'],
            'valor' => $valorJuros,
            'tipo' => $tipoLiquidador,
            'id_portador' => $origem['id_portador'],
            'id_conta' => $_POST['id_conta'] ?? $origem['id_conta'],
            'id_tipopgto' => $_POST['id_tipopgto'] ?? $origem['id_tipopgto'],
            'saldo' => 0,
            'situacao' => 'Juros/Multa',
            'dt_vencimento' => $origem['dt_vencimento'],
            'id_origem' => $origem['id'],
            'nf_contrato' => $origem['nf_contrato'] ?? null
        ];
        $model->create($dadosJuros);
    }
    
    // Update the original entry
    $origem['saldo'] = $novoSaldo;
    if ($novoSaldo <= 0) {
        $origem['saldo'] = 0;
        $origem['valor_juros'] = 0; // Clear interest once it is converted to separate entry
        
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
            <?php if (($origem['valor_juros'] ?? 0) > 0): ?>
            <div>
                <p class="text-xs uppercase text-gray-500 font-semibold tracking-wider">Juros Acumulados</p>
                <p class="text-sm font-bold text-red-600">R$ <?php echo number_format($origem['valor_juros'], 2, ',', '.'); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data da Liquidação *</label>
                    <input type="date" name="data_liquidacao" required value="<?php echo date('Y-m-d'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Principal a Liquidar *</label>
                    <input type="number" name="valor_liquidado" id="valor_liquidado" step="0.01" required 
                        max="<?php echo $origem['saldo'] ?? $origem['valor']; ?>"
                        value="<?php echo $origem['saldo'] ?? $origem['valor']; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 font-bold text-lg">
                    <p class="text-xs text-gray-500 mt-1">Este valor abaterá o saldo do título original.</p>
                </div>
            </div>

            <?php if (($origem['valor_juros'] ?? 0) > 0): ?>
                <div class="bg-amber-50 border border-amber-200 p-4 rounded-lg">
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="text-gray-600">Principal:</span>
                        <span class="font-medium" id="display_principal">R$ <?php echo number_format($origem['saldo'] ?? $origem['valor'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="text-gray-600">Juros/Multa (Automático):</span>
                        <span class="text-red-600 font-medium">R$ <?php echo number_format($origem['valor_juros'], 2, ',', '.'); ?></span>
                    </div>
                    <div class="border-t border-amber-200 pt-2 flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-base">Total da Operação:</span>
                        <span class="font-bold text-slate-900 text-lg" id="display_total">R$ <?php echo number_format(($origem['saldo'] ?? $origem['valor']) + $origem['valor_juros'], 2, ',', '.'); ?></span>
                    </div>
                    <p class="text-[10px] text-amber-700 mt-2 italic text-center">Serão gerados dois registros de saída separados (Principal + Juros).</p>
                </div>

                <script>
                    const principalInput = document.getElementById('valor_liquidado');
                    const displayPrincipal = document.getElementById('display_principal');
                    const displayTotal = document.getElementById('display_total');
                    const valorJuros = <?php echo (float)$origem['valor_juros']; ?>;

                    principalInput.addEventListener('input', function() {
                        const val = parseFloat(this.value) || 0;
                        displayPrincipal.textContent = 'R$ ' + val.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                        displayTotal.textContent = 'R$ ' + (val + valorJuros).toLocaleString('pt-BR', {minimumFractionDigits: 2});
                    });
                </script>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
