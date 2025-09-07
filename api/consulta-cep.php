<?php
// Importação das configurações e funções
include __DIR__ . '/funcoes.php';

// Aplica headers de segurança e CORS
setSecurityHeaders();

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, [
            'success' => false,
            'message' => 'Método não permitido, use POST'
        ]);
    }

    // Lê o corpo da requisição e decodifica o JSON
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['cep'])) {
        jsonResponse(400, [
            'success' => false,
            'message' => 'CEP não informado'
        ]);
    }

    $cep = sanitizeCep($input['cep']);

    if (!validateCep($cep)) {
        jsonResponse(400, [
            'success' => false,
            'message' => 'CEP inválido'
        ]);
    }

    // URL da API ViaCEP
    $url_api = "https://viacep.com.br/ws/$cep/json/";

    // Faz a requisição via cURL
    $ch = curl_init($url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $json = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        jsonResponse(502, [
            'success' => false,
            'message' => 'Falha ao acessar ViaCEP',
            'error'   => $error
        ]);
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        jsonResponse($http_code, [
            'success' => false,
            'message' => "ViaCEP retornou status $http_code"
        ]);
    }

    // Decodifica JSON da API
    $response = json_decode($json, true);

    if (isset($response['erro']) && $response['erro'] === true) {
        jsonResponse(404, [
            'success' => false,
            'message' => 'CEP não encontrado'
        ]);
    }

    jsonResponse(200, $response);
} catch (Exception $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erro interno do servidor' . $e->getMessage(),
    ]);
}
