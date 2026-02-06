<?php
require_once __DIR__ . '/../../models/' . $config['model'] . '.php';

$modelClass = $config['model'];
$model = new $modelClass();

if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = $config['title'] ?? 'Lista';
require_once __DIR__ . '/../layout/header.php';


$items = $model->getAll();
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $config['title']; ?></h2>
        <div class="flex gap-4">
            <a href="../dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            <a href="form.php" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Novo
            </a>
        </div>
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
                    <?php foreach ($config['columns'] as $col): ?>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"><?php echo $col['label']; ?></th>
                    <?php endforeach; ?>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($items as $item): ?>
                    <tr class="hover:bg-gray-50">
                        <?php foreach ($config['columns'] as $col): ?>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?php 
                                $value = $item[$col['field']] ?? '-';
                                if (isset($col['format'])) {
                                    if ($col['format'] === 'boolean') {
                                        echo $value ? '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Ativo</span>' : '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Inativo</span>';
                                    } elseif ($col['format'] === 'currency') {
                                        $color = $value < 0 ? 'text-red-600' : 'text-green-600';
                                        echo '<span class="font-bold ' . $color . '">R$ ' . number_format($value, 2, ',', '.') . '</span>';
                                    }
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="form.php?id=<?php echo $item['id']; ?>" class="text-slate-600 hover:text-slate-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirmDelete(event)" class="text-red-600 hover:text-red-900">
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
