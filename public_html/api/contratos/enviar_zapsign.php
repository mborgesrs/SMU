<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ContratoModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../models/ZapSignService.php';
require_once __DIR__ . '/../../pdf/contrato_fpdf.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID do contrato não informado']);
    exit;
}

$id = intval($_GET['id']);
$contratoModel = new ContratoModel();
$clienteModel = new ClienteModel();
$zapService = new ZapSignService();

try {
    $contrato = $contratoModel->getById($id);
    if (!$contrato) throw new Exception('Contrato não encontrado');

    $cliente = $clienteModel->getById($contrato['id_contratante']);
    if (!$cliente) throw new Exception('Cliente não encontrado');

    // 1. Gerar PDF em string e converter para Base64
    $pdfContent = gerarContratoPDF($id);
    if (!$pdfContent) throw new Exception('Erro ao gerar PDF do contrato');
    $base64Pdf = base64_encode($pdfContent);

    // 2. Criar documento na ZapSign
    $docName = "Contrato - " . ($cliente['nome'] ?? 'Cliente');
    $zapsignDoc = $zapService->createDocument($docName, $base64Pdf);
    $docToken = $zapsignDoc['token'];

    // 3. Adicionar signatário (Cliente)
    $email = $cliente['email'] ?? 'atendimento@rsmaternidade.com.br'; // Fallback se não houver email
    $signer = $zapService->addSigner($docToken, $cliente['nome'], $email);
    $signUrl = $signer['sign_url'];

    // 4. Atualizar banco de dados
    $db = getDB();
    $stmt = $db->prepare("UPDATE contratos SET zapsign_doc_id = ?, zapsign_url = ?, zapsign_status = 'sent' WHERE id = ?");
    $stmt->execute([$docToken, $signUrl, $id]);

    echo json_encode([
        'success' => true,
        'message' => 'Contrato enviado para ZapSign com sucesso',
        'sign_url' => $signUrl
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
