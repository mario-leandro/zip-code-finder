<?php
// importação das configurações e funções
include __DIR__ . '/database.php';
include __DIR__ . '/funcoes.php';

// Aplica headers de segurança e CORS
setSecurityHeaders();

try {
    // Conexão com o banco de dados
    $db = getConnection();

    // Verifica se o método da requisição é DELETE
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        jsonResponse(405, ['success' => false, 'message' => 'Método não permitido, use DELETE']);
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

    // Verifica se existe
    $check = $db->prepare("SELECT 1 FROM enderecos WHERE cep = :cep LIMIT 1");
    $check->execute([':cep' => $cep]);

    if (!$check->fetch()) {
        jsonResponse(404, ['success' => false, 'message' => 'CEP não encontrado']);
    }

    // Deleta o CEP
    $stmt = $db->prepare("DELETE FROM enderecos WHERE cep = :cep");
    $stmt->execute([':cep' => $cep]);

    jsonResponse(200, ['success' => true, 'message' => 'CEP removido com sucesso']);
} catch (Exception $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erro interno do servidor' . $e->getMessage(),
    ]);
}
