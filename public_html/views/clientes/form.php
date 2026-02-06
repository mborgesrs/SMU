<?php
require_once __DIR__ . '/../../controllers/ClienteController.php';

$controller = new ClienteController();
$cliente = null;
$isEdit = false;

// Check if editing
if (isset($_GET['id'])) {
    $cliente = $controller->show($_GET['id']);
    $isEdit = true;
}

// Handle form submission BEFORE including header
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isEdit) {
        $controller->update($_GET['id'], $_POST);
        header('Location: form.php?id=' . $_GET['id'] . '&success=updated');
    } else {
        $newId = $controller->store($_POST);
        if ($newId) {
            header('Location: form.php?id=' . $newId . '&success=created');
        } else {
            header('Location: list.php?error=failed');
        }
    }
    exit;
}

// Now include header after POST processing
$pageTitle = 'Cliente';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800">
            <?php echo $isEdit ? 'Editar Cliente' : 'Novo Cliente'; ?>
        </h2>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            Operação realizada com sucesso!
        </div>
    <?php endif; ?>

    <form method="POST" class="bg-white rounded-lg shadow-md p-6 space-y-6">
        <!-- Basic Information -->
        <div class="border-b pb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-info-circle mr-2 text-slate-500"></i> Informações Básicas
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="nome" required
                        value="<?php echo htmlspecialchars($cliente['nome'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome Fantasia</label>
                    <input type="text" name="fantasia"
                        value="<?php echo htmlspecialchars($cliente['fantasia'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contato</label>
                    <input type="text" name="contato"
                        value="<?php echo htmlspecialchars($cliente['contato'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pessoa</label>
                    <select name="tipo_pessoa" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                        <option value="Fisica" <?php echo ($cliente['tipo_pessoa'] ?? '') === 'Fisica' ? 'selected' : ''; ?>>Física</option>
                        <option value="Juridica" <?php echo ($cliente['tipo_pessoa'] ?? '') === 'Juridica' ? 'selected' : ''; ?>>Jurídica</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Divisão</label>
                    <select name="divisao" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                        <option value="clientes" <?php echo ($cliente['divisao'] ?? 'clientes') === 'clientes' ? 'selected' : ''; ?>>Clientes</option>
                        <option value="fornecedores" <?php echo ($cliente['divisao'] ?? '') === 'fornecedores' ? 'selected' : ''; ?>>Fornecedores</option>
                        <option value="colaboradores" <?php echo ($cliente['divisao'] ?? '') === 'colaboradores' ? 'selected' : ''; ?>>Colaboradores</option>
                        <option value="representantes" <?php echo ($cliente['divisao'] ?? '') === 'representantes' ? 'selected' : ''; ?>>Representantes</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CPF/CNPJ</label>
                    <input type="text" name="cpf_cnpj" class="cpf-cnpj-mask w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        value="<?php echo htmlspecialchars($cliente['cpf_cnpj'] ?? ''); ?>">
                </div>

                <div class="pj-field <?php echo ($cliente['tipo_pessoa'] ?? 'Fisica') === 'Fisica' ? 'hidden' : ''; ?>">
                    <label class="block text-sm font-medium text-gray-700 mb-1">IE</label>
                    <input type="text" name="ie"
                        value="<?php echo htmlspecialchars($cliente['ie'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div class="pj-field <?php echo ($cliente['tipo_pessoa'] ?? 'Fisica') === 'Fisica' ? 'hidden' : ''; ?>">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                    <input type="text" name="insc_mun"
                        value="<?php echo htmlspecialchars($cliente['insc_mun'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div class="flex items-end pb-2">
                    <label class="flex items-center cursor-pointer group">
                        <div class="relative">
                            <input type="checkbox" name="ativo" value="1" <?php echo ($cliente['ativo'] ?? 1) ? 'checked' : ''; ?> class="sr-only">
                            <div class="w-10 h-6 bg-gray-200 rounded-full shadow-inner transition-colors group-[&:has(input:checked)]:bg-indigo-600"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-4 h-4 rounded-full shadow transition-transform group-[&:has(input:checked)]:translate-x-4"></div>
                        </div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Status Ativo</span>
                    </label>
                </div>
            </div>

            <!-- PF Specific Fields -->
            <div id="pf_fields" class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4 <?php echo ($cliente['tipo_pessoa'] ?? 'Fisica') === 'Juridica' ? 'hidden' : ''; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado Civil</label>
                    <select name="estado_civil" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                        <option value="">Selecione...</option>
                        <?php
                        $estados = ['Solteiro', 'Casado', 'Divorciado', 'Viúvo', 'Separado'];
                        $atual = $cliente['estado_civil'] ?? '';
                        foreach ($estados as $estado) {
                            $selected = $estado === $atual ? 'selected' : '';
                            echo "<option value=\"$estado\" $selected>$estado</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">RG</label>
                    <input type="text" name="rg"
                        value="<?php echo htmlspecialchars($cliente['rg'] ?? ''); ?>"
                        class="rg-mask w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="00.000.000-0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data de Nascimento</label>
                    <input type="date" name="dt_nascto"
                        value="<?php echo htmlspecialchars($cliente['dt_nascto'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIT / PIS / PASEP</label>
                    <input type="text" name="nit_pis_pasep"
                        value="<?php echo htmlspecialchars($cliente['nit_pis_pasep'] ?? ''); ?>"
                        class="pis-mask w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="000.00000.00-0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Naturalidade</label>
                    <input type="text" name="naturalidade"
                        value="<?php echo htmlspecialchars($cliente['naturalidade'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Pai</label>
                    <input type="text" name="nome_pai"
                        value="<?php echo htmlspecialchars($cliente['nome_pai'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Mãe</label>
                    <input type="text" name="nome_mae"
                        value="<?php echo htmlspecialchars($cliente['nome_mae'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>
            </div>
        </div>

        <!-- Address -->
        <div class="border-b pb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-map-marker-alt mr-2 text-slate-500"></i> Endereço
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                    <input type="text" name="cep" id="cep" class="cep-mask w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        value="<?php echo htmlspecialchars($cliente['cep'] ?? ''); ?>">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="endereco" id="endereco"
                        value="<?php echo htmlspecialchars($cliente['endereco'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                    <input type="text" name="numero" id="numero"
                        value="<?php echo htmlspecialchars($cliente['numero'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>



                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                    <input type="text" name="bairro" id="bairro"
                        value="<?php echo htmlspecialchars($cliente['bairro'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Município</label>
                    <input type="text" name="municipio" id="municipio"
                        value="<?php echo htmlspecialchars($cliente['municipio'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">UF</label>
                    <input type="text" name="uf" id="uf" maxlength="2"
                        value="<?php echo htmlspecialchars($cliente['uf'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">País</label>
                    <input type="text" name="cd_pais"
                        value="<?php echo htmlspecialchars($cliente['cd_pais'] ?? 'BRASIL'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Complemento</label>
                    <input type="text" name="complemento"
                        value="<?php echo htmlspecialchars($cliente['complemento'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none">
                </div>
            </div>
        </div>

        <!-- Contact & Additional -->
        <div class="pb-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                <i class="fas fa-phone-alt mr-2 text-slate-500"></i> Contatos
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone Fixo</label>
                    <input type="text" name="telefone" id="telefone_mask"
                        value="<?php echo htmlspecialchars($cliente['telefone'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="(00) 0000-0000">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Celular / WhatsApp</label>
                    <input type="text" name="celular" id="celular_mask"
                        value="<?php echo htmlspecialchars($cliente['celular'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="(00) 00000-0000">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input type="email" name="email"
                        value="<?php echo htmlspecialchars($cliente['email'] ?? ''); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="email@exemplo.com">
                </div>

                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observações do Cliente</label>
                    <textarea name="observacoes" rows="2"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 transition-all outline-none"
                        placeholder="Informações adicionais sobre o cliente..."><?php echo htmlspecialchars($cliente['observacoes'] ?? ''); ?></textarea>
                </div>
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex gap-4 pt-4 border-t">
            <button type="submit" 
                    onclick="return confirmAction(event, 'Deseja salvar as alterações deste cliente?', 'question', 'Sim, salvar!', '#1e293b')"
                    class="bg-slate-800 text-white px-8 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg flex items-center">
                <i class="fas fa-save mr-2"></i> Salvar Cliente
            </button>

            <?php if ($isEdit): ?>
                <a href="../dependentes/list.php?id_cliente=<?php echo $cliente['id']; ?>" 
                   class="bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 transition shadow-lg flex items-center">
                    <i class="fas fa-child mr-2"></i> Listar Dependentes
                </a>
                <a href="../dependentes/form.php?id_cliente=<?php echo $cliente['id']; ?>" 
                   class="bg-emerald-600 text-white px-8 py-3 rounded-lg hover:bg-emerald-700 transition shadow-lg flex items-center">
                    <i class="fas fa-plus mr-2"></i> Novo Dependente
                </a>
            <?php endif; ?>

            <a href="list.php" class="bg-gray-500 text-white px-8 py-3 rounded-lg hover:bg-gray-600 transition flex items-center">
                <i class="fas fa-arrow-left mr-2"></i> Voltar
            </a>
        </div>
    </form>
</div>

<script>
// Máscara para PIS/PASEP: ###.#####.##-#
function aplicarMascaraPIS() {
    const pisInputs = document.querySelectorAll('.pis-mask');
    
    pisInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, "");
            
            if (v.length > 11) v = v.substring(0, 11);
            
            if (v.length > 10) {
                v = v.replace(/^(\d{3})(\d{5})(\d{2})(\d{1})$/, "$1.$2.$3-$4");
            } else if (v.length > 8) {
                v = v.replace(/^(\d{3})(\d{5})(\d{0,2})$/, "$1.$2.$3");
            } else if (v.length > 3) {
                v = v.replace(/^(\d{3})(\d{0,5})$/, "$1.$2");
            }
            
            e.target.value = v;
        });
    });
}

// Máscara para RG: ##.###.###-#
function aplicarMascaraRG() {
    const rgInputs = document.querySelectorAll('.rg-mask');
    
    rgInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let v = e.target.value.replace(/\D/g, "");
            
            if (v.length > 9) v = v.substring(0, 9);
            
            if (v.length > 8) {
                v = v.replace(/^(\d{2})(\d{3})(\d{3})(\d{1})$/, "$1.$2.$3-$4");
            } else if (v.length > 5) {
                v = v.replace(/^(\d{2})(\d{3})(\d{0,3})$/, "$1.$2.$3");
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,3})$/, "$1.$2");
            }
            
            e.target.value = v;
        });
    });
}
// Máscaras para os campos de telefone
function aplicarMascarasTelefone() {
    const foneInput = document.getElementById('telefone_mask');
    const celInput = document.getElementById('celular_mask');

    const formatar = (v, isCelular) => {
        v = v.replace(/\D/g, "");
        if (isCelular) {
            if (v.length > 11) v = v.substring(0, 11);
            if (v.length > 10) v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
            else if (v.length > 5) v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, "($1) $2-$3");
            else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,5})$/, "($1) $2");
        } else {
            if (v.length > 10) v = v.substring(0, 10);
            if (v.length > 5) v = v.replace(/^(\d{2})(\d{4})(\d{0,4})$/, "($1) $2-$3");
            else if (v.length > 2) v = v.replace(/^(\d{2})(\d{0,4})$/, "($1) $2");
        }
        return v;
    };

    foneInput?.addEventListener('input', (e) => { e.target.value = formatar(e.target.value, false); });
    celInput?.addEventListener('input', (e) => { e.target.value = formatar(e.target.value, true); });
}

// Toggle visibility of fields based on person type
function setupPFFieldsToggle() {
    const tipoPessoaSelect = document.querySelector('select[name="tipo_pessoa"]');
    const pfFields = document.getElementById('pf_fields');
    const pjFields = document.querySelectorAll('.pj-field');

    const toggle = () => {
        const isFisica = tipoPessoaSelect.value === 'Fisica';
        
        // Toggle PF fields block
        if (pfFields) {
            pfFields.classList.toggle('hidden', !isFisica);
        }
        
        // Toggle PJ individual fields
        pjFields.forEach(field => {
            field.classList.toggle('hidden', isFisica);
        });
    };

    tipoPessoaSelect?.addEventListener('change', toggle);
    // Initial call
    toggle();
}

document.addEventListener('DOMContentLoaded', () => {
    aplicarMascarasTelefone();
    aplicarMascaraPIS();
    aplicarMascaraRG();
    setupPFFieldsToggle();
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
