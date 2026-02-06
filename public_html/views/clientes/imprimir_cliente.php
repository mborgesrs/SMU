<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/ContratoModel.php';

// Check if ID (client) or contrato_id (contract) is provided
$clienteId = $_GET['id'] ?? null;
$contratoId = $_GET['contrato_id'] ?? null;

if (!$clienteId && !$contratoId) {
    die("ID do cliente ou contrato não informado.");
}

$clienteModel = new ClienteModel();
$contratoModel = new ContratoModel();
$cliente = null;

if ($contratoId) {
    $contrato = $contratoModel->getById($contratoId);
    if ($contrato) {
        $cliente = $clienteModel->getById($contrato['id_contratante']);
    }
} elseif ($clienteId) {
    $cliente = $clienteModel->getById($clienteId);
}

if (!$cliente) {
    die("Cliente não encontrado.");
}

// Prepare data for the template
$nome        = mb_strtoupper($cliente['nome'] ?? '');
$estadoCivil = $cliente['estado_civil'] ?? 'Brasileira';
$cpf         = $cliente['cpf_cnpj'] ?? '';
$rg          = $cliente['rg'] ?? '';
$dataNasc    = !empty($cliente['dt_nascto']) ? date('d/m/Y', strtotime($cliente['dt_nascto'])) : '';
$endereco    = $cliente['endereco'] ?? '';
$numero      = $cliente['numero'] ?? '';
$bairro      = $cliente['bairro'] ?? '';
$municipio   = $cliente['municipio'] ?? '';
$uf          = $cliente['uf'] ?? '';
$telefone    = $cliente['celular'] ?? $cliente['telefone'] ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Impressão de Contrato - <?php echo htmlspecialchars($nome); ?></title>
    <style>
        @page {
            size: A4;
            margin: 0; /* Removes browser headers/footers */
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt; /* Slightly smaller for better fit */
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .print-container {
            padding: 2.5cm; /* Re-adds document margin inside the body */
            min-height: 100vh;
        }
        p {
            text-align: justify;
            margin-bottom: 16px;
        }
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 16px 0;
        }
        .assinaturas {
            margin-top: 80px;
            display: flex;
            justify-content: space-between;
            text-align: center;
        }
        .assinatura {
            width: 45%;
        }
        .no-print {
            background: #fff3cd;
            padding: 10px;
            border: 1px solid #ffeeba;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .btn-print {
            background: #198754;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            margin-top: 10px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="print-container">
        <div class="no-print">
            <p><strong>Dica:</strong> Para Salvar como PDF, selecione "Salvar como PDF" no destino da impressora.</p>
            <button onclick="window.print()" class="btn-print">Abrir Diálogo de Impressão</button>
        </div>

        <p>
            Por este instrumento de contrato particular, eu <b><?php echo $nome; ?></b>, brasileiro(a),
            <b><?php echo $estadoCivil; ?></b>, do lar, portadora do CPF: <b><?php echo $cpf; ?></b>, RG: <b><?php echo $rg; ?></b>,
            nascida em <b><?php echo $dataNasc; ?></b>, residente na <b><?php echo $endereco; ?></b>, nº <b><?php echo $numero; ?></b>,
            bairro <b><?php echo $bairro; ?></b>, na cidade de <b><?php echo $municipio; ?>-<?php echo $uf; ?></b>,
            telefone <b><?php echo $telefone; ?></b>, denominado CONTRATANTE e, de outro
            RS Maternidade CNPJ-33.501.519/0001-02, têm justo e pactuado o que segue:
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 1º:</b> O objetivo do presente contrato é a prestação de serviços profissionais da CONTRATADA, para fim especifico do encaminhamento de processo administrativo, para o fim de oportunizar o recebimento de valores aproximadamente equivalentes à média aritmética simples das 12 (doze) ultimas remunerações oficialmente obtidas em virtude do ultimo contrato de emprego analisado multiplicado por 4 (quatro), ressalvada eventual mudança da legislação até o recebimento das quantias devidas, sendo-lhe ciente do depósito;
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 2º:</b> A CONTRATANTE, por sua vez, se obriga a pagar, à CONTRATADA, o valor mínimo de R$ 1.250,00 (HUM MIL DUZENTOS E CINQUENTA REAIS), ou 30% DO VALOR BRUTO RECEBIDO, CASO O BENEFÍCIO  ULTRAPASSE O VALOR EQUIVALENTE À 04 (QUATRO) SALÁRIOS MÍNIMOS. Sem qualquer outra espécie de desconto, sendo o benefício concedido. Parágrafo único: Da mora, em ocorrendo o atraso do pagamento na data estipulada, haverá acréscimo de juros no valor de 1% a.m. e multa de 10% sobre o valor total devido;
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 3º:</b> A CONTRATANTE outorga poderes à CONTRATADA, para atuação e acompanhamento do processo procedimentos necessários, visando o recebimento da indenização objeto deste contrato.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 4º:</b> A CONTRATANTE, neste ato, é informada pela CONTRATADA, de que pode encaminhar o pedido de indenização administrativamente e sem procurador, no entanto, pelo presente instrumento, opta livremente pela contratação dos serviços ora contratados.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 5º:</b> Ambas as partes declaram que o valor obtido em virtude desta contratação, no primeiro pagamento, será recebido exclusivamente em conjunto (ambos comparecendo no mesmo dia e horário na agência bancária respectiva para recebimento mencionado), momento no qual os serviços da CONTRATADA, serão pagos em uma única parcela, ou em duas parcelas onde o valor de cada parcela será estipulado pelo escritório em caso de o valor do primeiro pagamento seja inferior a R$ 1.500,00 (HUM MIL E QUINHENTOS REIAS). Caso o pagamento do escritório venha ser parcelado o cartão fica em posse do CONTRATADO até a quitação da dívida; O descumprimento dessa cláusula poderá implicar em processo judicial;
        </p>

        <p>
            <b>Parágrafo único:</b> Através deste instrumento, fica autorizado à CONTRATADA, ao seu critério, fazer a cobrança do valor correspondente aos honorários pactuados, através de terceiros, inclusive por procuração autorizando movimentação bancaria ou débito em conta corrente e/ou poupança de minha titularidade (CONTRATANTE), o qual somente poderá ser efetivado após o crédito, nessa mesma conta, do valor do benefício do qual a parte CONTRATANTE é beneficiária.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 6º:</b> A CONTRATANTE devera informar se já recebeu o SEGURO MATERNIDADE, caso já tenha recebido e não avisar será cobrado uma taxa de R$ 250,00 (DUZENTOS E CINQUENTA REAIS) pelo serviço prestado.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 7º:</b> No caso de rescisão contratual e/ou descumprimento total ou parcial ou retardo ao cumprimento das clausulas acima por parte da CONTRATANTE, será cobrada clausula  penal no valor de 2 (dois) salários mínimos mais honorários advocatícios.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 8º:</b> Caso a  CONTRATANTE  não  compareça no dia  e  local   combinado a  ser  feito   o pagamento, será   cobrado   uma  multa  de R$ 200,00 (DUZENTOS REAIS) , para   fins de   ressarcir gastos com deslocamento e pessoal.
        </p>

        <hr>

        <p>
            <b>CLÁUSULA 9º:</b> Fica  eleito  o  Foro  da  cidade  de  Novo  Hamburgo/ RS  para  dirimir qualquer questões  judiciais.
        </p>

        <div class="assinaturas">
            <div class="assinatura">
                ___________________________<br>
                <b>CONTRATADA</b><br>
                <span style="font-size: 0.8em;">RS MATERNIDADE</span>
            </div>
            <div class="assinatura">
                ___________________________<br>
                <b>CONTRATANTE</b><br>
                <span style="font-size: 0.8em;"><?php echo $nome; ?></span>
            </div>
        </div>
    </div>

</body>
</html>
