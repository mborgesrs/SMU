<?php
session_start();
require_once __DIR__ . '/../../controllers/UserController.php';
require_once __DIR__ . '/../../helpers.php';

checkAuth();
if (!isAdmin()) {
    header('Location: ../dashboard.php');
    exit;
}

$company_id = $_SESSION['company_id'];
if (isSuperAdmin() && isset($_GET['company_id'])) {
    $company_id = $_GET['company_id'];
}

$controller = new UserController($company_id);

// Handle delete
if (isset($_GET['delete'])) {
    if ($controller->destroy($_GET['delete'])) {
        header('Location: list.php?success=deleted');
    } else {
        header('Location: list.php?error=cannot_delete_self');
    }
    exit;
}

$pageTitle = 'Gestão de Usuários';
require_once __DIR__ . '/../layout/header.php';

// Get search parameter
$search = $_GET['search'] ?? '';
$usuarios = $controller->index($search);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Usuários</h2>
            <p class="text-gray-500">Gerencie os acessos dos funcionários da sua empresa.</p>
        </div>
        <a href="form.php<?php echo isset($_GET['company_id']) ? '?company_id='.$_GET['company_id'] : ''; ?>" class="bg-primary text-white px-6 py-3 rounded-xl hover:opacity-90 transition shadow-lg flex items-center">
            <i class="fas fa-plus mr-2"></i>Novo Usuário
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-4">
            <?php
            if ($_GET['success'] === 'created') echo 'Usuário criado com sucesso!';
            if ($_GET['success'] === 'updated') echo 'Usuário atualizado com sucesso!';
            if ($_GET['success'] === 'deleted') echo 'Usuário excluído com sucesso!';
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4">
            <?php
            if ($_GET['error'] === 'cannot_delete_self') echo 'Você não pode excluir seu próprio usuário!';
            ?>
        </div>
    <?php endif; ?>

    <!-- Search Bar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        <form method="GET" action="" class="flex gap-4">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                    placeholder="Buscar por nome ou e-mail..."
                    class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all">
            </div>
            <button type="submit" class="bg-slate-800 text-white px-8 py-3 rounded-xl hover:bg-slate-900 transition font-semibold">
                Buscar
            </button>
            <?php if ($search): ?>
                <a href="list.php" class="bg-gray-100 text-gray-600 px-6 py-3 rounded-xl hover:bg-gray-200 transition flex items-center">
                    <i class="fas fa-times mr-2"></i>Limpar
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Usuário</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">E-mail</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Perfil (Role)</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Data de Cadastro</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-users-slash text-5xl mb-4 text-gray-200"></i>
                                    <p class="text-lg font-medium">Nenhum usuário encontrado</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold mr-3">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($user['username']); ?></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php 
                                    $roleClass = 'bg-gray-100 text-gray-800';
                                    $roleLabel = 'Funcionário';
                                    if ($user['role'] === 'super_admin') {
                                        $roleClass = 'bg-purple-100 text-purple-800';
                                        $roleLabel = 'Super Admin';
                                    } elseif ($user['role'] === 'admin') {
                                        $roleClass = 'bg-indigo-100 text-indigo-800';
                                        $roleLabel = 'Administrador';
                                    }
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo $roleClass; ?>">
                                        <?php echo $roleLabel; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="form.php?id=<?php echo $user['id']; ?>" 
                                       class="text-primary hover:opacity-75 transition-colors mr-4 p-2 rounded-lg hover:bg-primary/5" 
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       onclick="return confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')"
                                       class="text-red-500 hover:text-red-700 transition-colors p-2 rounded-lg hover:bg-red-50"
                                       title="Excluir">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
