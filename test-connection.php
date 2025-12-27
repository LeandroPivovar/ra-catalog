<?php
/**
 * Script de teste de conexão com o banco de dados
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== Teste de Conexão ===\n\n";

// Testar conexão básica
echo "1. Testando conexão MySQL...\n";
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    echo "❌ ERRO: " . $conn->connect_error . "\n";
    exit(1);
}
echo "✅ Conexão MySQL OK\n\n";

// Testar se o banco existe
echo "2. Verificando se o banco existe...\n";
$result = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
if ($result->num_rows > 0) {
    echo "✅ Banco '" . DB_NAME . "' existe\n\n";
} else {
    echo "❌ Banco '" . DB_NAME . "' NÃO existe\n";
    echo "   Execute install.php para criar o banco\n\n";
    $conn->close();
    exit(1);
}

// Testar seleção do banco
echo "3. Selecionando banco...\n";
if ($conn->select_db(DB_NAME)) {
    echo "✅ Banco selecionado com sucesso\n\n";
} else {
    echo "❌ ERRO ao selecionar banco: " . $conn->error . "\n\n";
    $conn->close();
    exit(1);
}

// Testar tabelas
echo "4. Verificando tabelas...\n";
$tables = ['categorias', 'produtos'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "✅ Tabela '$table' existe\n";
    } else {
        echo "❌ Tabela '$table' NÃO existe\n";
    }
}

$conn->close();
echo "\n=== Teste concluído ===\n";


