<?php
/**
 * API REST para gerenciamento de produtos
 */

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

// Função para enviar resposta JSON
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Função para obter dados do body da requisição
function getRequestBody() {
    $data = json_decode(file_get_contents('php://input'), true);
    return $data ?: [];
}

try {
    switch ($method) {
        case 'GET':
            // Listar todos os produtos ou buscar por ID
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM produtos WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $produto = $result->fetch_assoc();
                    sendResponse(true, $produto, 'Produto encontrado');
                } else {
                    sendResponse(false, null, 'Produto não encontrado', 404);
                }
                $stmt->close();
            } else {
                // Filtrar por categoria se fornecido
                $categoria = isset($_GET['categoria']) ? $_GET['categoria'] : null;
                
                if ($categoria) {
                    $stmt = $conn->prepare("SELECT * FROM produtos WHERE categoria = ? ORDER BY nome ASC");
                    $stmt->bind_param("s", $categoria);
                } else {
                    $stmt = $conn->prepare("SELECT * FROM produtos ORDER BY nome ASC");
                }
                
                $stmt->execute();
                $result = $stmt->get_result();
                $produtos = [];
                
                while ($row = $result->fetch_assoc()) {
                    $produtos[] = $row;
                }
                
                $stmt->close();
                sendResponse(true, $produtos, 'Produtos listados com sucesso');
            }
            break;
            
        case 'POST':
            // Criar novo produto
            $data = getRequestBody();
            
            if (!isset($data['nome']) || !isset($data['categoria'])) {
                sendResponse(false, null, 'Campos obrigatórios: nome e categoria', 400);
            }
            
            $nome = $data['nome'];
            $categoria = $data['categoria'];
            $descricao = isset($data['descricao']) ? $data['descricao'] : '';
            $imagem_url = isset($data['imagem_url']) ? $data['imagem_url'] : '';
            $modelo_3d_url = isset($data['modelo_3d_url']) ? $data['modelo_3d_url'] : '';
            
            $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, descricao, imagem_url, modelo_3d_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $categoria, $descricao, $imagem_url, $modelo_3d_url);
            
            if ($stmt->execute()) {
                $id = $conn->insert_id;
                sendResponse(true, ['id' => $id], 'Produto criado com sucesso', 201);
            } else {
                sendResponse(false, null, 'Erro ao criar produto: ' . $conn->error, 500);
            }
            
            $stmt->close();
            break;
            
        case 'PUT':
            // Atualizar produto
            $data = getRequestBody();
            
            if (!isset($data['id'])) {
                sendResponse(false, null, 'ID do produto é obrigatório', 400);
            }
            
            $id = intval($data['id']);
            $nome = isset($data['nome']) ? $data['nome'] : null;
            $categoria = isset($data['categoria']) ? $data['categoria'] : null;
            $descricao = isset($data['descricao']) ? $data['descricao'] : null;
            $imagem_url = isset($data['imagem_url']) ? $data['imagem_url'] : null;
            $modelo_3d_url = isset($data['modelo_3d_url']) ? $data['modelo_3d_url'] : null;
            
            // Construir query dinamicamente baseado nos campos fornecidos
            $fields = [];
            $params = [];
            $types = '';
            
            if ($nome !== null) {
                $fields[] = "nome = ?";
                $params[] = $nome;
                $types .= "s";
            }
            if ($categoria !== null) {
                $fields[] = "categoria = ?";
                $params[] = $categoria;
                $types .= "s";
            }
            if ($descricao !== null) {
                $fields[] = "descricao = ?";
                $params[] = $descricao;
                $types .= "s";
            }
            if ($imagem_url !== null) {
                $fields[] = "imagem_url = ?";
                $params[] = $imagem_url;
                $types .= "s";
            }
            if ($modelo_3d_url !== null) {
                $fields[] = "modelo_3d_url = ?";
                $params[] = $modelo_3d_url;
                $types .= "s";
            }
            
            if (empty($fields)) {
                sendResponse(false, null, 'Nenhum campo para atualizar', 400);
            }
            
            $types .= "i";
            $params[] = $id;
            
            $sql = "UPDATE produtos SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                sendResponse(true, null, 'Produto atualizado com sucesso');
            } else {
                sendResponse(false, null, 'Erro ao atualizar produto: ' . $conn->error, 500);
            }
            
            $stmt->close();
            break;
            
        case 'DELETE':
            // Deletar produto
            if (!isset($_GET['id'])) {
                sendResponse(false, null, 'ID do produto é obrigatório', 400);
            }
            
            $id = intval($_GET['id']);
            $stmt = $conn->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    sendResponse(true, null, 'Produto deletado com sucesso');
                } else {
                    sendResponse(false, null, 'Produto não encontrado', 404);
                }
            } else {
                sendResponse(false, null, 'Erro ao deletar produto: ' . $conn->error, 500);
            }
            
            $stmt->close();
            break;
            
        default:
            sendResponse(false, null, 'Método não permitido', 405);
            break;
    }
    
} catch (Exception $e) {
    sendResponse(false, null, 'Erro: ' . $e->getMessage(), 500);
} finally {
    $conn->close();
}

