<?php
session_start();
require_once __DIR__ . '/../../models/CompanyModel.php';
require_once __DIR__ . '/../../helpers.php';

checkAuth();
if (!isSuperAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$model = new CompanyModel();
$company = null;
$id = $_GET['id'] ?? null;

if ($id) {
    $company = $model->getById($id);
    if (!$company) {
        header('Location: companies.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'razao_social' => $_POST['razao_social'] ?? '',
        'nome_fantasia' => $_POST['nome_fantasia'] ?? '',
        'cnpj' => $_POST['cnpj'] ?? '',
        'responsavel' => $_POST['responsavel'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'plan' => $_POST['plan'] ?? 'Pro',
        'status' => $_POST['status'] ?? 'active',
        'billing_status' => $_POST['billing_status'] ?? 'active',
        'setup_paid' => isset($_POST['setup_paid']) ? 1 : 0,
        'plan_price' => $_POST['plan_price'] ?? 0,
        'plan_interval' => $_POST['plan_interval'] ?? 'MONTHLY'
    ];

    if ($id) {
        if ($model->update($id, $data)) {
            header('Location: companies.php?success=updated');
            exit;
        }
    } else {
        $newId = $model->create($data); 
        if ($newId) {
            header('Location: companies.php?success=created');
            exit;
        }
    }
}

$pageTitle = $id ? 'Editar Empresa' : 'Nova Empresa';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8 p-4">
        <a href="companies.php" class="text-gray-500 hover:text-primary transition-colors flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para a lista
        </a>
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight"><?php echo $pageTitle; ?></h2>
        <p class="text-gray-500 mt-1">Configure os dados principais e o plano da empresa cliente.</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="" method="POST" class="divide-y divide-gray-100">
            <!-- Dados da Empresa -->
            <div class="p-8 space-y-6">
                <div class="flex items-center space-x-2 text-indigo-600 mb-2">
                    <i class="fas fa-id-card"></i>
                    <h3 class="font-bold uppercase tracking-wider text-xs">Informações Básicas</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Razão Social</label>
                        <input type="text" name="razao_social" required value="<?php echo htmlspecialchars($company['razao_social'] ?? ''); ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nome Fantasia</label>
                        <input type="text" name="nome_fantasia" value="<?php echo htmlspecialchars($company['nome_fantasia'] ?? ''); ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">CNPJ</label>
                        <input type="text" name="cnpj" id="cnpj_input" value="<?php echo htmlspecialchars($company['cnpj'] ?? ''); ?>" 
                               placeholder="00.000.000/0000-00" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all font-mono">
                    </div>
                </div>
            </div>

            <!-- Contato e Gestão -->
            <div class="p-8 space-y-6 bg-gray-50/30">
                <div class="flex items-center space-x-2 text-indigo-600 mb-2">
                    <i class="fas fa-headset"></i>
                    <h3 class="font-bold uppercase tracking-wider text-xs">Contato e Responsável</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nome do Responsável</label>
                        <input type="text" name="responsavel" value="<?php echo htmlspecialchars($company['responsavel'] ?? ''); ?>" 
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">E-mail de Contato</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($company['email'] ?? ''); ?>" 
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Telefone</label>
                        <input type="text" name="telefone" id="telefone_input" value="<?php echo htmlspecialchars($company['telefone'] ?? ''); ?>" 
                               placeholder="(00) 00000-0000" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>
                </div>
            </div>

            <!-- Plano e Status -->
            <div class="p-8 space-y-6">
                <div class="flex items-center space-x-2 text-indigo-600 mb-2">
                    <i class="fas fa-gem"></i>
                    <h3 class="font-bold uppercase tracking-wider text-xs">Configurações de Assinatura</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Plano Atual</label>
                        <select name="plan" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none">
                            <option value="Basic" <?php echo (isset($company['plan']) && $company['plan'] === 'Basic') ? 'selected' : ''; ?>>Basic</option>
                            <option value="Pro" <?php echo (isset($company['plan']) && $company['plan'] === 'Pro') ? 'selected' : ''; ?>>Pro</option>
                            <option value="Enterprise" <?php echo (isset($company['plan']) && $company['plan'] === 'Enterprise') ? 'selected' : ''; ?>>Enterprise</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Preço do Plano (R$)</label>
                        <input type="number" step="0.01" name="plan_price" value="<?php echo $company['plan_price'] ?? 79.00; ?>" 
                               class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ciclo</label>
                        <select name="plan_interval" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none">
                            <option value="MONTHLY" <?php echo (isset($company['plan_interval']) && $company['plan_interval'] === 'MONTHLY') ? 'selected' : ''; ?>>Mensal</option>
                            <option value="YEARLY" <?php echo (isset($company['plan_interval']) && $company['plan_interval'] === 'YEARLY') ? 'selected' : ''; ?>>Anual</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Status do Acesso</label>
                        <select name="status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none">
                            <option value="active" <?php echo (isset($company['status']) && $company['status'] === 'active') ? 'selected' : ''; ?>>Ativa</option>
                            <option value="inactive" <?php echo (isset($company['status']) && $company['status'] === 'inactive') ? 'selected' : ''; ?>>Inativa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Faturamento</label>
                        <select name="billing_status" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-primary outline-none transition-all appearance-none">
                            <option value="active" <?php echo (isset($company['billing_status']) && $company['billing_status'] === 'active') ? 'selected' : ''; ?>>Regular</option>
                            <option value="overdue" <?php echo (isset($company['billing_status']) && $company['billing_status'] === 'overdue') ? 'selected' : ''; ?>>Atrasado</option>
                            <option value="blocked" <?php echo (isset($company['billing_status']) && $company['billing_status'] === 'blocked') ? 'selected' : ''; ?>>Bloqueado</option>
                        </select>
                    </div>

                    <div class="flex items-center pt-8">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" name="setup_paid" value="1" <?php echo ($company['setup_paid'] ?? 0) ? 'checked' : ''; ?> 
                                   class="w-6 h-6 rounded-lg border-2 border-gray-300 text-primary focus:ring-primary transition">
                            <span class="text-sm font-bold text-gray-700">Implantação Paga</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-8 bg-gray-50 flex justify-end gap-4">
                <a href="companies.php" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 font-bold rounded-2xl hover:bg-gray-100 transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-12 py-3 bg-primary text-white font-bold rounded-2xl shadow-xl hover:opacity-90 transition-all transform hover:-translate-y-1">
                    <i class="fas fa-save mr-2"></i> <?php echo $id ? 'Salvar Alterações' : 'Cadastrar Empresa'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const cnpjInput = document.getElementById('cnpj_input');
    const telefoneInput = document.getElementById('telefone_input');

    const formatarCNPJ = (v) => {
        v = v.replace(/\D/g, "");
        if (v.length > 14) v = v.substring(0, 14);
        v = v.replace(/^(\d{2})(\d)/, "$1.$2");
        v = v.replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3");
        v = v.replace(/\.(\d{3})(\d)/, ".$1/$2");
        v = v.replace(/(\d{4})(\d{1,2})$/, "$1-$2");
        return v;
    };

    const formatarTelefone = (v) => {
        v = v.replace(/\D/g, "");
        if (v.length > 11) v = v.substring(0, 11);
        if (v.length === 11) v = v.replace(/^(\d{2})(\d{5})(\d{4})$/, "($1) $2-$3");
        else if (v.length === 10) v = v.replace(/^(\d{2})(\d{4})(\d{4})$/, "($1) $2-$3");
        return v;
    };

    cnpjInput?.addEventListener('input', (e) => e.target.value = formatarCNPJ(e.target.value));
    telefoneInput?.addEventListener('input', (e) => e.target.value = formatarTelefone(e.target.value));
});
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
