<?php
require_once __DIR__ . '/../../models/PortadorModel.php';

$model = new PortadorModel();
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

$pageTitle = 'Portador';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Portador</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Nome *</label>
            <input type="text" name="nome" required value="<?php echo htmlspecialchars($item['nome'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Conta</label>
                <input type="text" name="conta" value="<?php echo htmlspecialchars($item['conta'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Agência</label>
                <input type="text" name="agencia" value="<?php echo htmlspecialchars($item['agencia'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Número</label>
                <input type="text" name="numero" value="<?php echo htmlspecialchars($item['numero'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            </div>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar este portador?', 'question', 'Sim, salvar!', '#1e293b')"
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
