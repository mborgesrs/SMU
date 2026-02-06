<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ConfiguracaoModel.php';
require_once __DIR__ . '/../../models/UserModel.php';
require_once __DIR__ . '/../../db.php';

// Access Control
checkAuth();
if (!isAdmin()) {
    header('Location: ../../index.php');
    exit;
}

$model = new ConfiguracaoModel();
$userModel = new UserModel();
$message = '';

// Check if already verified in this session
$isVerified = $_SESSION['api_config_verified'] ?? false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['admin_password'])) {
        // Verification process
        $db = getDB();
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user && password_verify($_POST['admin_password'], $user['password'])) {
            $_SESSION['api_config_verified'] = true;
            $isVerified = true;
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Senha incorreta. Acesso negado.</div>';
        }
    } elseif (isset($_POST['save_keys']) && $isVerified) {
        // Save process
        $data = $model->getConfig(); // Get current to preserve other fields
        $data['asaas_api_key'] = $_POST['asaas_api_key'] ?? '';
        $data['asaas_environment'] = $_POST['asaas_environment'] ?? 'sandbox';
        $data['zapsign_api_token'] = $_POST['zapsign_api_token'] ?? '';
        $data['zapsign_environment'] = $_POST['zapsign_environment'] ?? 'sandbox';

        if ($model->update($data)) {
            $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">Chaves API atualizadas com sucesso!</div>';
        } else {
            $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">Erro ao salvar chaves API.</div>';
        }
    }
}

$config = $model->getConfig();
$pageTitle = 'Configurações de API';
include __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto py-8">
    <div class="mb-8">
        <h2 class="text-3xl font-extrabold text-gray-900">Configurações de Integração</h2>
        <p class="text-gray-500 mt-1">Gerencie as chaves de API para Asaas e ZapSign.</p>
    </div>

    <?php echo $message; ?>

    <?php if (!$isVerified): ?>
        <!-- Password Verification Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-8 max-w-md mx-auto text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center text-red-600 mx-auto mb-6">
                <i class="fas fa-lock text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Área Restrita</h3>
            <p class="text-gray-500 mb-6">Para visualizar e editar as chaves de API, por favor confirme sua senha de administrador.</p>
            
            <form method="POST" class="space-y-4">
                <input type="password" name="admin_password" required placeholder="Sua senha atual" 
                       class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500 outline-none transition-all text-center">
                <button type="submit" class="w-full py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl shadow-lg transition-all">
                    Confirmar Identidade
                </button>
            </form>
        </div>
    <?php else: ?>
        <!-- API Config Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <form method="POST" class="divide-y divide-gray-100">
                <input type="hidden" name="save_keys" value="1">
                
                <!-- Asaas Section -->
                <div class="p-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 mr-4">
                            <i class="fas fa-credit-card text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">Asaas (Pagamentos)</h3>
                            <p class="text-sm text-gray-500">Configuração de boletos e assinaturas.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">API Key</label>
                            <input type="password" name="asaas_api_key" value="<?php echo htmlspecialchars($config['asaas_api_key'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Ambiente</label>
                            <select name="asaas_environment" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                                <option value="sandbox" <?php echo ($config['asaas_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>Sandbox (Testes)</option>
                                <option value="production" <?php echo ($config['asaas_environment'] ?? '') === 'production' ? 'selected' : ''; ?>>Produção (Real)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- ZapSign Section -->
                <div class="p-6 bg-gray-50/30">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 mr-4">
                            <i class="fas fa-file-signature text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800">ZapSign (Assinaturas)</h3>
                            <p class="text-sm text-gray-500">Configuração de assinatura eletrônica.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1">API Token</label>
                            <input type="password" name="zapsign_api_token" value="<?php echo htmlspecialchars($config['zapsign_api_token'] ?? ''); ?>" 
                                   class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition-all font-mono">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Ambiente</label>
                            <select name="zapsign_environment" class="w-full px-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 outline-none transition-all">
                                <option value="sandbox" <?php echo ($config['zapsign_environment'] ?? 'sandbox') === 'sandbox' ? 'selected' : ''; ?>>Sandbox / Teste</option>
                                <option value="production" <?php echo ($config['zapsign_environment'] ?? '') === 'production' ? 'selected' : ''; ?>>Produção</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="p-8 bg-gray-50 flex justify-between items-center">
                    <p class="text-xs text-gray-400 max-w-md">As chaves de API são sensíveis. Nunca compartilhe estas informações com terceiros não autorizados.</p>
                    <button type="submit" class="px-10 py-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-2xl shadow-lg transition-all transform hover:-translate-y-1">
                        <i class="fas fa-save mr-2"></i> Salvar Integrações
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layout/footer.php'; ?>
