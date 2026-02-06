<?php
/**
 * Asaas Webhook Handler
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

// Get the POST body
$body = file_get_contents('php://input');
$data = json_decode($body, true);

if (!$data) {
    http_response_code(400);
    exit;
}

$event = $data['event'];
$payment = $data['payment'];

try {
    $db = getDB();

    if ($event === 'PAYMENT_RECEIVED' || $event === 'PAYMENT_CONFIRMED') {
        $customerId = $payment['customer'];
        $externalReference = $payment['externalReference'] ?? '';

        // Find company by customer ID
        $stmt = $db->prepare("SELECT id, setup_paid FROM companies WHERE asaas_customer_id = ?");
        $stmt->execute([$customerId]);
        $company = $stmt->fetch();

        if ($company) {
            $updateFields = [];
            $params = [];

            // If it was a setup payment
            if (strpos($externalReference, 'setup_') !== false) {
                $updateFields[] = "setup_paid = 1";
            }

            // Always activate if a payment is received (unless you want more specific logic)
            $updateFields[] = "billing_status = 'active'";
            $updateFields[] = "status = 'active'";
            
            if ($payment['subscription'] ?? null) {
                $updateFields[] = "asaas_subscription_id = ?";
                $params[] = $payment['subscription'];
            }

            $sql = "UPDATE companies SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $params[] = $company['id'];

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            file_put_contents('asaas_webhook_log.txt', "[" . date('Y-m-d H:i:s') . "] Success: Payment received for company " . $company['id'] . "\n", FILE_APPEND);
        }
    }

    if ($event === 'PAYMENT_OVERDUE') {
        $customerId = $payment['customer'];
        
        $stmt = $db->prepare("UPDATE companies SET billing_status = 'overdue' WHERE asaas_customer_id = ?");
        $stmt->execute([$customerId]);
    }

    http_response_code(200);
    echo json_encode(['status' => 'ok']);

} catch (Exception $e) {
    file_put_contents('asaas_webhook_error.txt', "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
}
