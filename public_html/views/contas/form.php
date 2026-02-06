<?php
require_once __DIR__ . '/../../models/ContaModel.php';

$model = new ContaModel();
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

$pageTitle = 'Conta';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-3xl mx-auto">
    <h2 class="text-3xl font-bold text-gray-800 mb-6"><?php echo $isEdit ? 'Editar' : 'Nova'; ?> Conta</h2>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Código (ex: 01.01.01)</label>
            <input type="text" name="codigo" placeholder="00.00.00" maxlength="20"
                value="<?php echo htmlspecialchars($item['codigo'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
            <p class="text-xs text-gray-500 mt-1">Use formato hierárquico: 01.01.01 para receitas, 02.01.01 para despesas</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Descrição *</label>
            <input type="text" name="descricao" required value="<?php echo htmlspecialchars($item['descricao'] ?? ''); ?>"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
            <select name="tipo" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                <option value="Analitica" <?php echo ($item['tipo'] ?? 'Analitica') === 'Analitica' ? 'selected' : ''; ?>>Analítica</option>
                <option value="Sintetica" <?php echo ($item['tipo'] ?? '') === 'Sintetica' ? 'selected' : ''; ?>>Sintética</option>
            </select>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="ativo" id="ativo" value="1" <?php echo ($item['ativo'] ?? 1) ? 'checked' : ''; ?>
                class="w-4 h-4 text-slate-800 border-gray-300 rounded focus:ring-slate-500">
            <label for="ativo" class="ml-2 block text-sm text-gray-700">Ativo</label>
        </div>

        <div class="flex gap-4 pt-4">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar as alterações desta conta?', 'question', 'Sim, salvar!', '#1e293b')"
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
