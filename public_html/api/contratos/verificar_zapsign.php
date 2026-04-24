<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ContratoModel.php';
require_once __DIR__ . '/../../models/ZapSignService.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'ID do contrato não informado']);
    exit;
}

$id = intval($_GET['id']);
$contratoModel = new ContratoModel();
$zapService = new ZapSignService();

try {
    $contrato = $contratoModel->getById($id);
    if (!$contrato) throw new Exception('Contrato não encontrado');
    if (empty($contrato['zapsign_doc_id'])) throw new Exception('Contrato não foi enviado para ZapSign');

    // Consultar ZapSign API
    $doc = $zapService->getDocument($contrato['zapsign_doc_id']);

    if (isset($doc['status']) && $doc['status'] === 'signed') {
        // Atualizar banco
        $db = getDB();
        $stmt = $db->prepare("UPDATE contratos SET status = 'ativo', zapsign_status = 'signed' WHERE id = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'signed' => true,
            'message' => 'Contrato assinado! Status atualizado para Ativo.'
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'signed' => false,
            'message' => 'Contrato ainda está aguardando assinatura.'
        ]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
