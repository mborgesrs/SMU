<?php
/**
 * ZapSign API Service
 */
class ZapSignService {
    private $apiToken;
    private $apiUrl;

    private $environment;

    public function __construct() {
        require_once __DIR__ . '/ConfiguracaoModel.php';
        $configModel = new ConfiguracaoModel();
        $config = $configModel->getConfig();

        $this->apiToken = !empty($config['zapsign_api_token']) ? $config['zapsign_api_token'] : (defined('ZAPSIGN_API_TOKEN') ? ZAPSIGN_API_TOKEN : '');
        $this->environment = !empty($config['zapsign_environment']) ? $config['zapsign_environment'] : (defined('ZAPSIGN_ENVIRONMENT') ? ZAPSIGN_ENVIRONMENT : 'sandbox');
        $this->apiUrl = 'https://api.zapsign.com.br/api/v1';
    }

    private function request($method, $endpoint, $data = null) {
        $url = $this->apiUrl . $endpoint . '?api_token=' . $this->apiToken;
        $ch = curl_init($url);

        $headers = [
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
            $error = $result['detail'] ?? 'ZapSign API Error';
            throw new Exception($error);
        }

        return $result;
    }

    /**
     * Create document via Base64
     */
    public function createDocument($name, $base64Pdf) {
        return $this->request('POST', '/docs/', [
            'name' => $name,
            'base64_pdf' => $base64Pdf,
            'sandbox' => ($this->environment === 'sandbox')
        ]);
    }

    /**
     * Add signer to a document
     */
    public function addSigner($docToken, $name, $email) {
        return $this->request('POST', "/docs/$docToken/signer/", [
            'name' => $name,
            'email' => $email,
            'auth_mode' => 'assinaturaTela' // Standard screen signature
        ]);
    }

    /**
     * Get document details
     */
    public function getDocument($docToken) {
        return $this->request('GET', "/docs/$docToken/");
    }
}
