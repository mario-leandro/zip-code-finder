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
    $db_connection = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Define o modo de erro para lançar exceções
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conectado com sucesso";
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
} finally {
    $db_connection = null;
}