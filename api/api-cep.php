<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['cep'])) {
        http_response_code(400);
        echo json_encode(['error' => 'CEP não informado']);
        exit;
    }

    $cep = preg_replace('/[^0-9]/', '', $input['cep']);

    if (strlen($cep) !== 8) {
        http_response_code(400);
        echo json_encode(['error' => 'CEP inválido']);
        exit;
    }

    $url_api = "https://viacep.com.br/ws/$cep/json/";

    $json = file_get_contents($url_api);

    if ($json === false) {
        http_response_code(502);
        echo json_encode(['error' => 'Falha ao acessar ViaCEP']);
        exit;
    }

    $response = json_decode($json, true);

    if (isset($response['erro']) && $response['erro'] === true) {
        http_response_code(404);
        echo json_encode(['error' => 'CEP não encontrado']);
        exit;
    }

    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} else {
    http_response_code(405);
    echo json_encode(["error" => "Método não permitido, use POST"]);
}
