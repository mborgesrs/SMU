<?php
$pageTitle = 'Relatórios Financeiros';
require_once __DIR__ . '/../layout/header.php';
?>

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Relatórios Financeiros</h2>
        <a href="list.php" class="text-slate-600 hover:text-slate-900 transition">
            <i class="fas fa-arrow-left mr-2"></i>Voltar
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="gerar_pdf.php" method="GET" target="_blank" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Lançamento Inicial</label>
                    <input type="date" name="data_inicio" value="<?php echo date('Y-m-01'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Lançamento Final</label>
                    <input type="date" name="data_fim" value="<?php echo date('Y-m-t'); ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <!-- Filtros por Vencimento -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vencimento Inicial</label>
                    <input type="date" name="venc_inicio"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Vencimento Final</label>
                    <input type="date" name="venc_fim"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de Lançamento</label>
                    <select name="tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="">Todos os Tipos</option>
                        <option value="Receber">A Receber (Aberto)</option>
                        <option value="Pagar">A Pagar (Aberto)</option>
                        <option value="Entrada">Entrada (Dinheiro)</option>
                        <option value="Saida">Saída (Dinheiro)</option>
                        <option value="cRecebido">Recebimentos (Liquidado)</option>
                        <option value="dPago">Pagamentos (Liquidado)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Situação</label>
                    <select name="situacao" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500">
                        <option value="">Todas</option>
                        <option value="Aberto">Em Aberto</option>
                        <option value="Liquidado">Liquidado (Tudo)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t">
                <button type="submit" class="w-full md:w-auto bg-slate-800 text-white px-8 py-3 rounded-lg hover:bg-slate-900 transition shadow-lg flex items-center justify-center">
                    <i class="fas fa-file-pdf mr-3 text-lg"></i>Gerar Relatório em PDF
                </button>
            </div>
        </form>
    </div>

    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="gerar_pdf.php?tipo=Receber&situacao=Aberto" target="_blank" class="p-4 bg-blue-50 border border-blue-100 rounded-lg hover:bg-blue-100 transition text-center">
            <i class="fas fa-file-invoice-dollar text-blue-600 text-2xl mb-2"></i>
            <span class="block text-sm font-semibold text-blue-800">Contas a Receber</span>
        </a>
        <a href="gerar_pdf.php?tipo=Pagar&situacao=Aberto" target="_blank" class="p-4 bg-amber-50 border border-amber-100 rounded-lg hover:bg-amber-100 transition text-center">
            <i class="fas fa-file-invoice text-amber-600 text-2xl mb-2"></i>
            <span class="block text-sm font-semibold text-amber-800">Contas a Pagar</span>
        </a>
        <a href="gerar_pdf.php?situacao=Liquidado" target="_blank" class="p-4 bg-green-50 border border-green-100 rounded-lg hover:bg-green-100 transition text-center">
            <i class="fas fa-check-double text-green-600 text-2xl mb-2"></i>
            <span class="block text-sm font-semibold text-green-800">Títulos Liquidados</span>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
