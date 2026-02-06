<?php
require_once __DIR__ . '/../../models/TipoPagamentoModel.php';

$model = new TipoPagamentoModel();
$item = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $item = $model->getById($_GET['id']);
    $isEdit = true;
}

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isEdit) {
        $model->update($_GET['id'], $_POST);
        header('Location: list.php?success=updated');
    } else {
        $model->create($_POST);
        header('Location: list.php?success=created');
    }
    exit;
}

$pageTitle = 'Tipo de Pagamento';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Tipo de Pagamento</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição *</label>
            <input type="text" name="descricao" required value="<?php echo htmlspecialchars($item['descricao'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="ativo" id="ativo" value="1" <?php echo ($item['ativo'] ?? 1) ? 'checked' : ''; ?>
                class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-2 focus:ring-slate-500">
            <label for="ativo" class="ml-2 block text-sm text-gray-700">Ativo</label>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar este tipo de pagamento?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition">
                <i class="fas fa-save mr-2"></i>Salvar
            </button>

            <a href="list.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
