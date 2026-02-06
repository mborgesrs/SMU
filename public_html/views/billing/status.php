<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../models/AsaasService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();

    if (!$company) {
        header('Location: ../../index.php');
        exit;
    }

    $isBlocked = ($company['status'] === 'inactive' || $company['billing_status'] === 'blocked' || $company['billing_status'] === 'overdue');
    
    $invoices = [];
    if (!empty($company['asaas_customer_id'])) {
        try {
            $asaas = new AsaasService();
            $paymentsData = $asaas->getCustomerPayments($company['asaas_customer_id']);
            $invoices = $paymentsData['data'] ?? [];
        } catch (Exception $e) {
            // Silence API errors for clean UI, or log them
        }
    }

} catch (Exception $e) {
    die("Erro ao processar cobrança: " . $e->getMessage());
}

$pageTitle = $isBlocked ? 'Acesso Suspenso' : 'Faturamento e Assinatura';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8">
    
    <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <a href="../dashboard.php" class="text-slate-500 hover:text-slate-800 transition flex items-center mb-2 text-sm font-bold">
                    <i class="fas fa-arrow-left mr-2"></i> Voltar ao Sistema
                </a>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight"><?php echo $pageTitle; ?></h1>
            </div>
            <div class="flex items-center space-x-3 bg-white p-2 rounded-2xl shadow-sm border border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-white">
                    <i class="fas fa-building text-sm"></i>
                </div>
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase">Empresa</p>
                    <p class="text-sm font-bold text-slate-800"><?php echo htmlspecialchars($company['nome_fantasia'] ?: $company['razao_social']); ?></p>
                </div>
            </div>
        </div>

        <?php if ($isBlocked): ?>
            <!-- BLOCKED VIEW -->
            <div class="bg-white rounded-[2.5rem] shadow-2xl overflow-hidden border border-red-50 mb-8">
                <div class="bg-gradient-to-br from-red-600 to-red-700 p-10 text-white text-center relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="w-20 h-20 bg-white/20 rounded-3xl flex items-center justify-center mx-auto mb-6 backdrop-blur-md border border-white/30 rotate-12">
                        <i class="fas fa-lock text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-extrabold tracking-tight">Acesso Temporariamente Suspenso</h2>
                    <p class="mt-2 text-red-100 font-medium opacity-90">Detectamos uma pendência financeira que precisa ser regularizada.</p>
                </div>
                <div class="p-10 text-center">
                    <p class="text-slate-600 text-lg mb-8">Sua conta foi bloqueada automaticamente. Clique no botão abaixo para gerar sua fatura e liberar o acesso imediatamente após o pagamento.</p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="generate_payment.php" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-bold hover:bg-black transition-all transform hover:scale-[1.02] shadow-xl flex items-center justify-center">
                            <i class="fas fa-bolt mr-3 text-yellow-400"></i>Regularizar Agora
                        </a>
                        <a href="https://wa.me/5500000000000" target="_blank" class="border-2 border-slate-200 text-slate-700 px-10 py-5 rounded-2xl font-bold hover:bg-gray-50 transition-all flex items-center justify-center">
                            <i class="fab fa-whatsapp mr-3 text-green-500"></i>Suporte Financeiro
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- ACTIVE VIEW STATS -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-widest">Plano Atual</p>
                    <div class="flex items-center justify-between">
                        <span class="text-2xl font-black text-slate-800"><?php echo strtoupper($company['plan']); ?></span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-black rounded-full">ATIVO</span>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-widest">Valor Mensal</p>
                    <span class="text-2xl font-black text-slate-800">R$ <?php echo number_format($company['plan_price'], 2, ',', '.'); ?></span>
                    <span class="text-xs text-slate-400 block mt-1">Próximo vencimento: <?php echo $company['next_due_date'] ? date('d/m/Y', strtotime($company['next_due_date'])) : 'Automático'; ?></span>
                </div>
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
                    <p class="text-xs font-bold text-slate-400 uppercase mb-4 tracking-widest">Implantação</p>
                    <div class="flex items-center">
                        <?php if ($company['setup_paid']): ?>
                            <i class="fas fa-check-circle text-green-500 mr-2 text-xl"></i>
                            <span class="font-bold text-slate-700 font-bold uppercase">Paga</span>
                        <?php else: ?>
                            <i class="fas fa-clock text-amber-500 mr-2 text-xl"></i>
                            <span class="font-bold text-slate-700 font-bold uppercase">Pendente</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Invoice List -->
        <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-black text-slate-800 flex items-center">
                    <i class="fas fa-history mr-3 text-slate-400"></i> Histórico de Faturas
                </h3>
                <?php if (!$isBlocked): ?>
                <a href="generate_payment.php" class="text-sm font-bold text-indigo-600 hover:text-indigo-800 transition">
                    <i class="fas fa-plus-circle mr-1"></i> Nova Cobrança
                </a>
                <?php endif; ?>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50 text-slate-400 text-[10px] font-black uppercase tracking-widest">
                            <th class="px-8 py-4">Vencimento</th>
                            <th class="px-8 py-4">Descrição</th>
                            <th class="px-8 py-4">Valor</th>
                            <th class="px-8 py-4">Status</th>
                            <th class="px-8 py-4 text-right">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if (empty($invoices)): ?>
                            <tr>
                                <td colspan="5" class="px-8 py-12 text-center text-slate-400 font-medium font-italic">
                                    Nenhuma fatura encontrada.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-8 py-5 text-sm font-bold text-slate-700">
                                        <?php echo date('d/m/Y', strtotime($invoice['dueDate'])); ?>
                                    </td>
                                    <td class="px-8 py-5 text-sm text-slate-500">
                                        <?php echo htmlspecialchars($invoice['description'] ?: 'Mensalidade SMU'); ?>
                                    </td>
                                    <td class="px-8 py-5 text-sm font-black text-slate-800">
                                        R$ <?php echo number_format($invoice['value'], 2, ',', '.'); ?>
                                    </td>
                                    <td class="px-8 py-5">
                                        <?php 
                                        $statusClass = 'bg-slate-100 text-slate-500';
                                        $statusLabel = $invoice['status'];
                                        if ($invoice['status'] === 'RECEIVED' || $invoice['status'] === 'CONFIRMED') {
                                            $statusClass = 'bg-green-100 text-green-700';
                                            $statusLabel = 'PAGO';
                                        } elseif ($invoice['status'] === 'OVERDUE') {
                                            $statusClass = 'bg-red-100 text-red-700';
                                            $statusLabel = 'VENCIDO';
                                        } elseif ($invoice['status'] === 'PENDING') {
                                            $statusClass = 'bg-amber-100 text-amber-700';
                                            $statusLabel = 'PENDENTE';
                                        }
                                        ?>
                                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase <?php echo $statusClass; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td class="px-8 py-5 text-right">
                                        <?php if ($invoice['status'] === 'PENDING' || $invoice['status'] === 'OVERDUE'): ?>
                                            <a href="<?php echo $invoice['invoiceUrl']; ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700 transition shadow-sm">
                                                PAGAR <i class="fas fa-external-link-alt ml-2"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $invoice['bankSlipUrl'] ?? $invoice['invoiceUrl']; ?>" target="_blank" class="text-slate-400 hover:text-slate-600 transition" title="Recibo">
                                                <i class="fas fa-file-alt"></i>
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

        <!-- Footer Info -->
        <div class="mt-12 flex flex-col items-center justify-center space-y-4">
            <div class="flex items-center space-x-6 text-slate-400 text-sm font-medium">
                <span>Termos de Uso</span>
                <span class="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
                <span>Privacidade</span>
                <span class="w-1.5 h-1.5 bg-slate-200 rounded-full"></span>
                <span>SMU Cloud System v2.0</span>
            </div>
            <p class="text-[10px] font-bold text-slate-300 uppercase tracking-[0.2em]">Tecnologia Segura Asaas Digital</p>
        </div>
    </div>
</body>
</html>
