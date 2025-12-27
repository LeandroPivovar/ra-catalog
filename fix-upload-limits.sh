#!/bin/bash
# Script para ajustar limites de upload no Nginx e PHP

echo "=== Ajustando limites de upload ==="

# 1. Verificar e atualizar Nginx
echo ""
echo "1. Verificando configuração do Nginx..."
NGINX_CONFIG="/etc/nginx/sites-available/longdev.com.br"

if [ -f "$NGINX_CONFIG" ]; then
    # Verificar se client_max_body_size já está configurado
    if grep -q "client_max_body_size" "$NGINX_CONFIG"; then
        echo "   ✓ client_max_body_size já configurado"
        grep "client_max_body_size" "$NGINX_CONFIG"
    else
        echo "   ⚠ client_max_body_size não encontrado, adicionando..."
        # Adicionar após a linha do root
        sed -i '/root \/var\/www\/ra-catalog;/a\    client_max_body_size 50M;\n    client_body_buffer_size 50M;' "$NGINX_CONFIG"
        echo "   ✓ Adicionado client_max_body_size 50M"
    fi
    
    # Testar configuração
    echo ""
    echo "   Testando configuração do Nginx..."
    if nginx -t 2>&1 | grep -q "successful"; then
        echo "   ✓ Configuração do Nginx válida"
        echo "   Recarregando Nginx..."
        systemctl reload nginx
        echo "   ✓ Nginx recarregado"
    else
        echo "   ✗ Erro na configuração do Nginx:"
        nginx -t
        exit 1
    fi
else
    echo "   ✗ Arquivo de configuração do Nginx não encontrado: $NGINX_CONFIG"
fi

# 2. Verificar e atualizar PHP
echo ""
echo "2. Verificando configuração do PHP..."

# Encontrar arquivo php.ini do PHP-FPM
PHP_INI=$(php --ini | grep "Loaded Configuration File" | awk '{print $4}' | sed 's/cli/fpm/')
PHP_FPM_INI="/etc/php/8.1/fpm/php.ini"

# Tentar encontrar o php.ini do FPM
if [ ! -f "$PHP_FPM_INI" ]; then
    # Tentar outras versões comuns
    for version in 8.2 8.0 7.4; do
        if [ -f "/etc/php/$version/fpm/php.ini" ]; then
            PHP_FPM_INI="/etc/php/$version/fpm/php.ini"
            break
        fi
    done
fi

if [ -f "$PHP_FPM_INI" ]; then
    echo "   Arquivo PHP.ini encontrado: $PHP_FPM_INI"
    
    # Backup
    cp "$PHP_FPM_INI" "$PHP_FPM_INI.backup.$(date +%Y%m%d_%H%M%S)"
    echo "   ✓ Backup criado"
    
    # Ajustar upload_max_filesize
    if grep -q "^upload_max_filesize" "$PHP_FPM_INI"; then
        sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_FPM_INI"
        echo "   ✓ upload_max_filesize ajustado para 50M"
    else
        echo "upload_max_filesize = 50M" >> "$PHP_FPM_INI"
        echo "   ✓ upload_max_filesize adicionado (50M)"
    fi
    
    # Ajustar post_max_size (DEVE ser maior ou igual a upload_max_filesize)
    if grep -q "^post_max_size" "$PHP_FPM_INI"; then
        sed -i 's/^post_max_size = .*/post_max_size = 50M/' "$PHP_FPM_INI"
        echo "   ✓ post_max_size ajustado para 50M"
    else
        echo "post_max_size = 50M" >> "$PHP_FPM_INI"
        echo "   ✓ post_max_size adicionado (50M)"
    fi
    
    # Ajustar max_execution_time
    if grep -q "^max_execution_time" "$PHP_FPM_INI"; then
        sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_FPM_INI"
        echo "   ✓ max_execution_time ajustado para 300"
    else
        echo "max_execution_time = 300" >> "$PHP_FPM_INI"
        echo "   ✓ max_execution_time adicionado (300)"
    fi
    
    # Ajustar max_input_time
    if grep -q "^max_input_time" "$PHP_FPM_INI"; then
        sed -i 's/^max_input_time = .*/max_input_time = 300/' "$PHP_FPM_INI"
        echo "   ✓ max_input_time ajustado para 300"
    else
        echo "max_input_time = 300" >> "$PHP_FPM_INI"
        echo "   ✓ max_input_time adicionado (300)"
    fi
    
    # Ajustar memory_limit
    if grep -q "^memory_limit" "$PHP_FPM_INI"; then
        sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_FPM_INI"
        echo "   ✓ memory_limit ajustado para 256M"
    else
        echo "memory_limit = 256M" >> "$PHP_FPM_INI"
        echo "   ✓ memory_limit adicionado (256M)"
    fi
    
    echo ""
    echo "   Reiniciando PHP-FPM..."
    # Detectar versão do PHP-FPM
    if systemctl is-active --quiet php8.1-fpm; then
        systemctl restart php8.1-fpm
        echo "   ✓ PHP 8.1-FPM reiniciado"
    elif systemctl is-active --quiet php8.2-fpm; then
        systemctl restart php8.2-fpm
        echo "   ✓ PHP 8.2-FPM reiniciado"
    elif systemctl is-active --quiet php8.0-fpm; then
        systemctl restart php8.0-fpm
        echo "   ✓ PHP 8.0-FPM reiniciado"
    else
        echo "   ⚠ Não foi possível detectar a versão do PHP-FPM. Reinicie manualmente:"
        echo "      sudo systemctl restart php8.1-fpm"
    fi
    
else
    echo "   ✗ Arquivo php.ini do PHP-FPM não encontrado"
    echo "   Procure manualmente em: /etc/php/*/fpm/php.ini"
fi

echo ""
echo "=== Verificando configurações atuais ==="
echo ""
echo "Nginx client_max_body_size:"
grep "client_max_body_size" /etc/nginx/sites-available/longdev.com.br 2>/dev/null || echo "  Não encontrado"
echo ""
echo "PHP (via phpinfo):"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
php -r "echo 'max_execution_time: ' . ini_get('max_execution_time') . PHP_EOL;"
php -r "echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;"

echo ""
echo "=== Concluído! ==="
echo ""
echo "IMPORTANTE: As configurações do PHP-FPM podem não aparecer imediatamente"
echo "no php -r. Para verificar as configurações do PHP-FPM, crie um arquivo"
echo "phpinfo.php e acesse via navegador."

