<?php
/**
 * Script de instalação do sistema
 * Cria o banco de dados e as tabelas necessárias
 */

require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar se já está instalado
$checkFile = 'installed.flag';
if (file_exists($checkFile)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Sistema já está instalado. Delete o arquivo installed.flag para reinstalar.'
    ]);
    exit;
}

try {
    // Conectar ao MySQL sem selecionar banco
    $conn = getConnectionWithoutDB();
    
    // Criar banco de dados se não existir
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "Banco de dados criado com sucesso ou já existe.\n";
    } else {
        throw new Exception("Erro ao criar banco de dados: " . $conn->error);
    }
    
    // Selecionar o banco
    $conn->select_db(DB_NAME);
    
    // Criar tabela de produtos
    $sql = "CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        categoria VARCHAR(100) NOT NULL,
        descricao TEXT,
        imagem_url VARCHAR(500),
        modelo_3d_url VARCHAR(500),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_categoria (categoria)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabela 'produtos' criada com sucesso.\n";
    } else {
        throw new Exception("Erro ao criar tabela produtos: " . $conn->error);
    }
    
    // Inserir alguns produtos de exemplo
    $produtosExemplo = [
        ['Smartphone XYZ', 'Eletrônicos', 'Smartphone de última geração com recursos avançados.', 'https://via.placeholder.com/180x180/2196F3/FFFFFF?text=Smartphone', ''],
        ['Tablet Pro', 'Eletrônicos', 'Tablet profissional para trabalho e entretenimento.', 'https://via.placeholder.com/180x180/4ECDC4/FFFFFF?text=Tablet', ''],
        ['Fones Bluetooth', 'Eletrônicos', 'Fones de ouvido sem fio com cancelamento de ruído.', 'https://via.placeholder.com/180x180/95E1D3/FFFFFF?text=Fones', ''],
        ['Tênis Esportivo', 'Calçados', 'Tênis confortável para atividades físicas.', 'https://via.placeholder.com/180x180/2196F3/FFFFFF?text=Tenis+1', ''],
        ['Tênis Casual', 'Calçados', 'Tênis casual para uso no dia a dia.', 'https://via.placeholder.com/180x180/4ECDC4/FFFFFF?text=Tenis+2', ''],
        ['Sapato Social', 'Calçados', 'Sapato elegante para ocasiões formais.', 'https://via.placeholder.com/180x180/95E1D3/FFFFFF?text=Sapato', ''],
        ['Geladeira Frost Free', 'Eletrodomésticos', 'Geladeira moderna com tecnologia frost free.', 'https://via.placeholder.com/180x180/2196F3/FFFFFF?text=Geladeira', ''],
        ['Microondas 30L', 'Eletrodomésticos', 'Microondas com capacidade de 30 litros.', 'https://via.placeholder.com/180x180/4ECDC4/FFFFFF?text=Microondas', ''],
        ['Lavadora 12kg', 'Eletrodomésticos', 'Lavadora de roupas com capacidade de 12kg.', 'https://via.placeholder.com/180x180/95E1D3/FFFFFF?text=Lavadora', ''],
    ];
    
    $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, descricao, imagem_url, modelo_3d_url) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($produtosExemplo as $produto) {
        $stmt->bind_param("sssss", $produto[0], $produto[1], $produto[2], $produto[3], $produto[4]);
        $stmt->execute();
    }
    
    $stmt->close();
    echo "Produtos de exemplo inseridos.\n";
    
    // Criar arquivo de flag de instalação
    file_put_contents($checkFile, date('Y-m-d H:i:s'));
    
    $conn->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Instalação concluída com sucesso!',
        'database' => DB_NAME,
        'tables' => ['produtos']
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na instalação: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

