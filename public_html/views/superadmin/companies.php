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

// Handle delete
if (isset($_GET['delete'])) {
    if ($model->delete($_GET['delete'])) {
        header('Location: companies.php?success=deleted');
    } else {
        header('Location: companies.php?error=cannot_delete');
    }
    exit;
}

$pageTitle = 'Gestão de Empresas (Super Admin)';
require_once __DIR__ . '/../layout/header.php';

$search = $_GET['search'] ?? '';
$companies = $model->getAll($search);
?>

<div class="max-w-7xl mx-auto py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Painel Super Admin</h2>
            <p class="text-gray-500 mt-1">Gerenciamento centralized de todas as empresas e planos do sistema.</p>
        </div>
        <a href="company_form.php" class="bg-primary text-white px-8 py-3 rounded-2xl hover:opacity-90 transition shadow-xl flex items-center font-bold">
            <i class="fas fa-plus-circle mr-2"></i>Nova Empresa
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-2xl mb-6 shadow-sm flex items-center">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span class="font-medium">
                <?php
                if ($_GET['success'] === 'created') echo 'Empresa cadastrada com sucesso!';
                if ($_GET['success'] === 'updated') echo 'Empresa atualizada com sucesso!';
                if ($_GET['success'] === 'deleted') echo 'Empresa removida com sucesso!';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Total de Empresas</p>
                    <h3 class="text-2xl font-bold text-gray-800"><?php echo count($companies); ?></h3>
                </div>
                <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-building text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Empresas Ativas</p>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php 
                        echo count(array_filter($companies, function($c) { return $c['status'] === 'active'; })); 
                        ?>
                    </h3>
                </div>
                <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-double text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase">Total Usuários</p>
                    <h3 class="text-2xl font-bold text-gray-800">
                        <?php 
                        echo array_sum(array_column($companies, 'user_count')); 
                        ?>
                    </h3>
                </div>
                <div class="w-12 h-12 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-8">
        <form method="GET" action="" class="flex gap-4">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Buscar por razão social, fantasia ou CNPJ..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all">
            </div>
            <button type="submit" class="bg-slate-800 text-white px-8 py-3 rounded-xl hover:bg-slate-900 transition font-semibold">
                Filtrar
            </button>
            <?php if ($search): ?>
                <a href="companies.php" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-xl hover:bg-gray-200 transition flex items-center">
                    <i class="fas fa-times mr-2"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Companies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($companies as $company): ?>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 flex flex-col">
                <div class="p-6 flex-1">
                    <div class="flex items-start justify-between mb-4">
                        <div class="h-12 w-12 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400 overflow-hidden border border-gray-50">
                            <?php if (!empty($company['logotipo'])): ?>
                                <img src="<?php echo APP_URL . $company['logotipo']; ?>" class="h-full w-full object-contain p-1">
                            <?php else: ?>
                                <i class="fas fa-building text-2xl"></i>
                            <?php endif; ?>
                        </div>
                            <span class="px-3 py-1 text-[10px] font-extrabold rounded-full mb-2 <?php echo $company['billing_status'] === 'active' ? 'bg-green-100 text-green-700' : ($company['billing_status'] === 'overdue' ? 'bg-amber-100 text-amber-700' : 'bg-red-100 text-red-700'); ?>">
                                <i class="fas fa-wallet mr-1"></i><?php echo strtoupper($company['billing_status']); ?>
                            </span>
                            <span class="px-3 py-1 text-[10px] font-extrabold rounded-full mb-2 <?php echo $company['status'] === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-500'; ?>">
                                <i class="fas fa-power-off mr-1"></i><?php echo strtoupper($company['status']); ?>
                            </span>
                            <span class="px-3 py-1 text-[10px] font-extrabold rounded-full bg-slate-100 text-slate-600">
                                <i class="fas fa-tag mr-1"></i><?php echo $company['plan']; ?>
                            </span>
                        </div>
                    </div>
                    
                    <h4 class="text-lg font-bold text-gray-800 line-clamp-1 mb-1" title="<?php echo htmlspecialchars($company['razao_social']); ?>">
                        <?php echo htmlspecialchars($company['nome_fantasia'] ?: $company['razao_social']); ?>
                    </h4>
                    <p class="text-sm text-gray-500 mb-4"><?php echo formatarCPF_CNPJ($company['cnpj']); ?></p>
                    
                    <div class="space-y-3 border-t border-gray-50 pt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Implantação:</span>
                            <span class="font-bold <?php echo $company['setup_paid'] ? 'text-green-600' : 'text-red-500'; ?>">
                                <?php echo $company['setup_paid'] ? '<i class="fas fa-check-circle"></i> PAGO' : '<i class="fas fa-clock"></i> PENDENTE'; ?>
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">Assinatura:</span>
                            <span class="font-bold text-gray-800 font-mono">
                                R$ <?php echo number_format($company['plan_price'], 2, ',', '.'); ?>/<?php echo ($company['plan_interval'] == 'YEARLY' ? 'ano' : 'mês'); ?>
                            </span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-users w-6 text-gray-400"></i>
                            <span><strong class="text-gray-900"><?php echo $company['user_count']; ?></strong> usuários</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex flex-wrap gap-3 justify-between items-center border-t border-gray-100">
                    <div class="flex gap-3">
                        <a href="company_form.php?id=<?php echo $company['id']; ?>" class="bg-white border border-gray-200 p-2 rounded-lg text-indigo-600 hover:bg-indigo-50 transition shadow-sm" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="../usuarios/list.php?company_id=<?php echo $company['id']; ?>" class="bg-white border border-gray-200 p-2 rounded-lg text-slate-600 hover:bg-slate-50 transition shadow-sm" title="Usuários">
                            <i class="fas fa-users"></i>
                        </a>
                        <?php if ($company['id'] != 1): ?>
                        <button onclick="toggleSetup(<?php echo $company['id']; ?>, <?php echo $company['setup_paid'] ? '0' : '1'; ?>)" 
                                class="bg-white border border-gray-200 p-2 rounded-lg <?php echo $company['setup_paid'] ? 'text-green-600' : 'text-gray-400'; ?> hover:bg-gray-50 transition shadow-sm" 
                                title="Alternar Status Implantação">
                            <i class="fas fa-tools"></i>
                        </button>
                        <button onclick="toggleBilling(<?php echo $company['id']; ?>, '<?php echo $company['billing_status'] === 'active' ? 'blocked' : 'active'; ?>')" 
                                class="bg-white border border-gray-200 p-2 rounded-lg <?php echo $company['billing_status'] === 'active' ? 'text-green-600' : 'text-red-600'; ?> hover:bg-gray-50 transition shadow-sm" 
                                title="Bloquear/Liberar Faturamento">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php if ($company['id'] != 1): ?>
                    <a href="?delete=<?php echo $company['id']; ?>" 
                       onclick="return confirm('ATENÇÃO: Excluir esta empresa apagará TODOS os dados relacionados a ela. Tem certeza?')"
                       class="text-gray-300 hover:text-red-500 p-2 transition-colors">
                        <i class="fas fa-trash-alt"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Scripts for actions -->
    <script>
    function toggleSetup(id, status) {
        if(confirm('Deseja alterar o status de pagamento da implantação?')) {
            window.location.href = 'company_actions.php?action=toggle_setup&id=' + id + '&status=' + status;
        }
    }
    function toggleBilling(id, status) {
        const msg = status === 'active' ? 'Liberar acesso para esta empresa?' : 'Bloquear acesso por falta de pagamento?';
        if(confirm(msg)) {
            window.location.href = 'company_actions.php?action=update_billing&id=' + id + '&status=' + status;
        }
    }
    </script>
    
    <?php if (empty($companies)): ?>
        <div class="bg-white rounded-3xl p-16 text-center shadow-sm border border-gray-100">
            <i class="fas fa-search-minus text-6xl text-gray-200 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-800">Nenhuma empresa encontrada</h3>
            <p class="text-gray-500">Tente ajustar seus filtros de busca ou cadastre uma nova empresa.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
