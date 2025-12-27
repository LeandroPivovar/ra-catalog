<?php
/**
 * Script de migração para adicionar coluna categoria_id
 */

require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = getConnectionWithoutDB();
    $conn->select_db(DB_NAME);
    
    // Verificar se a coluna categoria_id já existe
    $result = $conn->query("SHOW COLUMNS FROM produtos LIKE 'categoria_id'");
    
    if ($result->num_rows == 0) {
        // Adicionar coluna categoria_id
        $sql = "ALTER TABLE produtos ADD COLUMN categoria_id INT NULL AFTER categoria";
        if ($conn->query($sql) === TRUE) {
            echo "Coluna 'categoria_id' adicionada com sucesso.\n";
        } else {
            throw new Exception("Erro ao adicionar coluna: " . $conn->error);
        }
        
        // Adicionar índice
        $sql = "ALTER TABLE produtos ADD INDEX idx_categoria_id (categoria_id)";
        if ($conn->query($sql) === TRUE) {
            echo "Índice 'idx_categoria_id' criado com sucesso.\n";
        } else {
            echo "Aviso: Erro ao criar índice (pode já existir): " . $conn->error . "\n";
        }
        
        // Adicionar foreign key se possível
        $sql = "ALTER TABLE produtos ADD CONSTRAINT fk_produto_categoria 
                FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL";
        if ($conn->query($sql) === TRUE) {
            echo "Foreign key criada com sucesso.\n";
        } else {
            echo "Aviso: Erro ao criar foreign key (pode já existir): " . $conn->error . "\n";
        }
        
        // Atualizar produtos existentes com categoria_id baseado no nome da categoria
        echo "Atualizando produtos existentes...\n";
        $result = $conn->query("SELECT id, nome FROM categorias");
        $categoriaMap = [];
        while ($row = $result->fetch_assoc()) {
            $categoriaMap[$row['nome']] = $row['id'];
        }
        
        $stmt = $conn->prepare("UPDATE produtos SET categoria_id = ? WHERE categoria = ?");
        foreach ($categoriaMap as $nomeCategoria => $idCategoria) {
            $stmt->bind_param("is", $idCategoria, $nomeCategoria);
            $stmt->execute();
        }
        $stmt->close();
        echo "Produtos atualizados.\n";
        
    } else {
        echo "Coluna 'categoria_id' já existe.\n";
    }
    
    $conn->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Migração concluída com sucesso!'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro na migração: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}


