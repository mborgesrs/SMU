<?php
/**
 * Asaas API Service
 */
class AsaasService {
    private $apiKey;
    private $apiUrl;

    public function __construct() {
        require_once __DIR__ . '/ConfiguracaoModel.php';
        $configModel = new ConfiguracaoModel();
        $config = $configModel->getConfig();

        // 1. Prioritize database keys
        $this->apiKey = !empty($config['asaas_api_key']) ? $config['asaas_api_key'] : (defined('ASAAS_API_KEY') ? ASAAS_API_KEY : '');
        $environment = !empty($config['asaas_environment']) ? $config['asaas_environment'] : (defined('ASAAS_ENVIRONMENT') ? ASAAS_ENVIRONMENT : 'sandbox');

        $this->apiUrl = ($environment === 'production') 
            ? 'https://www.asaas.com/api/v3' 
            : 'https://sandbox.asaas.com/api/v3';
    }

    private function request($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint;
        $ch = curl_init($url);

        $headers = [
            'access_token: ' . $this->apiKey,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result = json_decode($response, true);

        if ($httpCode >= 400) {
            $error = $result['errors'][0]['description'] ?? 'Asaas API Error';
            throw new Exception($error);
        }

        return $result;
    }

    public function createCustomer($data) {
        return $this->request('POST', '/customers', [
            'name' => $data['razao_social'],
            'cpfCnpj' => $data['cnpj'],
            'email' => $data['email'],
            'phone' => $data['telefone'],
            'mobilePhone' => $data['telefone'],
            'postalCode' => $data['cep'],
            'address' => $data['logradouro'],
            'addressNumber' => $data['numero'],
            'complement' => $data['complemento'],
            'province' => $data['bairro'],
            'externalReference' => $data['id'],
            'notificationDisabled' => false
        ]);
    }

    public function createSetupPayment($customerId, $amount) {
        return $this->request('POST', '/payments', [
            'customer' => $customerId,
            'billingType' => 'UNDEFINED', // Allows customer to choose
            'value' => $amount,
            'dueDate' => date('Y-m-d', strtotime('+3 days')),
            'description' => 'Taxa de Implantação - SMU',
            'externalReference' => 'setup_' . time()
        ]);
    }

    public function createSubscription($customerId, $amount, $cycle = 'MONTHLY') {
        return $this->request('POST', '/subscriptions', [
            'customer' => $customerId,
            'billingType' => 'UNDEFINED',
            'value' => $amount,
            'cycle' => $cycle,
            'description' => 'Assinatura Mensal - SMU',
            'nextDueDate' => date('Y-m-d', strtotime('+30 days')),
        ]);
    }

    public function getPaymentStatus($paymentId) {
        return $this->request('GET', '/payments/' . $paymentId);
    }

    public function getSubscription($subscriptionId) {
        return $this->request('GET', '/subscriptions/' . $subscriptionId);
    }

    public function getCustomerPayments($customerId) {
        return $this->request('GET', '/payments?customer=' . $customerId . '&limit=10');
    }
}
