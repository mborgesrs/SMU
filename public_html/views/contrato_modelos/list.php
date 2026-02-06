<?php
require_once __DIR__ . '/../../models/ContratoModeloModel.php';

$model = new ContratoModeloModel();

if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = 'Modelos de Contrato';
require_once __DIR__ . '/../layout/header.php';

$modelos = $model->getAll();
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Modelos/Cláusulas de Contrato</h2>
        <a href="form.php" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
            <i class="fas fa-plus mr-2"></i>Novo Modelo
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            Operação realizada com sucesso!
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Criado em</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($modelos)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500 italic">Nenhum modelo cadastrado.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($modelos as $modelo): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($modelo['nome']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $modelo['ativo'] ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $modelo['ativo'] ? 'Ativo' : 'Inativo'; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($modelo['created_at'])); ?></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="form.php?id=<?php echo $modelo['id']; ?>" class="text-slate-600 hover:text-slate-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $modelo['id']; ?>" onclick="return confirmDelete(event, 'Deseja excluir este modelo?')" class="text-red-600 hover:text-red-900">
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
