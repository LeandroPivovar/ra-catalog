<?php
/**
 * Script para popular o banco de dados com novos produtos e categorias
 * Remove os produtos e categorias existentes e insere os novos
 */

require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $conn = getConnection();
    
    if (!$conn) {
        throw new Exception("Erro ao conectar ao banco de dados");
    }
    
    // Iniciar transação
    $conn->begin_transaction();
    
    // Remover todos os produtos
    $conn->query("DELETE FROM produtos");
    echo "Produtos existentes removidos.\n";
    
    // Remover todas as categorias
    $conn->query("DELETE FROM categorias");
    echo "Categorias existentes removidas.\n";
    
    // Resetar auto_increment
    $conn->query("ALTER TABLE categorias AUTO_INCREMENT = 1");
    $conn->query("ALTER TABLE produtos AUTO_INCREMENT = 1");
    
    // Definir categorias e produtos
    $categorias = [
        'Móveis' => [
            'icone' => '🛋️',
            'produtos' => [
                'Sofás',
                'Poltronas',
                'Cadeiras premium',
                'Armário premium',
                'Escrivaninhas'
            ]
        ],
        'Tecnologia & Gadgets' => [
            'icone' => '💻',
            'produtos' => [
                'Caixas de som bluetooth',
                'Smartwatches',
                'Consoles / videogames (PS5)'
            ]
        ],
        'Automotivo' => [
            'icone' => '🚗',
            'produtos' => [
                'Carros',
                'Motos',
                'Bicicletas',
                'Pneus',
                'Capacetes'
            ]
        ],
        'Instrumentos Musicais' => [
            'icone' => '🎸',
            'produtos' => [
                'Violões',
                'Baterias',
                'Teclados / pianos digitais'
            ]
        ],
        'Eletrodomésticos' => [
            'icone' => '🏠',
            'produtos' => [
                'Geladeiras',
                'Fogões / cooktops',
                'Máquinas de lavar',
                'Ar-condicionado',
                'Air fryer (linha premium)'
            ]
        ],
        'Acessórios' => [
            'icone' => '👓',
            'produtos' => [
                'Óculos',
                'Relógios',
                'Bolsas'
            ]
        ]
    ];
    
    // Inserir categorias e obter seus IDs
    $stmtCategoria = $conn->prepare("INSERT INTO categorias (nome, icone) VALUES (?, ?)");
    $categoriaIds = [];
    
    foreach ($categorias as $nomeCategoria => $dados) {
        $stmtCategoria->bind_param("ss", $nomeCategoria, $dados['icone']);
        $stmtCategoria->execute();
        $categoriaIds[$nomeCategoria] = $conn->insert_id;
        echo "Categoria '{$nomeCategoria}' inserida (ID: {$categoriaIds[$nomeCategoria]}).\n";
    }
    $stmtCategoria->close();
    
    // Inserir produtos
    $stmtProduto = $conn->prepare("INSERT INTO produtos (nome, categoria, categoria_id, descricao) VALUES (?, ?, ?, ?)");
    
    foreach ($categorias as $nomeCategoria => $dados) {
        $categoriaId = $categoriaIds[$nomeCategoria];
        
        foreach ($dados['produtos'] as $nomeProduto) {
            // Criar descrição padrão baseada no nome do produto
            $descricao = "Produto {$nomeProduto} da categoria {$nomeCategoria}. Qualidade premium e design moderno.";
            
            $stmtProduto->bind_param("ssis", $nomeProduto, $nomeCategoria, $categoriaId, $descricao);
            $stmtProduto->execute();
            echo "Produto '{$nomeProduto}' inserido na categoria '{$nomeCategoria}'.\n";
        }
    }
    $stmtProduto->close();
    
    // Confirmar transação
    $conn->commit();
    
    // Contar registros inseridos
    $result = $conn->query("SELECT COUNT(*) as total FROM categorias");
    $totalCategorias = $result->fetch_assoc()['total'];
    
    $result = $conn->query("SELECT COUNT(*) as total FROM produtos");
    $totalProdutos = $result->fetch_assoc()['total'];
    
    $conn->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Banco de dados populado com sucesso!',
        'categorias' => $totalCategorias,
        'produtos' => $totalProdutos
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    if (isset($conn) && $conn) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro ao popular banco de dados: ' . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}

