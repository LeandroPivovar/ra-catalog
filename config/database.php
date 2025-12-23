<?php
/**
 * Configuração de conexão com o banco de dados MySQL
 */

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ifood_ra');

/**
 * Conecta ao banco de dados MySQL
 * @return mysqli|false Retorna a conexão ou false em caso de erro
 */
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        error_log("Erro de conexão MySQL: " . $conn->connect_error);
        return false;
    }
    
    // Selecionar o banco de dados
    if (!$conn->select_db(DB_NAME)) {
        error_log("Erro ao selecionar banco de dados: " . DB_NAME . " - " . $conn->error);
        return false;
    }
    
    // Definir charset para UTF-8
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Conecta ao MySQL sem selecionar banco (para instalação)
 * @return mysqli|false Retorna a conexão ou false em caso de erro
 */
function getConnectionWithoutDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

