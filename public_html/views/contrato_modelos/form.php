<?php
require_once __DIR__ . '/../../models/ContratoModeloModel.php';

$model = new ContratoModeloModel();
$modelo = null;
$isEdit = false;

if (isset($_GET['id'])) {
    $modelo = $model->getById($_GET['id']);
    $isEdit = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isEdit) {
        $model->update($_GET['id'], $_POST);
    } else {
        $model->create($_POST);
    }
    header('Location: list.php?success=' . ($isEdit ? 'updated' : 'created'));
    exit;
}

$pageTitle = ($isEdit ? 'Editar' : 'Novo') . ' Modelo de Contrato';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $isEdit ? 'Editar' : 'Novo'; ?> Modelo de Contrato</h2>
    </div>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Título do Modelo *</label>
                <input type="text" name="nome" required
                    value="<?php echo htmlspecialchars($modelo['nome'] ?? ''); ?>"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                    placeholder="Ex: Contrato de Prestação de Serviços">
            </div>

            <div class="flex items-end pb-2">
                <label class="flex items-center cursor-pointer group">
                    <div class="relative">
                        <input type="checkbox" name="ativo" value="1" <?php echo ($modelo['ativo'] ?? 1) ? 'checked' : ''; ?> class="sr-only">
                        <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors group-[&:has(input:checked)]:bg-emerald-600"></div>
                        <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full shadow transition-transform group-[&:has(input:checked)]:translate-x-4"></div>
                    </div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Modelo Ativo</span>
                </label>
            </div>
        </div>

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Utilize as tags abaixo para que o sistema preencha os dados automaticamente na impressão:
                    </p>
                    <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-2 text-xs font-mono text-blue-800">
                        <span>{{cliente_nome}}</span>
                        <span>{{cliente_cpf_cnpj}}</span>
                        <span>{{cliente_rg}}</span>
                        <span>{{cliente_endereco}}</span>
                        <span>{{cliente_numero}}</span>
                        <span>{{cliente_bairro}}</span>
                        <span>{{cliente_municipio}}</span>
                        <span>{{cliente_uf}}</span>
                        <span>{{empresa_nome}}</span>
                        <span>{{empresa_cnpj}}</span>
                        <span>{{objeto_nome}}</span>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Cláusulas e Conteúdo do Contrato</label>
            <p class="text-xs text-gray-400 mb-2 italic">Dica: Você pode copiar e colar seu contrato do Word diretamente aqui.</p>
            <textarea id="conteudo_editor" name="conteudo"><?php echo htmlspecialchars($modelo['conteudo'] ?? ''); ?></textarea>
        </div>

        <div class="flex gap-4 pt-4 border-t">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar as alterações deste modelo?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-8 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg flex items-center">
                <i class="fas fa-save mr-2"></i> Salvar Modelo
            </button>

            <a href="list.php" class="bg-gray-500 text-white px-8 py-3 rounded-lg hover:bg-gray-600 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </form>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#conteudo_editor',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        branding: false,
        promotion: false,
        elementpath: false,
        relative_urls: false,
        remove_script_host: false,
        convert_urls: true,
        content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:14px }',
        setup: function (editor) {
            editor.on('init', function () {
                console.log('Editor initialized');
            });
        }
    });
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
