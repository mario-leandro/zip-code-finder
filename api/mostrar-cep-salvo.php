<?php
// importação das configurações e funções
include __DIR__ . '/database.php';
include __DIR__ . '/funcoes.php';

// Aplica headers de segurança e CORS
setSecurityHeaders();

try {
    // Conexão com o banco de dados
    $db = getConnection();

    // Verifica se o método da requisição é GET
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        jsonResponse(405, ['success' => false, 'message' => 'Método não permitido, use GET']);
    }

    // Busca todos os CEPs salvos, ordenados pelo mais recente
    $stmt = $db->prepare("SELECT * FROM enderecos ORDER BY id ASC");
    $stmt->execute();

    // Busca os resultados como um array associativo
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse(200, ['success' => true, 'data' => $enderecos]);
} catch (Exception $e) {
    jsonResponse(500, [
        'success' => false,
        'message' => 'Erro ao buscar CEPs' . $e->getMessage(),
    ]);
}
