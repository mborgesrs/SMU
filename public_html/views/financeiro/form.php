<?php
require_once __DIR__ . '/../../models/FinanceiroModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/PortadorModel.php';
require_once __DIR__ . '/../../models/ContaModel.php';
require_once __DIR__ . '/../../models/TipoPagamentoModel.php';

$model = new FinanceiroModel();
$clienteModel = new ClienteModel();
$portadorModel = new PortadorModel();
$contaModel = new ContaModel();
$tipoPgtoModel = new TipoPagamentoModel();

$item = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $item = $model->getById($_GET['id']);
    $isEdit = true;
}

$origem_contrato = $_GET['origem_contrato'] ?? $_POST['origem_contrato'] ?? null;

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isEdit) {
        $model->update($_GET['id'], $_POST);
        if ($origem_contrato) {
            header('Location: ../contratos/form.php?id=' . $origem_contrato . '&success=financeiro_updated');
        } else {
            header('Location: list.php?success=updated');
        }
    } else {
        $model->create($_POST);
        if ($origem_contrato) {
            header('Location: ../contratos/form.php?id=' . $origem_contrato . '&success=financeiro_created');
        } else {
            header('Location: list.php?success=created');
        }
    }
    exit;
}

$clientes = $clienteModel->getAll();
$portadores = $portadorModel->getAll();
$contas = $contaModel->getAll();
$tiposPgto = $tipoPgtoModel->getAll();

$pageTitle = 'Financeiro';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Lançamento Financeiro</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <?php if ($origem_contrato): ?>
            <input type="hidden" name="origem_contrato" value="<?php echo htmlspecialchars($origem_contrato); ?>">
        <?php endif; ?>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Lançamento *</label>
                <input type="date" name="data" required value="<?php echo htmlspecialchars($item['data'] ?? date('Y-m-d')); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Vencimento</label>
                <input type="date" name="dt_vencimento" value="<?php echo htmlspecialchars($item['dt_vencimento'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">NF/Contrato</label>
                <input type="text" name="nf_contrato" value="<?php echo htmlspecialchars($item['nf_contrato'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                <select name="tipo" id="tipo_select" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Selecione...</option>
                    <option value="Receber" <?php echo ($item['tipo'] ?? '') === 'Receber' ? 'selected' : ''; ?>>A Receber (Título)</option>
                    <option value="Pagar" <?php echo ($item['tipo'] ?? '') === 'Pagar' ? 'selected' : ''; ?>>A Pagar (Título)</option>
                    <option value="Entrada" <?php echo ($item['tipo'] ?? '') === 'Entrada' ? 'selected' : ''; ?>>Entrada (Dinheiro)</option>
                    <option value="Saida" <?php echo ($item['tipo'] ?? '') === 'Saida' ? 'selected' : ''; ?>>Saída (Dinheiro)</option>
                    
                    <?php if (($item['tipo'] ?? '') === 'cRecebido'): ?>
                        <option value="cRecebido" selected>Crédito Recebido (Liquidado)</option>
                    <?php endif; ?>
                    <?php if (($item['tipo'] ?? '') === 'dPago'): ?>
                        <option value="dPago" selected>Despesa Paga (Liquidado)</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="md:col-span-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Situação</label>
                <input type="text" id="situacao_input" readonly value="<?php echo htmlspecialchars($item['situacao'] ?? 'Aberto'); ?>"
                    class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 font-medium">
            </div>

            <script>
            document.getElementById('tipo_select').addEventListener('change', function() {
                const tipo = this.value;
                const situacaoInput = document.getElementById('situacao_input');
                
                if (tipo === 'Receber' || tipo === 'Pagar') {
                    situacaoInput.value = 'Aberto';
                } else if (tipo !== '') {
                    situacaoInput.value = 'Liquidado';
                }
            });
            </script>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total *</label>
                <?php $isLinked = !empty($item['nf_contrato']) || !empty($item['id_origem']); ?>
                <input type="number" name="valor" step="0.01" required value="<?php echo htmlspecialchars($item['valor'] ?? ''); ?>"
                    <?php echo $isLinked ? 'readonly class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 font-medium cursor-not-allowed"' : 'class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"'; ?>>
                <?php if ($isLinked): ?>
                    <p class="text-xs text-amber-600 mt-1"><i class="fas fa-lock mr-1"></i>Bloqueado (Vinculado a Contrato)</p>
                <?php endif; ?>
            </div>

            <div class="md:col-span-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Saldo Restante</label>
                <input type="text" readonly value="R$ <?php echo number_format($item['saldo'] ?? ($item['valor'] ?? 0), 2, ',', '.'); ?>"
                    class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-blue-600 font-bold">
            </div>

            <div class="md:col-span-12">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Cliente/Fornecedor *</label>
                    <button type="button" onclick="openQuickCreate('../clientes/form.php')" class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded hover:bg-slate-200 transition flex items-center" title="Novo Cliente">
                        <i class="fas fa-plus mr-1"></i> Novo
                    </button>
                </div>
                <input list="lista-clientes" id="cliente_search" placeholder="Digite para buscar..." 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"
                    value="<?php 
                        if (isset($item['id_cliente_forn'])) {
                            foreach ($clientes as $c) {
                                if ($c['id'] == $item['id_cliente_forn']) {
                                    echo htmlspecialchars($c['nome']);
                                    break;
                                }
                            }
                        }
                    ?>">
                <input type="hidden" name="id_cliente_forn" id="id_cliente_forn" value="<?php echo htmlspecialchars($item['id_cliente_forn'] ?? ''); ?>">
                <datalist id="lista-clientes">
                    <?php foreach ($clientes as $cliente): ?>
                        <option data-id="<?php echo $cliente['id']; ?>" value="<?php echo htmlspecialchars($cliente['nome'] . ($cliente['fantasia'] ? " (" . $cliente['fantasia'] . ")" : "") . ($cliente['cpf_cnpj'] ? " - " . $cliente['cpf_cnpj'] : "")); ?>">
                    <?php endforeach; ?>
                </datalist>

                <script>
                document.getElementById('cliente_search').addEventListener('input', function(e) {
                    const input = e.target;
                    const list = document.getElementById('lista-clientes');
                    const hiddenInput = document.getElementById('id_cliente_forn');
                    const options = list.options;
                    
                    hiddenInput.value = '';
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].value === input.value) {
                            hiddenInput.value = options[i].getAttribute('data-id');
                            break;
                        }
                    }
                });
                </script>
            </div>

            <div class="md:col-span-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Portador</label>
                    <button type="button" onclick="openQuickCreate('../portadores/form.php')" class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded hover:bg-slate-200 transition flex items-center" title="Novo Portador">
                        <i class="fas fa-plus mr-1"></i> Novo
                    </button>
                </div>
                <select name="id_portador" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Selecione...</option>
                    <?php foreach ($portadores as $portador): ?>
                        <option value="<?php echo $portador['id']; ?>" <?php echo ($item['id_portador'] ?? '') == $portador['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($portador['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Conta</label>
                    <button type="button" onclick="openQuickCreate('../contas/form.php')" class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded hover:bg-slate-200 transition flex items-center" title="Nova Conta">
                        <i class="fas fa-plus mr-1"></i> Novo
                    </button>
                </div>
                <select name="id_conta" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Selecione...</option>
                    <?php foreach ($contas as $conta): ?>
                        <option value="<?php echo $conta['id']; ?>" <?php echo ($item['id_conta'] ?? '') == $conta['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($conta['descricao']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="md:col-span-4">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-sm font-medium text-gray-700">Tipo de Pagamento</label>
                    <button type="button" onclick="openQuickCreate('../tipos_pagamento/form.php')" class="text-xs bg-slate-100 text-slate-600 px-2 py-1 rounded hover:bg-slate-200 transition flex items-center" title="Novo Tipo de PGTO">
                        <i class="fas fa-plus mr-1"></i> Novo
                    </button>
                </div>
                <select name="id_tipopgto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                    <option value="">Selecione...</option>
                    <?php foreach ($tiposPgto as $tipo): ?>
                        <option value="<?php echo $tipo['id']; ?>" <?php echo ($item['id_tipopgto'] ?? '') == $tipo['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tipo['descricao']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Observação</label>
            <textarea name="observacao" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"><?php echo htmlspecialchars($item['observacao'] ?? ''); ?></textarea>
        </div>

        <div class="flex flex-wrap gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar este lançamento financeiro?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition flex-1 md:flex-none">
                <i class="fas fa-save mr-2"></i>Salvar
            </button>

            
            <?php if ($isEdit && ($item['situacao'] ?? 'Aberto') === 'Aberto'): ?>
                <a href="liquidar.php?id=<?php echo $item['id']; ?>" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition flex-1 md:flex-none text-center">
                    <i class="fas fa-check-double mr-2"></i>Liquidar
                </a>
            <?php endif; ?>

            <?php if ($origem_contrato): ?>
                <a href="../contratos/form.php?id=<?php echo htmlspecialchars($origem_contrato); ?>" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition flex-1 md:flex-none text-center">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar ao Contrato
                </a>
            <?php else: ?>
                <a href="list.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition flex-1 md:flex-none text-center">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>

</div>

<!-- Modal Quick Create -->
<div id="quickCreateModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeQuickCreate()" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full border border-gray-100">
            <div class="bg-slate-800 px-4 py-3 flex justify-between items-center">
                <h3 class="text-lg font-bold text-white flex items-center">
                    <i class="fas fa-plus-circle mr-2 opacity-70"></i> Cadastro Rápido
                </h3>
                <button type="button" onclick="closeQuickCreate()" class="text-white hover:text-gray-300 transition-colors bg-white/10 hover:bg-white/20 p-2 rounded-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-white">
                <iframe id="quickCreateIframe" src="about:blank" class="w-full h-[70vh] border-0"></iframe>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3 border-t">
                <p class="text-xs text-gray-500 flex-1 flex items-center italic">
                    <i class="fas fa-info-circle mr-2"></i> Após salvar o cadastro, clique em fechar para atualizar as opções.
                </p>
                <button type="button" onclick="closeQuickCreate()" class="bg-slate-700 text-white px-6 py-2 rounded-lg hover:bg-slate-800 transition font-bold shadow-sm">
                    Fechar e Atualizar Transação
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openQuickCreate(url) {
    const modal = document.getElementById('quickCreateModal');
    const iframe = document.getElementById('quickCreateIframe');
    
    // Append modal=1 to the URL
    const separator = url.includes('?') ? '&' : '?';
    iframe.src = url + separator + 'modal=1';
    
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeQuickCreate() {
    const modal = document.getElementById('quickCreateModal');
    const iframe = document.getElementById('quickCreateIframe');
    
    modal.classList.add('hidden');
    iframe.src = 'about:blank';
    document.body.classList.remove('overflow-hidden');
    
    // Reload the page to refresh dropdowns
    Swal.fire({
        title: 'Atualizando listas...',
        text: 'Aguarde um momento enquanto recarregamos as opções.',
        timer: 1000,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            setTimeout(() => {
                location.reload();
            }, 800);
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
