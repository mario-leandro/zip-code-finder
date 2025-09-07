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

    // 🔎 Verifica se o CEP já existe no banco
    $checkStmt = $db_connection->prepare("SELECT 1 FROM enderecos WHERE cep = :cep LIMIT 1");
    $checkStmt->execute([':cep' => $cep]);

    if ($checkStmt->fetch()) {
        http_response_code(409); // 409 Conflict → já existe
        echo json_encode(['success' => false, 'message' => 'CEP já está salvo no banco de dados']);
        exit;
    }

    // 📌 Se não existe, insere
    $stmt = $db_connection->prepare("
        INSERT INTO enderecos (cep, logradouro, bairro, cidade, estado, ddd)
        VALUES (:cep, :logradouro, :bairro, :cidade, :estado, :ddd)
    ");

    $arrCep = [
        ':cep'        => $cep,
        ':logradouro' => $input['logradouro'] ?? null,
        ':bairro'     => $input['bairro'] ?? null,
        ':cidade'     => $input['localidade'] ?? null,
        ':estado'     => $input['uf'] ?? null,
        ':ddd'        => $input['ddd'] ?? null
    ];

    $stmt->execute($arrCep);

    echo json_encode(['success' => true, 'message' => 'CEP salvo com sucesso', 'data' => $arrCep]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'details' => $e->getMessage() // pode remover em produção
    ]);
} finally {
    if (isset($db_connection)) {
        $db_connection = null;
    }
}
