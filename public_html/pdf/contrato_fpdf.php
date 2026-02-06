<?php
require_once __DIR__ . '/fpdf_v2.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/ContratoModel.php';

function gerarContratoPDF($contratoId) {
    $clienteModel = new ClienteModel();
    $contratoModel = new ContratoModel();
    
    $contrato = $contratoModel->getById($contratoId);
    if (!$contrato) return false;
    
    $cliente = $clienteModel->getById($contrato['id_contratante']);
    if (!$cliente) return false;

    // Prepare data
    $nome        = mb_convert_encoding(mb_strtoupper($cliente['nome'] ?? ''), 'ISO-8859-1', 'UTF-8');
    $estadoCivil = mb_convert_encoding($cliente['estado_civil'] ?? 'Brasileira', 'ISO-8859-1', 'UTF-8');
    $cpf         = $cliente['cpf_cnpj'] ?? '';
    $rg          = $cliente['rg'] ?? '';
    $dataNasc    = !empty($cliente['dt_nascto']) ? date('d/m/Y', strtotime($cliente['dt_nascto'])) : '';
    $endereco    = mb_convert_encoding($cliente['endereco'] ?? '', 'ISO-8859-1', 'UTF-8');
    $numero      = $cliente['numero'] ?? '';
    $bairro      = mb_convert_encoding($cliente['bairro'] ?? '', 'ISO-8859-1', 'UTF-8');
    $municipio   = mb_convert_encoding($cliente['municipio'] ?? '', 'ISO-8859-1', 'UTF-8');
    $uf          = $cliente['uf'] ?? '';
    $telefone    = $cliente['celular'] ?? $cliente['telefone'] ?? '';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(25, 25, 25);
    $pdf->SetFont('Arial', '', 11);

    // Texto de abertura
    $texto = "Por este instrumento de contrato particular, eu $nome, brasileiro(a), $estadoCivil, do lar, portadora do CPF: $cpf, RG: $rg, nascida em $dataNasc, residente na $endereco, n$numero, bairro $bairro, na cidade de $municipio-$uf, telefone $telefone, denominado CONTRATANTE e, de outro RS Maternidade CNPJ-33.501.519/0001-02, têm justo e pactuado o que segue:";
    
    $pdf->MultiCell(0, 7, mb_convert_encoding($texto, 'UTF-8', 'ISO-8859-1'), 0, 'J');
    $pdf->Ln(5);
    $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
    $pdf->Ln(5);

    // Cláusulas (Simplified for brevity but maintaining essential logic)
    $clausulas = [
        "CLÁUSULA 1º: O objetivo do presente contrato é a prestação de serviços profissionais da CONTRATADA, para fim especifico do encaminhamento de processo administrativo, para o fim de oportunizar o recebimento de valores aproximadamente equivalentes à média aritmética simples das 12 (doze) ultimas remunerações oficialmente obtidas em virtude do ultimo contrato de emprego analisado multiplicado por 4 (quatro), ressalvada eventual mudança da legislação até o recebimento das quantias devidas, sendo-lhe ciente do depósito;",
        "CLÁUSULA 2º: A CONTRATANTE, por sua vez, se obriga a pagar, à CONTRATADA, o valor mínimo de R$ 1.250,00 (HUM MIL DUZENTOS E CINQUENTA REAIS), ou 30% DO VALOR BRUTO RECEBIDO, CASO O BENEFÍCIO ULTRAPASSE O VALOR EQUIVALENTE À 04 (QUATRO) SALÁRIOS MÍNIMOS. Sem qualquer outra espécie de desconto, sendo o benefício concedido.",
        "CLÁUSULA 3º: A CONTRATANTE outorga poderes à CONTRATADA, para atuação e acompanhamento do processo procedimentos necessários, visando o recebimento da indenização objeto deste contrato.",
        "CLÁUSULA 4º: A CONTRATANTE, neste ato, é informada pela CONTRATADA, de que pode encaminhar o pedido de indenização administrativamente e sem procurador, no entanto, pelo presente instrumento, opta livremente pela contratação dos serviços ora contratados.",
        "CLÁUSULA 5º: Ambas as partes declaram que o valor obtido em virtude desta contratação, no primeiro pagamento, será recebido exclusivamente em conjunto (ambos comparecendo no mesmo dia e horário na agência bancária respectiva para recebimento mencionado), momento no qual os serviços da CONTRATADA, serão pagos em uma única parcela, ou em duas parcelas onde o valor de cada parcela será estipulado pelo escritório em caso de o valor do primeiro pagamento seja inferior a R$ 1.500,00 (HUM MIL E QUINHENTOS REIAS).",
        "CLÁUSULA 6º: A CONTRATANTE devera informar se já recebeu o SEGURO MATERNIDADE, caso já tenha recebido e não avisar será cobrado uma taxa de R$ 250,00 (DUZENTOS E CINQUENTA REAIS) pelo serviço prestado.",
        "CLÁUSULA 7º: No caso de rescisão contratual e/ou descumprimento total ou parcial ou retardo ao cumprimento das clausulas acima por parte da CONTRATANTE, será cobrada clausula penal no valor de 2 (dois) salários mínimos mais honorários advocatícios.",
        "CLÁUSULA 8º: Caso a CONTRATANTE não compareça no dia e local combinado a ser feito o pagamento, será cobrado uma multa de R$ 200,00 (DUZENTOS REAIS), para fins de ressarcir gastos com deslocamento e pessoal.",
        "CLÁUSULA 9º: Fica eleito o Foro da cidade de Novo Hamburgo/ RS para dirimir qualquer questões judiciais."
    ];

    foreach ($clausulas as $clausula) {
        $pdf->MultiCell(0, 7, mb_convert_encoding($clausula, 'UTF-8', 'ISO-8859-1'), 0, 'J');
        $pdf->Ln(3);
        $pdf->Line(25, $pdf->GetY(), 185, $pdf->GetY());
        $pdf->Ln(3);
    }

    $pdf->Ln(20);
    $pdf->Cell(80, 10, '___________________________', 0, 0, 'C');
    $pdf->Cell(20, 10, '', 0, 0, 'C');
    $pdf->Cell(80, 10, '___________________________', 0, 1, 'C');
    
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(80, 5, 'CONTRATADA', 0, 0, 'C');
    $pdf->Cell(20, 5, '', 0, 0, 'C');
    $pdf->Cell(80, 5, 'CONTRATANTE', 0, 1, 'C');
    
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(80, 5, 'RS MATERNIDADE', 0, 0, 'C');
    $pdf->Cell(20, 5, '', 0, 0, 'C');
    $pdf->Cell(80, 5, $nome, 0, 1, 'C');

    return $pdf->Output('S', 'Contrato.pdf'); // Return as string
}
