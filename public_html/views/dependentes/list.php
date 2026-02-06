<?php
require_once __DIR__ . '/../../models/DependenteModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';

$model = new DependenteModel();
$clienteModel = new ClienteModel();

if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = 'Dependentes';
require_once __DIR__ . '/../layout/header.php';


$search = $_GET['search'] ?? '';
$idCliente = $_GET['id_cliente'] ?? null;

if ($idCliente) {
    $dependentes = $model->getByClienteId($idCliente);
    // If filtering by client, we might still want to show the client name in the title or a badge
    $clienteFiltrado = $clienteModel->getById($idCliente);
} else {
    $dependentes = $model->getAll($search);
}
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">
            Dependentes <?php echo isset($clienteFiltrado) ? "- " . htmlspecialchars($clienteFiltrado['nome']) : ''; ?>
        </h2>
        <div class="flex gap-4">
            <?php if ($idCliente): ?>
                <a href="../clientes/form.php?id=<?php echo $idCliente; ?>" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar para Cliente
                </a>
            <?php else: ?>
                <a href="../dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
            <?php endif; ?>
            
            <a href="form.php<?php echo $idCliente ? '?id_cliente=' . $idCliente : ''; ?>" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Novo Dependente
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            Operação realizada com sucesso!
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" class="flex gap-4">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Buscar por nome, CPF, RG, matrícula ou cliente..."
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

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPF</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Matrícula</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dt. Nascimento</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($dependentes as $dep): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($dep['nome']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($dep['cliente_nome'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($dep['cpf'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($dep['matricula'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo $dep['dt_nascto'] ? date('d/m/Y', strtotime($dep['dt_nascto'])) : '-'; ?></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="form.php?id=<?php echo $dep['id']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $dep['id']; ?>" onclick="return confirmDelete(event, 'Deseja excluir este dependente?')" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
