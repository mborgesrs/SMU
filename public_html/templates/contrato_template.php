<?php
// ===============================
// DADOS (normalmente vêm do banco)
// ===============================
$nome        = "Maria da Silva";
$estadoCivil = "Casada";
$cpf         = "000.000.000-00";
$rg          = "0000000";
$dataNasc    = "01/01/1990";
$endereco    = "Rua Exemplo";
$numero      = "123";
$bairro      = "Centro";
$municipio   = "Novo Hamburgo";
$uf          = "RS";
$telefone    = "(51) 99999-9999";

// ===============================
// HTML DO CONTRATO
// ===============================
$html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Contrato</title>

<style>
@page {
    size: A4;
    margin: 2.5cm;
}
body {
    font-family: Arial, Helvetica, sans-serif;
    font-size: 12pt;
    line-height: 1.6;
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
    width: 40%;
}
</style>
</head>

<body>

<p>
Por este instrumento de contrato particular, eu <b>$nome</b>, brasileiro(a),
<b>$estadoCivil</b>, do lar, portadora do CPF: <b>$cpf</b>, RG: <b>$rg</b>,
nascida em <b>$dataNasc</b>, residente na <b>$endereco</b>, nº <b>$numero</b>,
bairro <b>$bairro</b>, na cidade de <b>$municipio-$uf</b>,
telefone <b>$telefone</b>, denominado CONTRATANTE e, de outro
RS Maternidade CNPJ-33.501.519/0001-02, têm justo e pactuado o que segue:
</p>

<hr>

<p>
<b>CLÁUSULA 1º:</b> O objetivo do presente contrato é a prestação de serviços profissionais
para encaminhamento de processo administrativo, visando oportunizar o recebimento
de valores devidos conforme legislação vigente.
</p>

<hr>

<p>
<b>CLÁUSULA 2º:</b> A CONTRATANTE se obriga a pagar à CONTRATADA o valor mínimo de
R$ 1.250,00 (hum mil duzentos e cinquenta reais), ou 30% do valor bruto recebido,
caso o benefício ultrapasse quatro salários mínimos.
</p>

<hr>

<p>
<b>CLÁUSULA 3º:</b> A CONTRATANTE outorga poderes à CONTRATADA para acompanhamento
do processo objeto deste contrato.
</p>

<hr>

<p>
<b>CLÁUSULA 4º:</b> Fica eleito o Foro da cidade de Novo Hamburgo/RS para dirimir quaisquer
questões oriundas deste contrato.
</p>

<div class="assinaturas">
    <div class="assinatura">
        ___________________________<br>
        CONTRATADA
    </div>
    <div class="assinatura">
        ___________________________<br>
        CONTRATANTE
    </div>
</div>

</body>
</html>
HTML;

// ===============================
// FORÇAR DOWNLOAD DO HTML
// ===============================
header("Content-Type: text/html; charset=UTF-8");
header("Content-Disposition: attachment; filename=contrato.html");
header("Content-Length: " . strlen($html));

echo $html;
exit;
