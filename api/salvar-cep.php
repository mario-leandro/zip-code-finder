<?php
// importação das configurações e funções
include __DIR__ . '/database.php';
include __DIR__ . '/funcoes.php';

// Aplica headers de segurança e CORS
setSecurityHeaders();

try {
    // Conexão com o banco de dados
    $db = getConnection();

    // Verifica se o método da requisição é POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        jsonResponse(405, ['success' => false, 'message' => 'Método não permitido, use POST']);
    }

    // Lê o corpo da requisição e decodifica o JSON recebido em um array associativo
    $input = json_decode(file_get_contents('php://input'), true);

    // Valida se o CEP foi informado
    if (empty($input['cep'])) {
        jsonResponse(400, ['success' => false, 'message' => 'CEP não informado']);
    }

    // Trata o CEP (remoção de caracteres não numéricos e validação de tamanho)
    $cep = sanitizeCep($input['cep']);

    // Valida o CEP
    if (!validateCep($cep)) {
        jsonResponse(400, ['success' => false, 'message' => 'CEP inválido']);
    }

    // Verifica se o CEP já existe
    $checkStmt = $db->prepare("SELECT 1 FROM enderecos WHERE cep = :cep LIMIT 1");
    $checkStmt->execute([':cep' => $cep]);

    // Se já existir, retorna mensagem de conflito
    if ($checkStmt->fetch()) {
        jsonResponse(409, ['success' => false, 'message' => 'CEP já está salvo no banco de dados']);
    }

    // Se não existir, insere o novo CEP
    $stmt = $db->prepare("
        INSERT INTO enderecos (cep, logradouro, bairro, cidade, estado, ddd)
        VALUES (:cep, :logradouro, :bairro, :cidade, :estado, :ddd)
    ");

    // Prepara os dados para inserção, usando null para campos ausentes
    $arrCep = [
        ':cep'        => $cep,
        ':logradouro' => $input['logradouro'] ?? null,
        ':bairro'     => $input['bairro'] ?? null,
        ':cidade'     => $input['localidade'] ?? null,
        ':estado'     => $input['uf'] ?? null,
        ':ddd'        => $input['ddd'] ?? null
    ];

    $stmt->execute($arrCep);

    jsonResponse(201, ['success' => true, 'message' => 'CEP salvo com sucesso', 'data' => $arrCep]);
} catch (Exception $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erro interno do servidor ' . $e->getMessage(),
    ]);
}
