<?php
require_once __DIR__ . '/../../pdf/fpdf_v2.php';
require_once __DIR__ . '/../../models/FinanceiroModel.php';
require_once __DIR__ . '/../../models/ConfiguracaoModel.php';


// Prevent warnings from breaking PDF headers
ob_start();


$model = new FinanceiroModel();
$configModel = new ConfiguracaoModel();
$empresaConfig = $configModel->getConfig();

$empresaNome = !empty($empresaConfig['nome_fantasia']) ? $empresaConfig['nome_fantasia'] : (!empty($empresaConfig['razao_social']) ? $empresaConfig['razao_social'] : 'SMU');
$logoPath = !empty($empresaConfig['logotipo']) ? __DIR__ . '/../../' . $empresaConfig['logotipo'] : null;

$filters = [
    'data_inicio' => $_GET['data_inicio'] ?? null,
    'data_fim' => $_GET['data_fim'] ?? null,
    'venc_inicio' => $_GET['venc_inicio'] ?? null,
    'venc_fim' => $_GET['venc_fim'] ?? null,
    'tipo' => $_GET['tipo'] ?? null,
    'situacao' => $_GET['situacao'] ?? null
];

$items = $model->getAll($filters);

class PDF extends FPDF {
    public $empresaNome;
    public $logoPath;

    function Header() {
        $this->SetY(10);
        // Company Name and Emission Date
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(31, 41, 55);
        $this->Cell(130, 10, mb_convert_encoding($this->empresaNome, 'ISO-8859-1', 'UTF-8'), 0, 0, 'L');
        
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 10, mb_convert_encoding('Emissão: ' . date('d/m/Y H:i:s'), 'ISO-8859-1', 'UTF-8'), 0, 1, 'R');
        
        $this->SetFont('Arial', 'B', 18);
        $this->SetTextColor(17, 24, 39);
        $this->Cell(0, 12, mb_convert_encoding('RELATÓRIO FINANCEIRO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        
        // Horizontal Line
        $this->SetDrawColor(200, 200, 200);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        $this->Ln(5);
        
        // Header Table
        $this->SetFillColor(230, 230, 230);
        $this->SetFont('Arial', 'B', 9);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(20, 8, 'Data', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Cliente/Fornecedor', 1, 0, 'L', true);
        $this->Cell(25, 8, 'Tipo', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Situacao', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Valor (R$)', 1, 0, 'R', true);
        $this->Cell(30, 8, 'Saldo (R$)', 1, 1, 'R', true);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->empresaNome = $empresaNome;
$pdf->logoPath = $logoPath;
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial', '', 8);

$totalValor = 0;
$totalSaldo = 0;

foreach ($items as $item) {
    $pdf->Cell(20, 7, date('d/m/Y', strtotime($item['data'])), 1, 0, 'C');
    $pdf->Cell(60, 7, mb_convert_encoding(substr($item['cliente_nome'] ?? '-', 0, 35), 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
    $pdf->Cell(25, 7, mb_convert_encoding($item['tipo'], 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell(25, 7, mb_convert_encoding($item['situacao'] ?? 'Aberto', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C');
    $pdf->Cell(30, 7, number_format($item['valor'], 2, ',', '.'), 1, 0, 'R');
    $pdf->Cell(30, 7, number_format($item['saldo'] ?? $item['valor'], 2, ',', '.'), 1, 1, 'R');

    
    $totalValor += $item['valor'];
    $totalSaldo += $item['saldo'] ?? $item['valor'];
}

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(245, 245, 245);
$pdf->Cell(130, 10, 'TOTAL', 1, 0, 'R', true);
$pdf->Cell(30, 10, 'R$ ' . number_format($totalValor, 2, ',', '.'), 1, 0, 'R', true);
$pdf->Cell(30, 10, 'R$ ' . number_format($totalSaldo, 2, ',', '.'), 1, 1, 'R', true);

// Clean buffer and output PDF
if (ob_get_length()) ob_end_clean();
$pdf->Output('I', 'Relatorio_Financeiro_' . date('Ymd') . '.pdf');

