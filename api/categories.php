<?php
/**
 * API REST para gerenciamento de categorias
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
            // Listar todas as categorias ou buscar por ID
            if (isset($_GET['id'])) {
                $id = intval($_GET['id']);
                $stmt = $conn->prepare("SELECT * FROM categorias WHERE id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $categoria = $result->fetch_assoc();
                    sendResponse(true, $categoria, 'Categoria encontrada');
                } else {
                    sendResponse(false, null, 'Categoria não encontrada', 404);
                }
                $stmt->close();
            } else {
                $stmt = $conn->prepare("SELECT * FROM categorias ORDER BY nome ASC");
                $stmt->execute();
                $result = $stmt->get_result();
                $categorias = [];
                
                while ($row = $result->fetch_assoc()) {
                    $categorias[] = $row;
                }
                
                $stmt->close();
                sendResponse(true, $categorias, 'Categorias listadas com sucesso');
            }
            break;
            
        case 'POST':
            // Criar nova categoria
            $data = getRequestBody();
            
            if (!isset($data['nome']) || empty(trim($data['nome']))) {
                sendResponse(false, null, 'Nome da categoria é obrigatório', 400);
            }
            
            $nome = trim($data['nome']);
            $icone = isset($data['icone']) ? trim($data['icone']) : '';
            
            // Verificar se já existe
            $checkStmt = $conn->prepare("SELECT id FROM categorias WHERE nome = ?");
            $checkStmt->bind_param("s", $nome);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                $checkStmt->close();
                sendResponse(false, null, 'Categoria com este nome já existe', 400);
            }
            $checkStmt->close();
            
            $stmt = $conn->prepare("INSERT INTO categorias (nome, icone) VALUES (?, ?)");
            $stmt->bind_param("ss", $nome, $icone);
            
            if ($stmt->execute()) {
                $id = $conn->insert_id;
                sendResponse(true, ['id' => $id], 'Categoria criada com sucesso', 201);
            } else {
                sendResponse(false, null, 'Erro ao criar categoria: ' . $conn->error, 500);
            }
            
            $stmt->close();
            break;
            
        case 'PUT':
            // Atualizar categoria
            $data = getRequestBody();
            
            if (!isset($data['id'])) {
                sendResponse(false, null, 'ID da categoria é obrigatório', 400);
            }
            
            $id = intval($data['id']);
            $nome = isset($data['nome']) ? trim($data['nome']) : null;
            $icone = isset($data['icone']) ? trim($data['icone']) : null;
            
            // Construir query dinamicamente
            $fields = [];
            $params = [];
            $types = '';
            
            if ($nome !== null) {
                // Verificar se nome já existe em outra categoria
                $checkStmt = $conn->prepare("SELECT id FROM categorias WHERE nome = ? AND id != ?");
                $checkStmt->bind_param("si", $nome, $id);
                $checkStmt->execute();
                if ($checkStmt->get_result()->num_rows > 0) {
                    $checkStmt->close();
                    sendResponse(false, null, 'Categoria com este nome já existe', 400);
                }
                $checkStmt->close();
                
                $fields[] = "nome = ?";
                $params[] = $nome;
                $types .= "s";
            }
            if ($icone !== null) {
                $fields[] = "icone = ?";
                $params[] = $icone;
                $types .= "s";
            }
            
            if (empty($fields)) {
                sendResponse(false, null, 'Nenhum campo para atualizar', 400);
            }
            
            $types .= "i";
            $params[] = $id;
            
            $sql = "UPDATE categorias SET " . implode(", ", $fields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                sendResponse(true, null, 'Categoria atualizada com sucesso');
            } else {
                sendResponse(false, null, 'Erro ao atualizar categoria: ' . $conn->error, 500);
            }
            
            $stmt->close();
            break;
            
        case 'DELETE':
            // Deletar categoria
            if (!isset($_GET['id'])) {
                sendResponse(false, null, 'ID da categoria é obrigatório', 400);
            }
            
            $id = intval($_GET['id']);
            
            // Verificar se há produtos usando esta categoria
            $checkStmt = $conn->prepare("SELECT COUNT(*) as total FROM produtos WHERE categoria_id = ?");
            $checkStmt->bind_param("i", $id);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            $row = $result->fetch_assoc();
            $checkStmt->close();
            
            if ($row['total'] > 0) {
                sendResponse(false, null, 'Não é possível excluir categoria que possui produtos associados', 400);
            }
            
            $stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    sendResponse(true, null, 'Categoria deletada com sucesso');
                } else {
                    sendResponse(false, null, 'Categoria não encontrada', 404);
                }
            } else {
                sendResponse(false, null, 'Erro ao deletar categoria: ' . $conn->error, 500);
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

