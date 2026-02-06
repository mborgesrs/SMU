<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ContratoModel.php';

checkAuth();
$model = new ContratoModel();

// Handle status toggle BEFORE any HTML output
if (isset($_GET['status']) && isset($_GET['id'])) {
    $model->updateStatus($_GET['id'], $_GET['status']);
    header('Location: list.php?success=updated');
    exit;
}

// Handle delete BEFORE any HTML output
if (isset($_GET['delete'])) {
    $model->delete($_GET['delete']);
    header('Location: list.php?success=deleted');
    exit;
}

$pageTitle = 'Contratos';
require_once __DIR__ . '/../layout/header.php';

$search = $_GET['search'] ?? '';
$contratos = $model->getAll($search);
?>

<div class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Contratos</h2>
            <p class="text-gray-500 text-sm">Gerencie e pesquise os contratos emitidos.</p>
        </div>
        
        <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4 w-full md:w-auto">
            <form action="" method="GET" class="relative w-full md:w-80">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Pesquisar contratante..." 
                       class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-slate-500 outline-none transition-all shadow-sm">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <?php if (!empty($search)): ?>
                    <a href="list.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500" title="Limpar busca">
                        <i class="fas fa-times-circle"></i>
                    </a>
                <?php endif; ?>
            </form>

            <a href="/views/contratos/form.php" class="w-full md:w-auto bg-slate-800 text-white px-6 py-3 rounded-xl hover:bg-slate-900 transition shadow-lg flex items-center justify-center">
                <i class="fas fa-plus mr-2"></i>Novo Contrato
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contratante</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Objeto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Ações</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($contratos as $contrato): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">#<?php echo $contrato['id']; ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($contrato['contratante_nome'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($contrato['objeto_descricao'] ?? '-'); ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?php echo htmlspecialchars($contrato['tipo']); ?></td>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">R$ <?php echo number_format($contrato['valor_total'], 2, ',', '.'); ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $statusMap = [
                                'pendente' => ['label' => 'Pendente/Aguardando Assinatura', 'color' => 'bg-yellow-100 text-yellow-800', 'next' => 'ativo'],
                                'ativo' => ['label' => 'Ativo/Vigente', 'color' => 'bg-green-100 text-green-800', 'next' => 'encerrado'],
                                'encerrado' => ['label' => 'Encerrado', 'color' => 'bg-red-100 text-red-800', 'next' => 'pendente']
                            ];
                            $statusData = $statusMap[$contrato['status']] ?? ['label' => ucfirst($contrato['status']), 'color' => 'bg-gray-100 text-gray-800', 'next' => 'pendente'];
                            ?>
                            <a href="?id=<?php echo $contrato['id']; ?>&status=<?php echo $statusData['next']; ?>" 
                               title="Clique para alternar para o próximo status"
                               class="px-2 py-1 text-xs font-semibold rounded-full cursor-pointer hover:opacity-80 transition-all <?php echo $statusData['color']; ?>">
                                <?php echo $statusData['label']; ?>
                            </a>
                        </td>
                        <td class="px-6 py-4 text-right text-sm space-x-2">
                            <a href="../clientes/imprimir_cliente.php?contrato_id=<?php echo $contrato['id']; ?>" target="_blank"
                               class="text-purple-600 hover:text-purple-900" title="Imprimir Contrato (Modelo Novo)">
                                <i class="fas fa-print"></i>
                            </a>

                            <a href="javascript:void(0)" onclick="enviarWhatsApp(<?php echo $contrato['id']; ?>, '<?php echo htmlspecialchars($contrato['contratante_nome']); ?>')"
                               class="text-green-600 hover:text-green-900" title="Enviar WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                            <a href="form.php?id=<?php echo $contrato['id']; ?>" class="text-slate-600 hover:text-slate-900" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="gerar_financeiro.php?id=<?php echo $contrato['id']; ?>" 
                               onclick="return confirmAction(event, 'Deseja gerar o financeiro do contrato?', 'warning', 'Sim, gerar!', '#10b981')"
                               class="text-blue-600 hover:text-blue-900" title="Gerar Financeiro">
                                <i class="fas fa-dollar-sign"></i>
                            </a>
                            <a href="?delete=<?php echo $contrato['id']; ?>" onclick="return confirmDelete(event, 'Deseja excluir este contrato?')" class="text-red-600 hover:text-red-900" title="Excluir">
                                <i class="fas fa-trash"></i>
                            </a>


                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function enviarWhatsApp(contratoId, clienteNome) {
    const mensagem = `Olá ${clienteNome}! Foi gerado um novo contrato (#${contratoId}) para os serviços contratados. Em breve entraremos em contato com mais detalhes.`;
    const url = `https://wa.me/?text=${encodeURIComponent(mensagem)}`;
    window.open(url, '_blank');
}
</script>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>
