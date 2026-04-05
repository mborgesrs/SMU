<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controllers/ClienteController.php';

checkAuth();

$controller = new ClienteController();

// Handle delete
if (isset($_GET['delete'])) {
    try {
        $controller->destroy($_GET['delete']);
        header('Location: list.php?success=deleted');
    } catch (Exception $e) {
        header('Location: list.php?error=' . urlencode($e->getMessage()));
    }
    exit;
}

// Handle status toggle
if (isset($_GET['toggle_status'])) {
    $controller->toggleStatus($_GET['toggle_status']);
    header('Location: list.php?success=updated');
    exit;
}

$pageTitle = 'Clientes';
require_once __DIR__ . '/../layout/header.php';


// Get search parameter
$search = $_GET['search'] ?? '';
$clientes = $controller->index($search);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Clientes</h2>
        <div class="flex gap-4">
            <a href="../dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            <a href="form.php" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Novo Cliente
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            <?php
            if ($_GET['success'] === 'created') echo 'Cliente criado com sucesso!';
            if ($_GET['success'] === 'updated') echo 'Cliente atualizado com sucesso!';
            if ($_GET['success'] === 'deleted') echo 'Cliente excluído com sucesso!';
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="" class="flex gap-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Buscar por nome, fantasia, CPF ou CNPJ..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none">
            <button type="submit" class="bg-slate-800 text-white px-6 py-2 rounded-lg hover:bg-slate-900 transition">
                <i class="fas fa-search mr-2"></i>Buscar
            </button>
            <?php if ($search): ?>
                <a href="list.php" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition">
                    <i class="fas fa-times mr-2"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fantasia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF/CNPJ</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contatos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Município</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-2"></i>
                                <p>Nenhum cliente encontrado</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cliente): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($cliente['nome']); ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($cliente['fantasia'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">
                                    <?php echo formatarCPF_CNPJ($cliente['cpf_cnpj'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <div class="flex flex-col space-y-1">
                                        <?php if (!empty($cliente['celular'])): ?>
                                            <div class="flex items-center text-indigo-600 font-medium">
                                                <i class="fab fa-whatsapp mr-2 text-green-500"></i>
                                                <a href="https://wa.me/55<?php echo preg_replace('/\D/', '', $cliente['celular']); ?>" target="_blank" class="hover:underline">
                                                    <?php echo formatarTelefone($cliente['celular']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($cliente['telefone'])): ?>
                                            <div class="flex items-center text-gray-600">
                                                <i class="fas fa-phone mr-2 text-gray-400"></i>
                                                <?php echo formatarTelefone($cliente['telefone']); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (empty($cliente['celular']) && empty($cliente['telefone'])): ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($cliente['municipio'] ?? '-'); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="?toggle_status=<?php echo $cliente['id']; ?>" 
                                       title="Clique para alterar o status"
                                       class="cursor-pointer">
                                        <?php if ($cliente['ativo']): ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                                Ativo
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 hover:bg-red-200 transition-colors">
                                                Inativo
                                            </span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button onclick="openDependentsModal(<?php echo $cliente['id']; ?>, '<?php echo htmlspecialchars($cliente['nome']); ?>')" 
                                       class="text-teal-600 hover:text-teal-900 mr-3" title="Dependentes">
                                        <i class="fas fa-child"></i>
                                    </button>
                                    <a href="imprimir_cliente.php?id=<?php echo $cliente['id']; ?>" target="_blank"
                                       class="text-green-600 hover:text-green-900 mr-3" title="Imprimir Contrato">
                                        <i class="fas fa-print"></i>
                                    </a>
                                    <a href="form.php?id=<?php echo $cliente['id']; ?>" 
                                       class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $cliente['id']; ?>" 
                                       onclick="return confirmDelete(event, 'Deseja excluir este cliente?')"
                                       class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </a>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Dependents Modal -->
<div id="dependentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-3/4 max-w-4xl shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Dependentes</h3>
            <button onclick="closeDependentsModal()" class="text-gray-600 hover:text-gray-900 text-2xl">&times;</button>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data Nasc.</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPF</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RG</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Matrícula</th>
                    </tr>
                </thead>
                <tbody id="dependentsTableBody" class="bg-white divide-y divide-gray-200">
                    <!-- Content will be loaded via JS -->
                </tbody>
            </table>
            <div id="noDependentsMessage" class="hidden text-center py-6 text-gray-500">
                Nenhum dependente encontrado.
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <button onclick="closeDependentsModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition">
                Fechar
            </button>
        </div>
    </div>
</div>

<script>
function openDependentsModal(clienteId, clienteNome) {
    document.getElementById('modalTitle').textContent = 'Dependentes de ' + clienteNome;
    document.getElementById('dependentsModal').classList.remove('hidden');
    
    // Clear previous content
    const tbody = document.getElementById('dependentsTableBody');
    tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">Carregando...</td></tr>';
    document.getElementById('noDependentsMessage').classList.add('hidden');

    // Dynamic path resolution
    let baseUrl = window.location.origin;
    let path = window.location.pathname;
    if (path.includes('/views/')) {
        path = path.split('/views/')[0];
    } else {
        path = ''; 
    }
    if (path.endsWith('/')) {
        path = path.slice(0, -1);
    }
    const apiUrl = `${baseUrl}${path}/api/get_dependentes.php?cliente_id=${clienteId}`;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                document.getElementById('noDependentsMessage').classList.remove('hidden');
            } else {
                data.forEach(dep => {
                    const dtNasc = dep.dt_nascto ? new Date(dep.dt_nascto).toLocaleDateString('pt-BR') : '-';
                    const row = `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">${dep.nome}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dtNasc}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dep.cpf || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dep.rg || '-'}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${dep.matricula || '-'}</td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-red-500">Erro ao carregar dependentes.</td></tr>';
        });
}

function closeDependentsModal() {
    document.getElementById('dependentsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('dependentsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDependentsModal();
    }
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
