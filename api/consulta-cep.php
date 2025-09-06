<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Verifica se o método da requisição é POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lê o corpo da requisição e decodifica o JSON recebido em um array associativo
    $input = json_decode(file_get_contents('php://input'), true);

    // Verifica se o CEP foi informado
    if (empty($input['cep'])) {
        // Retorna um erro se o CEP não foi informado
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CEP não informado']);
        exit;
    }

    // Remove qualquer caractere que não seja número
    $cep = preg_replace('/[^0-9]/', '', $input['cep']);

    // Verifica se o CEP tem 8 dígitos
    if (strlen($cep) !== 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CEP inválido']);
        exit;
    }

    // Consulta o serviço ViaCEP
    $url_api = "https://viacep.com.br/ws/$cep/json/";

    // Usa file_get_contents para fazer a requisição
    $ch = curl_init($url_api);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout de 10s
    $json = curl_exec($ch);

    if (curl_errno($ch)) {
        http_response_code(502);
        echo json_encode([
            'success' => false,
            'message' => 'Falha ao acessar ViaCEP',
            'error'   => curl_error($ch)
        ]);
        curl_close($ch);
        exit;
    }

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        http_response_code($http_code);
        echo json_encode([
            'success' => false,
            'message' => "ViaCEP retornou status $http_code"
        ]);
        exit;
    }

    // Decodifica a resposta JSON
    $response = json_decode($json, true);

    // Verifica se o CEP foi encontrado
    if (isset($response['erro']) && $response['erro'] === true) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'CEP não encontrado']);
        exit;
    }

    // Retorna a resposta da API ViaCEP
    echo json_encode($response);
    exit;

} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido, use POST"]);
}