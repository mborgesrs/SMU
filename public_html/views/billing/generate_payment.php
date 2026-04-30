<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../models/AsaasService.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();

    if (!$company) {
        throw new Exception("Empresa não encontrada.");
    }

    $asaas = new AsaasService();

    // 1. Create customer if not exists
    if (empty($company['asaas_customer_id'])) {
        try {
            $customer = $asaas->createCustomer($company);
            $customerId = $customer['id'];

            $stmt = $db->prepare("UPDATE companies SET asaas_customer_id = ? WHERE id = ?");
            $stmt->execute([$customerId, $company['id']]);
            $company['asaas_customer_id'] = $customerId;
        } catch (Exception $e) {
            throw new Exception("Erro ao criar perfil no Asaas: " . $e->getMessage());
        }
    } else {
        $customerId = $company['asaas_customer_id'];
    }

    $paymentLink = '';

    // 2. Decide what to pay: Setup or Subscription
    if (!$company['setup_paid']) {
        // Create Setup Payment (R$ 297,00)
        $payment = $asaas->createSetupPayment($customerId, 297.00);
        $paymentLink = $payment['invoiceUrl'] ?? '';
    } else {
        // Create or get subscription link
        if (empty($company['asaas_subscription_id'])) {
            $amount = ($company['plan_price'] > 0) ? $company['plan_price'] : 79.00;
            $interval = $company['plan_interval'] ?: 'MONTHLY';

            // Apply annual discount if selected (e.g., 20% off)
            if ($interval === 'YEARLY') {
                $amount = ($amount * 12) * 0.80;
            }

            $subscription = $asaas->createSubscription($customerId, $amount, $interval);

            $stmt = $db->prepare("UPDATE companies SET asaas_subscription_id = ? WHERE id = ?");
            $stmt->execute([$subscription['id'], $company['id']]);
            $company['asaas_subscription_id'] = $subscription['id'];
        }

        // Find latest pending or overdue payment for the customer
        $paymentsData = $asaas->getCustomerPayments($customerId);
        if (!empty($paymentsData['data'])) {
            foreach ($paymentsData['data'] as $p) {
                if ($p['status'] === 'PENDING' || $p['status'] === 'OVERDUE') {
                    $paymentLink = $p['invoiceUrl'];
                    break;
                }
            }
        }
    }

    if (!empty($paymentLink)) {
        header('Location: ' . $paymentLink);
    } else {
        // If no link, show a message
        echo "<script>
            alert('Estamos gerando sua cobrança. Por favor, contate o suporte se o link não aparecer em instantes.');
            window.location.href = 'status.php';
        </script>";
    }
    exit;

} catch (Exception $e) {
    die("<div style='font-family: sans-serif; padding: 20px; color: #721c24; background: #f8d7da; border-radius: 10px; margin: 20px;'>
            <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "
            <br><br>
            <a href='status.php' style='color: #721c24; font-weight: bold;'>Voltar</a>
         </div>");
}
