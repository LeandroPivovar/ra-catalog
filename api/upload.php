<?php
/**
 * API para upload de arquivos (imagens e modelos 3D)
 */

require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Tratar requisições OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
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

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, null, 'Método não permitido', 405);
}

// Verificar se há arquivo
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    sendResponse(false, null, 'Nenhum arquivo enviado ou erro no upload', 400);
}

$file = $_FILES['file'];
$type = isset($_POST['type']) ? $_POST['type'] : 'thumbnail'; // 'thumbnail' ou 'model3d'

// Validar tipo
if (!in_array($type, ['thumbnail', 'model3d'])) {
    sendResponse(false, null, 'Tipo inválido. Use "thumbnail" ou "model3d"', 400);
}

// Criar diretórios se não existirem
$uploadDir = '../uploads/';
$thumbnailDir = $uploadDir . 'thumbnails/';
$model3dDir = $uploadDir . 'models3d/';

if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
if (!file_exists($thumbnailDir)) {
    mkdir($thumbnailDir, 0755, true);
}
if (!file_exists($model3dDir)) {
    mkdir($model3dDir, 0755, true);
}

// Validar extensões
$allowedExtensions = [];
$maxSize = 0;

if ($type === 'thumbnail') {
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $targetDir = $thumbnailDir;
} else { // model3d
    $allowedExtensions = ['glb', 'gltf', 'obj', 'fbx'];
    $maxSize = 50 * 1024 * 1024; // 50MB
    $targetDir = $model3dDir;
}

// Validar tamanho
if ($file['size'] > $maxSize) {
    $maxSizeMB = round($maxSize / (1024 * 1024));
    sendResponse(false, null, "Arquivo muito grande. Tamanho máximo: {$maxSizeMB}MB", 400);
}

// Validar extensão
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    $allowed = implode(', ', $allowedExtensions);
    sendResponse(false, null, "Extensão não permitida. Use: {$allowed}", 400);
}

// Gerar nome único
$fileName = uniqid() . '_' . time() . '.' . $fileExtension;
$targetPath = $targetDir . $fileName;

// Mover arquivo
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    sendResponse(false, null, 'Erro ao salvar arquivo', 500);
}

// Retornar caminho relativo (sem ../)
$relativePath = 'uploads/' . ($type === 'thumbnail' ? 'thumbnails/' : 'models3d/') . $fileName;

sendResponse(true, [
    'path' => $relativePath,
    'filename' => $fileName,
    'size' => $file['size'],
    'type' => $type
], 'Arquivo enviado com sucesso', 201);


