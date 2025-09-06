<?php

include __DIR__ . '/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Verifica se o método da requisição é GET

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db_connection = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    try {
        // Prepara a consulta SQL para buscar todos os CEPs salvos
        $stmt = $db_connection->prepare("SELECT * FROM enderecos");
        $stmt->execute();

        // Busca todos os resultados
        $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retorna os resultados em formato JSON
        echo json_encode(['success' => true, 'data' => $enderecos]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Erro ao buscar CEPs no banco de dados',
            'details' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método não permitido, use GET"]);
}