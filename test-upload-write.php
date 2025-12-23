<?php
/**
 * Script de teste para verificar se o PHP consegue escrever na pasta uploads
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Teste de Escrita na Pasta Uploads ===\n\n";

$baseDir = __DIR__ . '/uploads';
$testFile = $baseDir . '/teste.txt';

// Verificar se a pasta existe
echo "1. Verificando se a pasta existe...\n";
if (!file_exists($baseDir)) {
    echo "❌ Pasta 'uploads' não existe. Tentando criar...\n";
    if (mkdir($baseDir, 0755, true)) {
        echo "✅ Pasta criada com sucesso!\n";
    } else {
        echo "❌ Erro ao criar pasta: " . error_get_last()['message'] . "\n";
        exit(1);
    }
} else {
    echo "✅ Pasta 'uploads' existe\n";
}

// Verificar permissões da pasta
echo "\n2. Verificando permissões da pasta...\n";
$perms = substr(sprintf('%o', fileperms($baseDir)), -4);
echo "   Permissões atuais: $perms\n";
if (is_writable($baseDir)) {
    echo "✅ Pasta é gravável\n";
} else {
    echo "❌ Pasta NÃO é gravável\n";
}

// Verificar usuário do PHP
echo "\n3. Informações do processo PHP:\n";
echo "   Usuário: " . get_current_user() . "\n";
echo "   UID: " . getmyuid() . "\n";
echo "   GID: " . getmygid() . "\n";

// Tentar escrever arquivo
echo "\n4. Tentando escrever arquivo de teste...\n";
$content = "Teste de escrita em " . date('Y-m-d H:i:s') . "\n";
$result = @file_put_contents($testFile, $content);

if ($result === false) {
    echo "❌ ERRO: Não foi possível escrever em $testFile\n";
    $error = error_get_last();
    if ($error) {
        echo "   Erro: " . $error['message'] . "\n";
    }
    echo "\n   Possíveis soluções:\n";
    echo "   - Verificar permissões: chmod 775 uploads/\n";
    echo "   - Verificar proprietário: chown www-data:www-data uploads/\n";
    echo "   - Verificar se o usuário do PHP tem permissão\n";
} else {
    echo "✅ Arquivo escrito com sucesso!\n";
    echo "   Tamanho: $result bytes\n";
    
    // Tentar ler o arquivo
    echo "\n5. Tentando ler o arquivo...\n";
    $readContent = @file_get_contents($testFile);
    if ($readContent !== false) {
        echo "✅ Arquivo lido com sucesso!\n";
        echo "   Conteúdo: " . trim($readContent) . "\n";
    } else {
        echo "❌ Erro ao ler arquivo\n";
    }
    
    // Limpar arquivo de teste
    echo "\n6. Removendo arquivo de teste...\n";
    if (unlink($testFile)) {
        echo "✅ Arquivo removido\n";
    } else {
        echo "⚠️  Não foi possível remover o arquivo (pode ser removido manualmente)\n";
    }
}

// Verificar subpastas
echo "\n7. Verificando subpastas...\n";
$subdirs = ['thumbnails', 'models3d'];
foreach ($subdirs as $subdir) {
    $path = $baseDir . '/' . $subdir;
    if (!file_exists($path)) {
        echo "   Criando $subdir...\n";
        if (mkdir($path, 0755, true)) {
            echo "   ✅ $subdir criada\n";
        } else {
            echo "   ❌ Erro ao criar $subdir\n";
        }
    } else {
        echo "   ✅ $subdir existe\n";
    }
}

// Verificar $_FILES
echo "\n8. Verificando configuração de upload do PHP:\n";
echo "   file_uploads: " . (ini_get('file_uploads') ? 'Habilitado' : 'Desabilitado') . "\n";
echo "   upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "   post_max_size: " . ini_get('post_max_size') . "\n";
echo "   upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: 'Padrão do sistema') . "\n";

echo "\n=== Teste concluído ===\n";

