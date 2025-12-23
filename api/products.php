<?php
/**
 * API REST para gerenciamento de produtos
 */

require_once '../config/database.php';

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Sempre retornar JSON (mesmo com uploads)
header('Content-Type: application/json; charset=utf-8');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();

if (!$conn) {
    sendResponse(false, null, 'Erro de conexão com o banco de dados. Verifique se o banco foi instalado.', 500);
}

// Função para enviar resposta JSON
function sendResponse($success, $data = null, $message = '', $statusCode = 200) {
    http_response_code($statusCode);
    $response = ['success' => $success];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Função para processar upload de arquivo
function processFileUpload($fileKey, $type) {
    if (!isset($_FILES[$fileKey]) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file = $_FILES[$fileKey];
    
    // Criar diretórios
    $uploadDir = '../uploads/';
    $thumbnailDir = $uploadDir . 'thumbnails/';
    $model3dDir = $uploadDir . 'models3d/';
    
    if (!file_exists($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!file_exists($thumbnailDir)) mkdir($thumbnailDir, 0755, true);
    if (!file_exists($model3dDir)) mkdir($model3dDir, 0755, true);
    
    // Validar extensões e tamanho
    if ($type === 'thumbnail') {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $targetDir = $thumbnailDir;
    } else { // model3d
        $allowedExtensions = ['glb', 'gltf', 'obj', 'fbx'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        $targetDir = $model3dDir;
    }
    
    if ($file['size'] > $maxSize) {
        return null;
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, $allowedExtensions)) {
        return null;
    }
    
    // Gerar nome único e mover arquivo
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $targetDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/' . ($type === 'thumbnail' ? 'thumbnails/' : 'models3d/') . $fileName;
    }
    
    return null;
}

// Função para obter dados do body da requisição
function getRequestBody() {
    // Se for multipart/form-data, usar $_POST
    if (!empty($_POST)) {
        return $_POST;
    }
    
    // Caso contrário, tentar JSON
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
            
            // Processar uploads de arquivos
            $imagem_path = processFileUpload('thumbnail', 'thumbnail');
            if (!$imagem_path && isset($data['imagem_url'])) {
                $imagem_path = $data['imagem_url']; // Fallback para URL
            }
            
            $modelo_3d_path = processFileUpload('model3d', 'model3d');
            if (!$modelo_3d_path && isset($data['modelo_3d_url'])) {
                $modelo_3d_path = $data['modelo_3d_url']; // Fallback para URL
            }
            
            $stmt = $conn->prepare("INSERT INTO produtos (nome, categoria, descricao, imagem_url, modelo_3d_url) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nome, $categoria, $descricao, $imagem_path, $modelo_3d_path);
            
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
            
            // Buscar produto atual para manter arquivos existentes se não houver novos uploads
            $stmt = $conn->prepare("SELECT imagem_url, modelo_3d_url FROM produtos WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $currentProduct = $result->fetch_assoc();
            $stmt->close();
            
            $nome = isset($data['nome']) ? $data['nome'] : null;
            $categoria = isset($data['categoria']) ? $data['categoria'] : null;
            $descricao = isset($data['descricao']) ? $data['descricao'] : null;
            
            // Processar uploads de arquivos (se houver)
            $imagem_path = processFileUpload('thumbnail', 'thumbnail');
            if (!$imagem_path) {
                $imagem_path = isset($data['imagem_url']) ? $data['imagem_url'] : $currentProduct['imagem_url'];
            }
            
            $modelo_3d_path = processFileUpload('model3d', 'model3d');
            if (!$modelo_3d_path) {
                $modelo_3d_path = isset($data['modelo_3d_url']) ? $data['modelo_3d_url'] : $currentProduct['modelo_3d_url'];
            }
            
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
            if ($imagem_path !== null) {
                $fields[] = "imagem_url = ?";
                $params[] = $imagem_path;
                $types .= "s";
            }
            if ($modelo_3d_path !== null) {
                $fields[] = "modelo_3d_url = ?";
                $params[] = $modelo_3d_path;
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
    error_log("Erro na API de produtos: " . $e->getMessage());
    sendResponse(false, null, 'Erro: ' . $e->getMessage(), 500);
} finally {
    if ($conn) {
        $conn->close();
    }
}

