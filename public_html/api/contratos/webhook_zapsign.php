<?php
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/ContratoModel.php';

// Pegar o corpo da requisição (JSON)
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    exit('Invalid payload');
}

// O Webhook da ZapSign envia o status no campo 'event_type'
// Ex: doc_signed, all_signed
$eventType = $data['event_type'] ?? '';
$docToken = $data['document']['token'] ?? '';

if ($eventType === 'all_signed' && !empty($docToken)) {
    try {
        $db = getDB();
        
        // Buscar contrato pelo token da ZapSign
        $stmtCheck = $db->prepare("SELECT id FROM contratos WHERE zapsign_doc_id = ?");
        $stmtCheck->execute([$docToken]);
        $contrato = $stmtCheck->fetch();

        if ($contrato) {
            // Atualizar status para ativo
            $stmtUpdate = $db->prepare("UPDATE contratos SET status = 'ativo', zapsign_status = 'signed' WHERE id = ?");
            $stmtUpdate->execute([$contrato['id']]);
            
            error_log("ZapSign Webhook: Contrato " . $contrato['id'] . " assinado e ativado.");
        }
    } catch (Exception $e) {
        error_log("ZapSign Webhook Error: " . $e->getMessage());
        http_response_code(500);
        exit;
    }
}

http_response_code(200);
echo "OK";
