<?php
$pageTitle = 'Usuários';
require_once __DIR__ . '/../layout/header.php';
require_once __DIR__ . '/../../models/UserModel.php';

$model = new UserModel();
$search = $_GET['search'] ?? '';
$users = $model->getAll($search);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-3xl font-extrabold text-gray-900">Usuários</h2>
            <p class="text-gray-500 mt-1">Gerencie os acessos à sua empresa.</p>
        </div>
        <a href="form.php" class="bg-primary text-white px-6 py-3 rounded-xl hover:opacity-90 transition shadow-lg flex items-center">
            <i class="fas fa-plus mr-2"></i> Novo Usuário
        </a>
    </div>

    <!-- Filtros -->
    <div class="bg-white p-4 rounded-xl shadow-sm mb-6 border border-gray-100">
        <form method="GET" class="flex gap-4">
            <div class="flex-1">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Buscar por nome ou e-mail..." 
                       class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary outline-none transition-all">
            </div>
            <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition">
                <i class="fas fa-search mr-2"></i> Buscar
            </button>
        </form>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100 text-gray-600 uppercase text-xs font-bold">
                    <tr>
                        <th class="px-6 py-4">Usuário</th>
                        <th class="px-6 py-4">E-mail</th>
                        <th class="px-6 py-4">Nível</th>
                        <th class="px-6 py-4">Criado em</th>
                        <th class="px-6 py-4 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $u): ?>
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 mr-3">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="font-bold text-gray-800"><?php echo htmlspecialchars($u['username']); ?></span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold <?php echo $u['role'] === 'super_admin' ? 'bg-purple-100 text-purple-700' : ($u['role'] === 'admin' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700'); ?>">
                                        <?php echo ucfirst($u['role']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-500 text-sm"><?php echo date('d/m/Y H:i', strtotime($u['created_at'])); ?></td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex justify-center space-x-2">
                                        <a href="form.php?id=<?php echo $u['id']; ?>" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                            <button onclick="deleteUser(<?php echo $u['id']; ?>)" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function deleteUser(id) {
    Swal.fire({
        title: 'Tem certeza?',
        text: "Esta ação não poderá ser revertida!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'var(--primary-color)',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sim, excluir!',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete.php?id=${id}`;
        }
    })
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
