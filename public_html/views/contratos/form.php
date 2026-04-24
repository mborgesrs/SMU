<?php
require_once __DIR__ . '/../../models/ContratoModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/ObjetoModel.php';
require_once __DIR__ . '/../../models/ProductModel.php';
require_once __DIR__ . '/../../models/ContaModel.php';
require_once __DIR__ . '/../../models/PortadorModel.php';
require_once __DIR__ . '/../../models/FinanceiroModel.php';

$model = new ContratoModel();
$clienteModel = new ClienteModel();
$objetoModel = new ObjetoModel();
$productModel = new ProductModel();
$contaModel = new ContaModel();
$portadorModel = new PortadorModel();
$financeiroModel = new FinanceiroModel();

$contrato = null;
$isEdit = false;
$financeirosVinculados = [];

if (isset($_GET['id'])) {
    $contrato = $model->getById($_GET['id']);
    $financeirosVinculados = $financeiroModel->getAll(['nf_contrato' => $_GET['id']]);
    $isEdit = true;
}

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process products
    $items = [];
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        foreach ($_POST['products'] as $productId) {
            $product = $productModel->getById($productId);
            if ($product) {
                $quantidade = $_POST['quantidade_' . $productId] ?? 1;
                $items[] = [
                    'id_produto' => $productId,
                    'quantidade' => $quantidade,
                    'preco_unitario' => $product['preco_unitario'],
                    'subtotal' => $product['preco_unitario'] * $quantidade
                ];
            }
        }
    }

    if ($isEdit) {
        $model->update($_GET['id'], $_POST);
        header('Location: list.php?success=updated');
    } else {
        $model->create($_POST, $items);
        header('Location: list.php?success=created');
    }
    exit;
}

$clientes = $clienteModel->getAll();
$objetos = $objetoModel->getAll();
$products = $productModel->getAll();
$contas = $contaModel->getAll();
$portadores = $portadorModel->getAll();

$pageTitle = 'Contrato';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-5xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Contrato</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <!-- Basic Info -->
        <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Informações Básicas</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contratante *</label>
                    <div class="relative">
                        <input type="text" id="cliente_search" placeholder="Digite o nome, fantasia ou CPF/CNPJ..." 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"
                               autocomplete="off"
                               value="<?php 
                                    if ($isEdit) {
                                        $c = $clienteModel->getById($contrato['id_contratante']);
                                        echo htmlspecialchars($c['nome'] ?? '');
                                    }
                                ?>">
                        <input type="hidden" name="id_contratante" id="id_contratante" required 
                               value="<?php echo $contrato['id_contratante'] ?? ''; ?>">
                        
                        <!-- Dropdown de resultados -->
                        <div id="cliente_results" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                            <!-- Resultados via JS -->
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Objeto</label>
                    <select name="id_objeto" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($objetos as $objeto): ?>
                            <option value="<?php echo $objeto['id']; ?>" <?php echo ($contrato['id_objeto'] ?? '') == $objeto['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($objeto['descricao']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Natureza *</label>
                    <select name="natureza" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="cobranca" <?php echo ($contrato['natureza'] ?? 'cobranca') === 'cobranca' ? 'selected' : ''; ?>>Cobrança</option>
                        <option value="pagamento" <?php echo ($contrato['natureza'] ?? '') === 'pagamento' ? 'selected' : ''; ?>>Pagamento</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                    <select name="tipo" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="servico" <?php echo ($contrato['tipo'] ?? 'servico') === 'servico' ? 'selected' : ''; ?>>Serviço</option>
                        <option value="assessoria" <?php echo ($contrato['tipo'] ?? '') === 'assessoria' ? 'selected' : ''; ?>>Assessoria</option>
                        <option value="consultoria" <?php echo ($contrato['tipo'] ?? '') === 'consultoria' ? 'selected' : ''; ?>>Consultoria</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalidade *</label>
                    <select name="modalidade" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="Antecipado" <?php echo ($contrato['modalidade'] ?? 'Antecipado') === 'Antecipado' ? 'selected' : ''; ?>>Antecipado</option>
                        <option value="Retroativo" <?php echo ($contrato['modalidade'] ?? '') === 'Retroativo' ? 'selected' : ''; ?>>Retroativo</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Portador</label>
                    <select name="id_portador" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($portadores as $portador): ?>
                            <option value="<?php echo $portador['id']; ?>" <?php echo ($contrato['id_portador'] ?? '') == $portador['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($portador['nome']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Início *</label>
                    <input type="date" name="dt_inicio" required value="<?php echo htmlspecialchars($contrato['dt_inicio'] ?? date('Y-m-d')); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Término</label>
                    <input type="date" name="dt_termino" value="<?php echo htmlspecialchars($contrato['dt_termino'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Conta</label>
                    <select name="id_conta" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="">Selecione...</option>
                        <?php foreach ($contas as $conta): ?>
                            <option value="<?php echo $conta['id']; ?>" <?php echo ($contrato['id_conta'] ?? '') == $conta['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conta['descricao']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <?php if ($isEdit): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select name="status" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 font-semibold <?php 
                        echo ($contrato['status'] ?? 'pendente') === 'ativo' ? 'text-green-600' : (
                             ($contrato['status'] ?? 'pendente') === 'encerrado' ? 'text-red-600' : 'text-yellow-600'
                        ); ?>">
                        <option value="pendente" <?php echo ($contrato['status'] ?? 'pendente') === 'pendente' ? 'selected' : ''; ?>>Pendente/Aguardando Assinatura</option>
                        <option value="ativo" <?php echo ($contrato['status'] ?? '') === 'ativo' ? 'selected' : ''; ?>>Ativo/Vigente</option>
                        <option value="encerrado" <?php echo ($contrato['status'] ?? '') === 'encerrado' ? 'selected' : ''; ?>>Encerrado</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valor Total</label>
                    <input type="text" name="valor_total" id="valor_total_input" required value="<?php echo number_format($contrato['valor_total'] ?? 0, 2, ',', '.'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 font-bold text-indigo-600">
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Products/Services (Only for New Contracts) -->
        <?php if (!$isEdit): ?>
        <div class="border-b pb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Produtos/Serviços</h3>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                <?php foreach ($products as $product): ?>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                        <div class="flex items-center space-x-3">
                            <input type="checkbox" name="products[]" value="<?php echo $product['id']; ?>"
                                class="product-checkbox w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-slate-500"
                                data-price="<?php echo $product['preco_unitario']; ?>"
                                data-quantity="1"
                                onchange="calculateTotal()">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($product['nome']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($product['descricao'] ?? ''); ?></div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div>
                                <label class="text-xs text-gray-500">Qtd:</label>
                                <input type="number" name="quantidade_<?php echo $product['id']; ?>" value="1" min="1"
                                    class="w-16 px-2 py-1 border border-gray-300 rounded text-sm"
                                    onchange="updateQuantity(this, <?php echo $product['id']; ?>)">
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-medium text-gray-900">R$ <?php echo number_format($product['preco_unitario'], 2, ',', '.'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Total Display -->
        <div class="bg-slate-50 p-4 rounded-lg">
            <div class="flex justify-between items-center">
                <span class="text-lg font-semibold text-gray-700">Valor Total:</span>
                <span id="total_display" class="text-2xl font-bold text-slate-800">R$ 0,00</span>
            </div>
            <input type="hidden" name="valor_total" id="valor_total" value="0">
        </div>
        <?php endif; ?>

        <!-- Observations -->
        <div class="pt-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
            <textarea name="observacoes" rows="3"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"><?php echo htmlspecialchars($contrato['observacoes'] ?? ''); ?></textarea>
        </div>

        <?php if ($isEdit): ?>
        <div class="border-t pt-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Lançamentos Financeiros Vinculados</h3>
            
            <?php if (empty($financeirosVinculados)): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Ainda não foi gerado nenhum lançamento financeiro para este contrato.
                    </div>
                    <a href="gerar_financeiro.php?id=<?php echo $_GET['id']; ?>" 
                       onclick="return confirmAction(event, 'Deseja gerar o financeiro do contrato?', 'warning', 'Sim, gerar!', '#10b981')"
                       class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-sm text-sm font-bold">
                        <i class="fas fa-hand-holding-usd mr-2"></i>Gerar Financeiro
                    </a>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto bg-white border border-gray-200 rounded-lg shadow-sm">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3">ID</th>
                                <th class="px-4 py-3">Vencimento</th>
                                <th class="px-4 py-3">Tipo Pgto</th>
                                <th class="px-4 py-3">Valor</th>
                                <th class="px-4 py-3">Situação</th>
                                <th class="px-4 py-3 text-right">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($financeirosVinculados as $fin): ?>
                            <tr class="border-b hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-900">#<?php echo $fin['id']; ?></td>
                                <td class="px-4 py-3">
                                    <?php echo $fin['dt_vencimento'] ? date('d/m/Y', strtotime($fin['dt_vencimento'])) : '-'; ?>
                                </td>
                                <td class="px-4 py-3"><?php echo htmlspecialchars($fin['tipo_pagamento'] ?? '-'); ?></td>
                                <td class="px-4 py-3 font-bold text-gray-700">R$ <?php echo number_format($fin['valor'], 2, ',', '.'); ?></td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-bold rounded <?php 
                                        echo $fin['situacao'] === 'Liquidado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; 
                                    ?>">
                                        <?php echo htmlspecialchars($fin['situacao']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <a href="../financeiro/form.php?id=<?php echo $fin['id']; ?>&origem_contrato=<?php echo $_GET['id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded transition text-xs font-semibold">
                                        <i class="fas fa-edit mr-1"></i>Alterar
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Buttons -->
        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar as alterações deste contrato?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-save mr-2"></i>Salvar
            </button>

            <?php if ($isEdit): ?>
                <a href="../../pdf/contrato_pdf.php?id=<?php echo $_GET['id']; ?>" target="_blank" class="bg-purple-600 text-white px-6 py-3 rounded-lg hover:bg-purple-700 transition shadow-lg">
                    <i class="fas fa-file-pdf mr-2"></i>Imprimir Contrato
                </a>

                <?php if (empty($contrato['zapsign_doc_id'])): ?>
                    <button type="button" onclick="enviarZapSign(<?php echo $_GET['id']; ?>)" id="btn-zapsign"
                            class="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition shadow-lg">
                        <i class="fas fa-pen-fancy mr-2"></i>Assinar via ZapSign
                    </button>
                <?php else: ?>
                    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                        <span class="text-sm font-medium text-blue-700">ZapSign:</span>
                        <span class="px-2 py-1 text-xs font-bold rounded <?php 
                            echo $contrato['zapsign_status'] === 'signed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; 
                        ?>">
                            <?php echo $contrato['zapsign_status'] === 'signed' ? 'ASSINADO' : 'AGUARDANDO'; ?>
                        </span>
                        <?php if ($contrato['zapsign_status'] !== 'signed'): ?>
                            <a href="<?php echo $contrato['zapsign_url']; ?>" target="_blank" class="text-xs text-blue-600 underline hover:text-blue-800">
                                Ver link de assinatura
                            </a>
                            <button type="button" onclick="verificarZapSign(<?php echo $_GET['id']; ?>)" id="btn-verificar-zapsign" class="ml-3 text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded hover:bg-indigo-200 transition font-semibold">
                                <i class="fas fa-sync-alt mr-1"></i>Atualizar Status
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
            <a href="list.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </form>
</div>

<script>
// Busca dinâmica de clientes
function initClienteSearch() {
    const searchInput = document.getElementById('cliente_search');
    const idInput = document.getElementById('id_contratante');
    const resultsDiv = document.getElementById('cliente_results');
    let timeout = null;

    searchInput?.addEventListener('input', function() {
        clearTimeout(timeout);
        const query = this.value;

        if (query.length < 2) {
            resultsDiv.classList.add('hidden');
            return;
        }

        timeout = setTimeout(async () => {
            try {
                const response = await fetch(`../clientes/api_search.php?q=${encodeURIComponent(query)}`);
                const data = await response.json();

                if (data.length > 0) {
                    resultsDiv.innerHTML = data.map(c => `
                        <div class="p-3 hover:bg-indigo-50 cursor-pointer border-b border-gray-50 transition-colors" 
                             onclick="selectCliente(${c.id}, '${c.nome.replace(/'/g, "\\'")}')">
                            <div class="font-bold text-gray-800 text-sm">${c.nome}</div>
                            <div class="text-xs text-gray-500">${c.fantasia || ''} ${c.cpf_cnpj ? ' - ' + c.cpf_cnpj : ''}</div>
                        </div>
                    `).join('');
                    resultsDiv.classList.remove('hidden');
                } else {
                    resultsDiv.innerHTML = '<div class="p-3 text-sm text-gray-500">Nenhum cliente encontrado.</div>';
                    resultsDiv.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Erro ao buscar clientes:', error);
            }
        }, 300);
    });

    // Fechar ao clicar fora
    document.addEventListener('click', function(e) {
        if (!searchInput?.contains(e.target) && !resultsDiv?.contains(e.target)) {
            resultsDiv?.classList.add('hidden');
        }
    });
}

function selectCliente(id, nome) {
    document.getElementById('id_contratante').value = id;
    document.getElementById('cliente_search').value = nome;
    document.getElementById('cliente_results').classList.add('hidden');
}

function formatarMoeda(v) {
    v = v.replace(/\D/g, "");
    v = (v / 100).toFixed(2) + "";
    v = v.replace(".", ",");
    v = v.replace(/(\d)(\d{3}),/g, "$1.$2,");
    return v;
}

function initCurrencyMask() {
    const valorInput = document.getElementById('valor_total_input');
    if (valorInput) {
        valorInput.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, "");
            if (v === "") v = "0";
            e.target.value = "R$ " + formatarMoeda(v);
        });

        // Garantir formato inicial
        if (valorInput.value && !valorInput.value.startsWith('R$')) {
            valorInput.value = "R$ " + valorInput.value;
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initClienteSearch();
    initCurrencyMask();
});

function calculateTotal() {
    let total = 0;
    document.querySelectorAll('.product-checkbox:checked').forEach(cb => {
        const price = parseFloat(cb.dataset.price);
        const quantity = parseInt(cb.dataset.quantity);
        total += price * quantity;
    });

    const display = document.getElementById('total_display');
    const hiddenInput = document.getElementById('valor_total');
    
    if (display) {
        display.textContent = "R$ " + formatarMoeda((total * 100).toFixed(0));
    }
    if (hiddenInput) {
        hiddenInput.value = total.toFixed(2);
    }
}

// Antes de enviar o formulário, remover a máscara para o PHP processar como número
document.querySelector('form').addEventListener('submit', function(e) {
    const valorInput = document.getElementById('valor_total_input');
    if (valorInput) {
        let value = valorInput.value.replace("R$ ", "").replace(/\./g, "").replace(",", ".");
        valorInput.value = value;
    }
});

function updateQuantity(input, productId) {
    const checkbox = document.querySelector(`input[value="${productId}"]`);
    if (checkbox) {
        checkbox.dataset.quantity = input.value;
        calculateTotal();
    }
}

async function enviarZapSign(id) {
    const btn = document.getElementById('btn-zapsign');
    const originalContent = btn.innerHTML;
    
    // Confirmação
    const result = await Swal.fire({
        title: 'Enviar para ZapSign?',
        text: 'O contrato será enviado para assinatura eletrônica do cliente.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, enviar!',
        cancelButtonText: 'Cancelar'
    });

    if (!result.isConfirmed) return;

    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';
        
        const response = await fetch(`../../api/contratos/enviar_zapsign.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            await Swal.fire({
                title: 'Sucesso!',
                text: 'Contrato enviado para ZapSign com sucesso. O link de assinatura foi gerado.',
                icon: 'success'
            });
            window.location.reload();
        } else {
            throw new Error(data.error || 'Erro ao enviar para ZapSign');
        }
    } catch (error) {
        console.error('Erro ZapSign:', error);
        Swal.fire({
            title: 'Erro!',
            text: error.message || 'Ocorreu um erro ao processar a assinatura digital.',
            icon: 'error'
        });
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}

async function verificarZapSign(id) {
    const btn = document.getElementById('btn-verificar-zapsign');
    const originalContent = btn.innerHTML;
    
    try {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Aguarde...';
        
        const response = await fetch(`../../api/contratos/verificar_zapsign.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            if (data.signed) {
                await Swal.fire({
                    title: 'Assinado!',
                    text: data.message,
                    icon: 'success'
                });
                window.location.reload();
            } else {
                Swal.fire({
                    title: 'Aviso',
                    text: data.message,
                    icon: 'info'
                });
            }
        } else {
            throw new Error(data.error || 'Erro ao verificar status na ZapSign');
        }
    } catch (error) {
        console.error('Erro ao verificar ZapSign:', error);
        Swal.fire({
            title: 'Erro!',
            text: error.message,
            icon: 'error'
        });
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalContent;
    }
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
