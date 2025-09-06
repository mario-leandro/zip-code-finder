<?php

include __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $db_connection = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Método não permitido, use POST']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['cep'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CEP não informado']);
        exit;
    }

    $cep = str_replace('-', '', $input['cep']);

    if (strlen($cep) !== 8) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'CEP inválido']);
        exit;
    }

    // Prepara a consulta SQL com placeholders para evitar SQL Injection
    $stmt = $db_connection->prepare("
        INSERT INTO enderecos (cep, logradouro, bairro, cidade, estado, ddd)
        VALUES (:cep, :logradouro, :bairro, :cidade, :estado, :ddd)
        ON DUPLICATE KEY UPDATE
            logradouro = VALUES(logradouro),
            bairro = VALUES(bairro),
            cidade = VALUES(cidade),
            estado = VALUES(estado),
            ddd = VALUES(ddd)
    ");

    // Prepara os dados para inserção
    // esse dois pontos antes do nome da variável são importantes para o PDO
    // eles indicam que é um valor será inserido ali depois
    $arrCep = [
        ':cep' => $cep,
        ':logradouro' => $input['logradouro'] ?? null,
        ':bairro' => $input['bairro'] ?? null,
        ':cidade' => $input['localidade'] ?? null,
        ':estado' => $input['uf'] ?? null,
        ':ddd' => $input['ddd'] ?? null
    ];

    $stmt->execute($arrCep);

    echo json_encode(['success' => true, 'message' => 'CEP salvo com sucesso', 'data' => $arrCep]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'details' => $e->getMessage() // útil para debug local
    ]);
} finally {
    if (isset($db_connection)) {
        $db_connection = null;
    }
}
