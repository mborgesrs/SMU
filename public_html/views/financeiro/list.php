<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/FinanceiroModel.php';

checkAuth();
$model = new FinanceiroModel();

if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = 'Financeiro';
require_once __DIR__ . '/../layout/header.php';


$items = $model->getAll();
?>

<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Financeiro</h2>
        <div class="flex gap-3">
            <a href="../dashboard.php" class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition shadow-lg">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </a>
            <a href="relatorios.php" class="bg-gray-100 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-200 transition">
                <i class="fas fa-file-pdf mr-2"></i>Relatórios
            </a>
            <a href="form.php" class="bg-slate-800 text-white px-6 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg">
                <i class="fas fa-plus mr-2"></i>Novo Lançamento
            </a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            Operação realizada com sucesso!
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Lanç.</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimento</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente/Fornecedor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NF/Contrato</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Situação</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Saldo</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($items as $item): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($item['data'])); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-900"><?php echo $item['dt_vencimento'] ? date('d/m/Y', strtotime($item['dt_vencimento'])) : '-'; ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($item['cliente_nome'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($item['nf_contrato'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $type_colors = [
                                'Receber' => 'bg-blue-100 text-blue-800',
                                'Pagar' => 'bg-amber-100 text-amber-800',
                                'Entrada' => 'bg-green-100 text-green-800',
                                'Saida' => 'bg-red-100 text-red-800',
                                'cRecebido' => 'bg-cyan-100 text-cyan-800',
                                'dPago' => 'bg-rose-100 text-rose-800'
                            ];
                            $color = $type_colors[$item['tipo']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $color; ?>">
                                <?php echo htmlspecialchars($item['tipo']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $situacao = $item['situacao'] ?? 'Aberto';
                            $liquidated_statuses = ['Liquidado', 'cRecebido', 'dPago'];
                            $sit_color = in_array($situacao, $liquidated_statuses) ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2 py-1 text-xs rounded-full <?php echo $sit_color; ?>">
                                <?php echo htmlspecialchars($situacao); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">R$ <?php echo number_format($item['valor'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">R$ <?php echo number_format($item['saldo'] ?? $item['valor'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="form.php?id=<?php echo $item['id']; ?>" class="text-slate-600 hover:text-slate-900 mr-3">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="?delete=<?php echo $item['id']; ?>" onclick="return confirmDelete(event)" class="text-red-600 hover:text-red-900">
                                <i class="fas fa-trash"></i>
                            </a>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
