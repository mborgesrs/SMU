<?php
session_start();
require_once __DIR__ . '/../../models/ConfiguracaoModel.php';

$model = new ConfiguracaoModel();
$config = $model->getConfig();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'razao_social' => $_POST['razao_social'] ?? '',
        'nome_fantasia' => $_POST['nome_fantasia'] ?? '',
        'cnpj' => $_POST['cnpj'] ?? '',
        'responsavel' => $_POST['responsavel'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'email' => $_POST['email'] ?? '',
        'cep' => $_POST['cep'] ?? '',
        'logradouro' => $_POST['logradouro'] ?? '',
        'numero' => $_POST['numero'] ?? '',
        'complemento' => $_POST['complemento'] ?? '',
        'bairro' => $_POST['bairro'] ?? '',
        'cidade' => $_POST['cidade'] ?? '',
        'uf' => $_POST['uf'] ?? '',
        'primary_color' => $_POST['primary_color'] ?? '#1e293b',
        'secondary_color' => $_POST['secondary_color'] ?? '#334155'
    ];

    // Handle File Upload
    if (isset($_FILES['logotipo']) && $_FILES['logotipo']['error'] === UPLOAD_ERR_OK) {
        $companyId = $_SESSION['company_id'];
        $uploadDir = __DIR__ . '/../../assets/uploads/company_' . $companyId . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileExtension = pathinfo($_FILES['logotipo']['name'], PATHINFO_EXTENSION);
        $fileName = 'logo_' . time() . '.' . $fileExtension;
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['logotipo']['tmp_name'], $targetFile)) {
            $data['logotipo'] = 'assets/uploads/company_' . $companyId . '/' . $fileName;
        }
    }

    if ($model->update($data)) {
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">Configurações salvas com sucesso!</div>';
        $config = $model->getConfig(); // Refresh data
    } else {
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">Erro ao salvar configurações.</div>';
    }
}

$pageTitle = 'Configurações da Empresa';
include __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900">Configurações</h2>
            <p class="text-gray-500 mt-1">Gerencie os dados da sua empresa e identidade visual.</p>
        </div>
    </div>

    <?php echo $message; ?>

    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <form action="" method="POST" enctype="multipart/form-data" class="divide-y divide-gray-100">
            <!-- Seção Dados Principais -->
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center text-slate-600 mr-4">
                        <i class="fas fa-building text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Dados Principais</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Razão Social</label>
                        <input type="text" name="razao_social" required value="<?php echo htmlspecialchars($config['razao_social'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nome Fantasia</label>
                        <input type="text" name="nome_fantasia" value="<?php echo htmlspecialchars($config['nome_fantasia'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Responsável (CEO)</label>
                        <input type="text" name="responsavel" value="<?php echo htmlspecialchars($config['responsavel'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj_input" value="<?php echo htmlspecialchars($config['cnpj'] ?? ''); ?>" 
                               placeholder="00.000.000/0000-00" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Telefone</label>
                        <input type="text" name="telefone" id="telefone_input" value="<?php echo htmlspecialchars($config['telefone'] ?? ''); ?>" 
                               placeholder="(00) 00000-0000" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">E-mail Corporativo</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($config['email'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 focus:bg-white outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Seção Endereço -->
            <div class="p-6 bg-gray-50/30">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 mr-4">
                        <i class="fas fa-map-marker-alt text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Endereço</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">CEP</label>
                        <div class="relative">
                            <input type="text" name="cep" id="cep_input" 
                                   onkeypress="if(event.key === 'Enter') { event.preventDefault(); buscarEndereçoPorCEP(event); }" 
                                   value="<?php echo htmlspecialchars($config['cep'] ?? ''); ?>" 
                                   placeholder="00000-000" 
                                   class="w-full pl-4 pr-12 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all font-mono text-sm">
                            <button type="button" onclick="buscarEndereçoPorCEP(event)" 
                                    class="absolute right-1.5 top-1.5 bottom-1.5 px-2.5 bg-slate-50 hover:bg-slate-100 text-slate-600 rounded-lg transition-colors border border-slate-100" 
                                    title="Buscar CEP">
                                <i class="fas fa-search text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Logradouro / Rua</label>
                        <input type="text" name="logradouro" id="rua_field" value="<?php echo htmlspecialchars($config['logradouro'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Número</label>
                        <input type="text" name="numero" value="<?php echo htmlspecialchars($config['numero'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Bairro</label>
                        <input type="text" name="bairro" id="bairro_field" value="<?php echo htmlspecialchars($config['bairro'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-3">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Complemento</label>
                        <input type="text" name="complemento" value="<?php echo htmlspecialchars($config['complemento'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Cidade</label>
                        <input type="text" name="cidade" id="cidade_field" value="<?php echo htmlspecialchars($config['cidade'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">UF</label>
                        <input type="text" name="uf" id="uf_field" value="<?php echo htmlspecialchars($config['uf'] ?? ''); ?>" 
                               class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all text-center font-bold uppercase">
                    </div>
                </div>
            </div>

            <!-- Seção Identidade Visual (Logotipo) -->
            <div class="p-6 border-t border-gray-100">
                <div class="flex items-center mb-4">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center text-orange-600 mr-4">
                        <i class="fas fa-palette text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Identidade Visual</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Logotipo da Empresa</label>
                        <div class="flex items-center space-x-6 p-4 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200 hover:border-slate-300 transition-colors cursor-pointer group">
                            <div class="relative">
                                <?php if (!empty($config['logotipo'])): ?>
                                    <img src="<?php echo APP_URL . $config['logotipo']; ?>" alt="Logo" class="h-20 w-20 object-contain bg-white rounded-xl shadow-md p-2 border border-gray-100">
                                <?php else: ?>
                                    <div class="h-20 w-20 bg-white rounded-xl shadow-inner flex items-center justify-center text-gray-300 border border-gray-100">
                                        <i class="fas fa-image text-3xl"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="logotipo" accept="image/*" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700 cursor-pointer">
                                <p class="text-xs text-gray-400 mt-2">Formatos aceitos: PNG, JPG, SVG. Tamanho máximo: 2MB.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cor Principal</label>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <input type="color" name="primary_color" value="<?php echo $config['primary_color'] ?? '#1e293b'; ?>" 
                                       class="h-10 w-10 rounded cursor-pointer border-none bg-transparent">
                                <span class="text-sm font-mono text-gray-600"><?php echo $config['primary_color'] ?? '#1e293b'; ?></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Cor Secundária</label>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-xl border border-gray-200">
                                <input type="color" name="secondary_color" value="<?php echo $config['secondary_color'] ?? '#334155'; ?>" 
                                       class="h-10 w-10 rounded cursor-pointer border-none bg-transparent">
                                <span class="text-sm font-mono text-gray-600"><?php echo $config['secondary_color'] ?? '#334155'; ?></span>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <p class="text-xs text-gray-400">As cores serão aplicadas em todo o sistema para reforçar a identidade da sua marca.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Seção Assinatura e Faturamento (Tenant View) -->
            <div class="p-6 border-t border-gray-100 bg-slate-50/50">
                <div class="flex items-center mb-6">
                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mr-4">
                        <i class="fas fa-credit-card text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Assinatura e Plano</h3>
                </div>

                <?php
                // Fetch extra company info for the tenant
                $db = getDB();
                $stmt = $db->prepare("SELECT plan, billing_status, plan_price, plan_interval, setup_paid FROM companies WHERE id = ?");
                $stmt->execute([$_SESSION['company_id']]);
                $companyInfo = $stmt->fetch();
                ?>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Plano Atual</p>
                        <div class="flex items-center justify-between">
                            <h4 class="text-2xl font-black text-slate-800"><?php echo strtoupper($companyInfo['plan'] ?? 'Pro'); ?></h4>
                            <span class="px-3 py-1 bg-indigo-100 text-indigo-700 text-[10px] font-bold rounded-full">ATIVO</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-2 font-medium">R$ <?php echo number_format($companyInfo['plan_price'], 2, ',', '.'); ?> / <?php echo ($companyInfo['plan_interval'] == 'YEARLY' ? 'ano' : 'mês'); ?></p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <p class="text-xs font-bold text-gray-400 uppercase mb-2">Status Financeiro</p>
                        <div class="flex items-center space-x-2">
                            <?php if ($companyInfo['billing_status'] === 'active'): ?>
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                                <span class="font-bold text-green-700">Conta Regularizada</span>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
                                <span class="font-bold text-amber-700 uppercase"><?php echo $companyInfo['billing_status']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Implantação: <?php echo $companyInfo['setup_paid'] ? '<span class="text-green-600 font-bold">PAGA</span>' : '<span class="text-red-500 font-bold">PENDENTE</span>'; ?></p>
                    </div>

                    <div class="flex flex-col justify-center space-y-3">
                        <a href="../billing/status.php" class="w-full bg-indigo-600 text-white text-center py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-md flex items-center justify-center">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>Gerenciar Faturas
                        </a>
                        <button type="button" onclick="Swal.fire({
                            title: 'Cancelar Assinatura?',
                            text: 'Sentiremos sua falta! Para cancelar sua conta SMU, entre em contato com nosso suporte para processarmos a baixa e exportação de seus dados.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Falar com Suporte',
                            cancelButtonText: 'Continuar no SMU',
                            confirmButtonColor: '#1e293b'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.open('mailto:suporte@smu.com.br?subject=Solicitação de Cancelamento - ' + '<?php echo addslashes($config['razao_social']); ?>');
                            }
                        })" class="w-full border-2 border-red-100 text-red-500 py-3 rounded-xl font-bold hover:bg-red-50 transition flex items-center justify-center">
                            <i class="fas fa-times-circle mr-2"></i>Cancelar Plano
                        </button>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 flex justify-end">
                <button type="submit" class="px-10 py-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-2xl shadow-lg hover:shadow-slate-200 transition-all transform hover:-translate-y-1 active:scale-95">
                    <i class="fas fa-save mr-2"></i> Salvar Todas as Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Função para aplicar máscaras de forma robusta
function aplicarMascaras() {
    const cnpjInput = document.getElementById('cnpj_input');
    const telefoneInput = document.getElementById('telefone_input');
    const cepInput = document.getElementById('cep_input');

    const formatarDocumento = (v) => {
        v = v.replace(/\D/g, "");
        if (v.length > 14) v = v.substring(0, 14);

        if (v.length <= 11) {
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d)/, "$1.$2");
            v = v.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        } else {
            v = v.replace(/^(\d{2})(\d)/, "$1.$2");
            v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
            v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
            v = v.replace(/(\d{4})(\d{1,2})$/, "$1-$2");
        }
        return v;
    };

    const formatarTelefone = (v) => {
        v = v.replace(/\D/g, "");
        if (v.length > 11) v = v.substring(0, 11);
        
        if (v.length === 11) {
            v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
        } else if (v.length === 10) {
            v = v.replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
        } else if (v.length > 2) {
            v = v.replace(/^(\d{2})(\d)/, "($1) $2");
        } else if (v.length > 0) {
            v = v.replace(/^(\d)/, "($1");
        }
        return v;
    };

    const formatarCEP = (v) => {
        v = v.replace(/\D/g, "");
        if (v.length > 8) v = v.substring(0, 8);
        v = v.replace(/^(\d{5})(\d)/, "$1-$2");
        return v;
    };

    cnpjInput?.addEventListener('input', (e) => { 
        e.target.value = formatarDocumento(e.target.value); 
    });
    
    telefoneInput?.addEventListener('input', (e) => { 
        e.target.value = formatarTelefone(e.target.value); 
    });

    telefoneInput?.addEventListener('blur', (e) => {
        e.target.value = formatarTelefone(e.target.value);
    });
    
    cepInput?.addEventListener('input', (e) => { 
        e.target.value = formatarCEP(e.target.value); 
    });

    cepInput?.addEventListener('blur', (e) => {
        e.target.value = formatarCEP(e.target.value);
    });

    // Aplicar máscaras nos valores iniciais
    if (cnpjInput && cnpjInput.value) cnpjInput.value = formatarDocumento(cnpjInput.value);
    if (telefoneInput && telefoneInput.value) telefoneInput.value = formatarTelefone(telefoneInput.value);
    if (cepInput && cepInput.value) cepInput.value = formatarCEP(cepInput.value);
}

// Inicializar máscaras ao carregar
document.addEventListener('DOMContentLoaded', aplicarMascaras);

async function buscarEndereçoPorCEP(event) {
    const cepField = document.getElementById('cep_input');
    const cep = cepField.value.replace(/\D/g, '');
    
    if (cep.length !== 8) {
        alert('Por favor, informe um CEP válido com 8 dígitos.');
        return;
    }

    // Feedback visual
    const btnSearch = event.currentTarget.tagName === 'BUTTON' ? event.currentTarget : document.querySelector('button[onclick*="buscarEndereçoPorCEP"]');
    const icon = btnSearch.querySelector('i');
    const originalIconClass = icon.className;
    icon.className = 'fas fa-spinner fa-spin';
    btnSearch.disabled = true;

    try {
        const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
        const data = await response.json();

        if (data.erro) {
            alert('CEP não encontrado. Verifique os números e tente novamente.');
        } else {
            document.getElementById('rua_field').value = data.logradouro;
            document.getElementById('bairro_field').value = data.bairro;
            document.getElementById('cidade_field').value = data.localidade;
            document.getElementById('uf_field').value = data.uf;
            
            // Focar no campo de número após preencher
            const numField = document.getElementsByName('numero')[0];
            if(numField) numField.focus();
        }
    } catch (error) {
        console.error('Erro na requisição ViaCEP:', error);
        alert('Ocorreu um erro ao consultar o CEP. Verifique sua conexão.');
    } finally {
        icon.className = originalIconClass;
        btnSearch.disabled = false;
    }
}
</script>

<?php include __DIR__ . '/../layout/footer.php'; ?>
