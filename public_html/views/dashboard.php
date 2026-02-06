<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/layout/header.php';
require_once __DIR__ . '/../db.php';

// Get current month statistics
$currentMonth = date('Y-m');
$db = getDB();

// Count contracts: Statuses are global, Total is by month
$stmt = $db->prepare("
    SELECT 
        COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
        COUNT(CASE WHEN status = 'ativo' THEN 1 END) as ativos,
        COUNT(CASE WHEN status = 'encerrado' THEN 1 END) as encerrados,
        COUNT(CASE WHEN DATE_FORMAT(created_at, '%Y-%m') = ? THEN 1 END) as total_mes
    FROM contratos 
    WHERE company_id = ?
");
$stmt->execute([$currentMonth, $_SESSION['company_id']]);
$stats = $stmt->fetch();
?>

<div class="max-w-7xl mx-auto">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Dashboard</h2>
        <p class="text-gray-600 mt-2">Bem-vindo ao Sistema de Controle de Maternidade</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Contracts -->
        <div class="rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-white opacity-80 text-sm font-medium">Total do Mês</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo $stats['total_mes'] ?? 0; ?></h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-file-contract text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Pending Contracts -->
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-medium">Contratos Pendentes</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo $stats['pendentes'] ?? 0; ?></h3>
                </div>
                <div class="bg-amber-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-clock text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Contracts -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Contratos Ativos</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo $stats['ativos'] ?? 0; ?></h3>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Enclosed/Finished Contracts -->
        <div class="bg-gradient-to-br from-slate-500 to-slate-600 rounded-xl shadow-lg p-6 text-white transform hover:scale-105 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-slate-100 text-sm font-medium">Contratos Encerrados</p>
                    <h3 class="text-4xl font-bold mt-2"><?php echo $stats['encerrados'] ?? 0; ?></h3>
                </div>
                <div class="bg-slate-400 bg-opacity-30 rounded-full p-4">
                    <i class="fas fa-archive text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 pb-12">
        <a href="<?php echo APP_URL; ?>views/clientes/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-indigo-100 rounded-lg p-4 group-hover:bg-indigo-200 transition">
                    <i class="fas fa-users text-indigo-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Clientes</h3>
                    <p class="text-gray-600 text-sm">Gerenciar clientes</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/dependentes/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-blue-100 rounded-lg p-4 group-hover:bg-blue-200 transition">
                    <i class="fas fa-user-friends text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Dependentes</h3>
                    <p class="text-gray-600 text-sm">Gerenciar dependentes</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/products/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-green-100 rounded-lg p-4 group-hover:bg-green-200 transition">
                    <i class="fas fa-box text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Produtos/Serviços</h3>
                    <p class="text-gray-600 text-sm">Gerenciar produtos</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/financeiro/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-yellow-100 rounded-lg p-4 group-hover:bg-yellow-200 transition">
                    <i class="fas fa-dollar-sign text-yellow-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Financeiro</h3>
                    <p class="text-gray-600 text-sm">Gerenciar finanças</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/contas/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-orange-100 rounded-lg p-4 group-hover:bg-orange-200 transition">
                    <i class="fas fa-book text-orange-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Contas</h3>
                    <p class="text-gray-600 text-sm">Plano de contas</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/contratos/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-purple-100 rounded-lg p-4 group-hover:bg-purple-200 transition">
                    <i class="fas fa-file-contract text-purple-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Contratos</h3>
                    <p class="text-gray-600 text-sm">Gerenciar contratos</p>
                </div>
            </div>
        </a>

        <a href="<?php echo APP_URL; ?>views/objetos/list.php" class="bg-white rounded-xl shadow-md p-6 hover:shadow-xl transition group">
            <div class="flex items-center space-x-4">
                <div class="bg-pink-100 rounded-lg p-4 group-hover:bg-pink-200 transition">
                    <i class="fas fa-clipboard-list text-pink-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 text-lg">Objetos</h3>
                    <p class="text-gray-600 text-sm">Gerenciar objetos</p>
                </div>
            </div>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/layout/footer.php'; ?>
