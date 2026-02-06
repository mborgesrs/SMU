<?php
header('Content-Type: application/json');

$cep = $_GET['cep'] ?? '';

// Sanitize CEP
$cep = preg_replace('/[^0-9]/', '', $cep);

if (strlen($cep) !== 8) {
    http_response_code(400);
    echo json_encode(['erro' => true, 'message' => 'CEP inválido']);
    exit;
}

// Switching to BrasilAPI as ViaCEP is blocking/failing
$url = "https://brasilapi.com.br/api/cep/v1/{$cep}";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);

if ($httpCode !== 200) {
    // Fallback para ViaCEP
    $urlFallback = "https://viacep.com.br/ws/{$cep}/json/";
    $chF = curl_init();
    curl_setopt($chF, CURLOPT_URL, $urlFallback);
    curl_setopt($chF, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($chF, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($chF, CURLOPT_TIMEOUT, 10);
    $responseF = curl_exec($chF);
    $httpCodeF = curl_getinfo($chF, CURLINFO_HTTP_CODE);
    curl_close($chF);

    if ($httpCodeF === 200) {
        $viaCepData = json_decode($responseF, true);
        if (isset($viaCepData['cep'])) {
            echo json_encode($viaCepData);
            exit;
        }
    }

    // If both failed
    http_response_code($httpCode === 404 ? 404 : 502);
    echo json_encode([
        'erro' => true, 
        'message' => "Erro na busca do CEP (BrasilAPI code $httpCode). Tentativa de fallback também falhou.", 
        'debug' => $curlError ?: $response
    ]);
    exit;
}

// BrasilAPI returns different keys: state, city, neighborhood, street
// We map them to ViaCEP format to match frontend expectations: logradouro, bairro, localidade, uf
$data = json_decode($response, true);

if (!$data) {
    http_response_code(502);
    echo json_encode(['erro' => true, 'message' => 'Erro ao processar resposta JSON']);
    exit;
}

$mapped = [
    'cep' => $data['cep'] ?? $cep,
    'logradouro' => $data['street'] ?? '',
    'bairro' => $data['neighborhood'] ?? '',
    'localidade' => $data['city'] ?? '',
    'uf' => $data['state'] ?? '',
    'erro' => false
];

echo json_encode($mapped);
