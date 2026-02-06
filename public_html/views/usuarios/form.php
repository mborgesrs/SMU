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
$user = null;
$id = $_GET['id'] ?? null;

if ($id) {
    $user = $controller->show($id);
    if (!$user) {
        header('Location: list.php' . ($company_id != $_SESSION['company_id'] ? '?company_id='.$company_id : ''));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'username' => $_POST['username'] ?? '',
        'email' => $_POST['email'] ?? '',
        'role' => $_POST['role'] ?? 'user',
        'password' => $_POST['password'] ?? ''
    ];

    if ($id) {
        if ($controller->update($id, $data)) {
            header('Location: list.php?success=updated' . ($company_id != $_SESSION['company_id'] ? '&company_id='.$company_id : ''));
            exit;
        }
    } else {
        if ($controller->store($data)) {
            header('Location: list.php?success=created' . ($company_id != $_SESSION['company_id'] ? '&company_id='.$company_id : ''));
            exit;
        }
    }
}

$pageTitle = $id ? 'Editar Usuário' : 'Novo Usuário';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-3xl mx-auto py-8">
    <div class="mb-8">
        <a href="list.php<?php echo ($company_id != $_SESSION['company_id']) ? '?company_id='.$company_id : ''; ?>" class="text-gray-500 hover:text-primary transition-colors flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Voltar para a lista
        </a>
        <h2 class="text-3xl font-bold text-gray-800"><?php echo $pageTitle; ?></h2>
        <p class="text-gray-500">Preencha as informações de acesso do usuário.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <form action="" method="POST" class="p-8 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nome de Usuário (Login)</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="username" required value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all"
                               placeholder="Ex: joao.silva">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">E-mail</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all"
                               placeholder="email@empresa.com">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Perfil de Acesso</label>
                    <div class="relative">
                        <i class="fas fa-user-tag absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <select name="role" required class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all appearance-none">
                            <option value="user" <?php echo (isset($user['role']) && $user['role'] === 'user') ? 'selected' : ''; ?>>Funcionário (Apenas visualização/edição)</option>
                            <option value="admin" <?php echo (isset($user['role']) && $user['role'] === 'admin') ? 'selected' : ''; ?>>Administrador (Gestão total da empresa)</option>
                            <?php if (isSuperAdmin()): ?>
                                <option value="super_admin" <?php echo (isset($user['role']) && $user['role'] === 'super_admin') ? 'selected' : ''; ?>>Super Admin (Gestão do Sistema)</option>
                            <?php endif; ?>
                        </select>
                        <i class="fas fa-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-bold text-gray-700 mb-2">
                        Senha <?php echo $id ? '(Deixe em branco para não alterar)' : ''; ?>
                    </label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" <?php echo $id ? '' : 'required'; ?> 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary focus:bg-white outline-none transition-all"
                               placeholder="********">
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100 flex justify-end gap-4">
                <a href="list.php" class="px-8 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-all">
                    Cancelar
                </a>
                <button type="submit" class="px-10 py-3 bg-primary text-white font-bold rounded-xl shadow-lg hover:opacity-90 transition-all transform hover:-translate-y-1">
                    <i class="fas fa-save mr-2"></i> <?php echo $id ? 'Salvar Alterações' : 'Criar Usuário'; ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
