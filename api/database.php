<?php
// Configurações de conexão com o banco de dados
$servername = 'localhost';
$username = 'root';
$password = '#Mario2004';
$dbname = 'cep_db';

// Inicializa a variável de conexão
$db_connection = null;

// Cria a conexão
try {
    $db_connection = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // NENHUM echo aqui
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na conexão com o banco de dados',
        'details' => $e->getMessage()
    ]);
    exit;
}
