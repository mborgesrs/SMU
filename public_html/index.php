<?php
require_once 'config.php';
require_once 'db.php';
require_once 'models/CompanyModel.php';

// Fetch companies for dropdown
try {
    $companyModel = new CompanyModel();
    $companies = $companyModel->getAll();
} catch (Exception $e) {
    $companies = [];
    $error = 'Erro ao carregar empresas: ' . $e->getMessage();
}

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: views/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $company_id = $_POST['company_id'] ?? '';

    if (!empty($username) && !empty($password) && !empty($company_id)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, username, password, company_id, role FROM users WHERE username = ? AND company_id = ?");
            $stmt->execute([$username, $company_id]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['role'] = $user['role'];
                header('Location: views/dashboard.php');
                exit;
            } else {
                $error = 'Usuário ou senha inválidos';
            }
        } catch (PDOException $e) {
            $error = 'Erro ao processar login';
        }
    } else {
        $error = 'Preencha todos os campos';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        html { font-size: 16px; }
        @media (min-width: 1024px) {
            html { font-size: 14.5px; }
        }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-200 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-800 mb-2"><?php echo APP_NAME; ?></h1>
            <p class="text-gray-600">Sistema de Controle de Maternidade</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
                <select id="company_id" name="company_id" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent transition bg-white">
                    <option value="">Selecione uma empresa</option>
                    <?php foreach ($companies as $company): ?>
                        <option value="<?php echo $company['id']; ?>" <?php echo (isset($_POST['company_id']) && $_POST['company_id'] == $company['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($company['nome_fantasia'] ?: $company['razao_social']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Usuário</label>
                <input type="text" id="username" name="username" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 focus:border-transparent transition">
            </div>

            <button type="submit"
                class="w-full bg-slate-800 text-white py-3 rounded-lg font-semibold hover:bg-slate-900 transition duration-200 shadow-lg hover:shadow-xl">
                Entrar
            </button>
        </form>

    </div>
</body>
</html>
