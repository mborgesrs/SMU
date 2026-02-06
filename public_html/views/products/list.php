<?php
require_once __DIR__ . '/../../models/ProductModel.php';

$model = new ProductModel();

if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = 'Produtos/Serviços';
require_once __DIR__ . '/../layout/header.php';


$products = $model->getAll();
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Produtos/Serviços</h2>
        <div class="flex gap-4">
            <a href="../dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            <a href="form.php" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Novo Produto
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['nome']); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($product['descricao'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500">R$ <?php echo number_format($product['preco_unitario'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="form.php?id=<?php echo $product['id']; ?>" class="text-slate-600 hover:text-slate-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $product['id']; ?>" onclick="return confirmDelete(event, 'Deseja excluir este produto/serviço?')" class="text-red-600 hover:text-red-900">
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
