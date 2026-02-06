<?php
require_once __DIR__ . '/../../models/DependenteModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';

$model = new DependenteModel();
$clienteModel = new ClienteModel();
$dependente = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $dependente = $model->getById($_GET['id']);
    $isEdit = true;
} elseif (isset($_GET['id_cliente'])) {
    // Sugestão de cliente caso venha via URL
    $clienteSugerido = $clienteModel->getById($_GET['id_cliente']);
}

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirectUrl = 'list.php';
    if (isset($_GET['id_cliente']) || isset($_POST['id_cliente'])) {
        $idCliente = $_GET['id_cliente'] ?? $_POST['id_cliente'];
        $redirectUrl = "../clientes/form.php?id=" . $idCliente . "&status=dep_saved";
    }

    if ($isEdit) {
        $model->update($_GET['id'], $_POST);
        header('Location: ' . $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'success=updated');
    } else {
        $model->create($_POST);
        header('Location: ' . $redirectUrl . (strpos($redirectUrl, '?') !== false ? '&' : '?') . 'success=created');
    }
    exit;
}

$clientes = $clienteModel->getAll();

// Now include header after POST processing
$pageTitle = 'Dependente';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Dependente</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($dependente['nome'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cliente *</label>
            <div class="relative">
                <input type="text" id="cliente_search" placeholder="Digite o nome, fantasia ou CPF/CNPJ..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"
                       autocomplete="off"
                       value="<?php 
                            if ($isEdit) {
                                $c = $clienteModel->getById($dependente['id_cliente']);
                                echo htmlspecialchars($c['nome'] ?? '');
                            } elseif (isset($clienteSugerido)) {
                                echo htmlspecialchars($clienteSugerido['nome'] ?? '');
                            }
                        ?>">
                <input type="hidden" name="id_cliente" id="id_cliente" required 
                       value="<?php 
                            if ($isEdit) {
                                echo $dependente['id_cliente'];
                            } elseif (isset($clienteSugerido)) {
                                echo $clienteSugerido['id'];
                            }
                        ?>">
                
                <!-- Dropdown de resultados -->
                <div id="cliente_results" class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                    <!-- Resultados via JS -->
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Nascimento</label>
                <input type="date" name="dt_nascto" value="<?php echo htmlspecialchars($dependente['dt_nascto'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Matrícula</label>
                <input type="text" name="matricula" value="<?php echo htmlspecialchars($dependente['matricula'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">CPF</label>
                <input type="text" name="cpf" class="cpf-mask w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500"
                    value="<?php echo htmlspecialchars($dependente['cpf'] ?? ''); ?>">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">RG</label>
                <input type="text" name="rg" value="<?php echo htmlspecialchars($dependente['rg'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Licença</label>
                <input type="text" name="tipo_licenca" list="tipos_licenca" value="<?php echo htmlspecialchars($dependente['tipo_licenca'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                <datalist id="tipos_licenca">
                    <option value="Parto">
                    <option value="Adoção">
                </datalist>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data Licença</label>
                <input type="date" name="dt_licenca" value="<?php echo htmlspecialchars($dependente['dt_licenca'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Data de Registro</label>
                <input type="date" name="dt_registro" value="<?php echo htmlspecialchars($dependente['dt_registro'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Dt Certidão Reg. Civil</label>
                <input type="date" name="dt_certidao_reg_civil" value="<?php echo htmlspecialchars($dependente['dt_certidao_reg_civil'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">Livro Civil</label>
                <input type="text" name="livro_civil" value="<?php echo htmlspecialchars($dependente['livro_civil'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Folha Registro</label>
                <input type="text" name="folha_registro" value="<?php echo htmlspecialchars($dependente['folha_registro'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Termo Registro</label>
                <input type="text" name="termo_registro" value="<?php echo htmlspecialchars($dependente['termo_registro'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar este dependente?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition">
                <i class="fas fa-save mr-2"></i>Salvar
            </button>

            <?php 
                $backUrl = 'list.php';
                if (isset($_GET['id_cliente'])) {
                    $backUrl = "../clientes/form.php?id=" . $_GET['id_cliente'];
                } elseif (isset($dependente['id_cliente'])) {
                    $backUrl = "../clientes/form.php?id=" . $dependente['id_cliente'];
                }
            ?>
            <a href="<?php echo $backUrl; ?>" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </form>
</div>

<script>
// Busca dinâmica de clientes
function initClienteSearch() {
    const searchInput = document.getElementById('cliente_search');
    const idInput = document.getElementById('id_cliente');
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
                        <div class="p-3 hover:bg-slate-50 cursor-pointer border-b border-gray-50 transition-colors" 
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
    document.getElementById('id_cliente').value = id;
    document.getElementById('cliente_search').value = nome;
    document.getElementById('cliente_results').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
    initClienteSearch();
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
