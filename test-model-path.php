<?php
/**
 * Script para testar e verificar caminhos de modelos 3D no banco de dados
 */

require_once 'config/database.php';

header('Content-Type: text/html; charset=utf-8');

$conn = getConnection();

if (!$conn) {
    die("Erro de conexão com o banco de dados");
}

echo "<h1>Verificação de Modelos 3D</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #2196F3; color: white; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .path { font-family: monospace; }
    .exists { color: green; }
    .missing { color: red; }
</style>";

// Buscar todos os produtos com modelo 3D
$sql = "SELECT id, nome, modelo_3d_url FROM produtos WHERE modelo_3d_url IS NOT NULL AND modelo_3d_url != ''";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h2>Produtos com Modelo 3D:</h2>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Nome</th><th>Caminho no Banco</th><th>Arquivo Existe?</th><th>URL Completa</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $path = $row['modelo_3d_url'];
        $fullPath = '../' . $path;
        $exists = file_exists($fullPath);
        
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['nome']}</td>";
        echo "<td class='path'>{$path}</td>";
        echo "<td class='" . ($exists ? 'exists' : 'missing') . "'>" . ($exists ? '✓ Sim' : '✗ Não') . "</td>";
        echo "<td class='path'>" . ($exists ? $fullPath : 'Arquivo não encontrado') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
} else {
    echo "<p>Nenhum produto com modelo 3D encontrado.</p>";
}

// Verificar arquivos na pasta
echo "<h2>Arquivos na pasta uploads/models3d/:</h2>";
$modelsDir = '../uploads/models3d/';
if (is_dir($modelsDir)) {
    $files = scandir($modelsDir);
    $files = array_filter($files, function($file) {
        return $file !== '.' && $file !== '..';
    });
    
    if (count($files) > 0) {
        echo "<ul>";
        foreach ($files as $file) {
            $filePath = $modelsDir . $file;
            $size = filesize($filePath);
            $sizeMB = round($size / (1024 * 1024), 2);
            echo "<li><strong>{$file}</strong> - {$sizeMB} MB</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Nenhum arquivo encontrado na pasta.</p>";
    }
} else {
    echo "<p>Pasta não encontrada: {$modelsDir}</p>";
}

$conn->close();
?>

