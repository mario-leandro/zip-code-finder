<?php
// Configurações do banco de dados
$servername = 'localhost';
$username   = 'root';
$password   = '#Mario2004';
$dbname     = 'cep_db';

// Função para obter uma conexão PDO
function getConnection(): PDO {
    // Configurações de conexão
    global $servername, $username, $password, $dbname;

    // Cria a conexão PDO com tratamento de erros
    $pdo = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION] // Tratamento de erros
    );

    return $pdo;
}
