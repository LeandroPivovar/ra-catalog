#!/bin/bash
# Script para encontrar e ajustar configurações do PHP-FPM

echo "=== Encontrando versão do PHP e PHP-FPM ==="
echo ""

# Verificar versão do PHP
echo "1. Versão do PHP instalada:"
php -v | head -1
echo ""

# Encontrar arquivo php.ini do CLI
echo "2. Arquivo php.ini do CLI:"
php --ini | grep "Loaded Configuration File"
echo ""

# Encontrar todos os arquivos php.ini do FPM
echo "3. Procurando arquivos php.ini do PHP-FPM:"
find /etc/php -name "php.ini" -path "*/fpm/*" 2>/dev/null
echo ""

# Verificar serviços PHP-FPM ativos
echo "4. Serviços PHP-FPM disponíveis:"
systemctl list-units --type=service | grep php.*fpm
echo ""

# Verificar qual PHP-FPM está rodando
echo "5. Processos PHP-FPM em execução:"
ps aux | grep php-fpm | grep -v grep | head -3
echo ""

# Tentar encontrar a versão do PHP-FPM pelo socket
echo "6. Verificando socket do PHP-FPM:"
if [ -S /var/run/php/php-fpm.sock ]; then
    echo "   Socket encontrado: /var/run/php/php-fpm.sock"
    # Tentar identificar versão pelo socket
    ls -la /var/run/php/ | grep php-fpm
else
    echo "   Socket não encontrado em /var/run/php/php-fpm.sock"
    echo "   Procurando outros sockets:"
    find /var/run/php -name "*fpm*.sock" 2>/dev/null
fi
echo ""

# Verificar configuração do Nginx para ver qual PHP-FPM está sendo usado
echo "7. Verificando configuração do Nginx:"
grep -r "fastcgi_pass" /etc/nginx/sites-available/longdev.com.br | grep -v "^#"
echo ""

echo "=== Aplicando correções ==="
echo ""

# Tentar diferentes versões comuns do PHP
for version in 8.3 8.2 8.1 8.0 7.4; do
    PHP_INI="/etc/php/$version/fpm/php.ini"
    if [ -f "$PHP_INI" ]; then
        echo "✓ Arquivo encontrado: $PHP_INI"
        
        # Backup
        cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"
        echo "  ✓ Backup criado"
        
        # Ajustar valores
        sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI"
        sed -i 's/^post_max_size = .*/post_max_size = 50M/' "$PHP_INI"
        sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
        sed -i 's/^max_input_time = .*/max_input_time = 300/' "$PHP_INI"
        sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
        
        echo "  ✓ Valores ajustados"
        
        # Verificar se as alterações foram aplicadas
        echo ""
        echo "  Valores atuais:"
        grep "^upload_max_filesize" "$PHP_INI"
        grep "^post_max_size" "$PHP_INI"
        grep "^max_execution_time" "$PHP_INI"
        
        # Reiniciar PHP-FPM
        if systemctl is-active --quiet "php$version-fpm" 2>/dev/null; then
            echo ""
            echo "  Reiniciando php$version-fpm..."
            systemctl restart "php$version-fpm"
            echo "  ✓ PHP $version-FPM reiniciado"
        else
            echo ""
            echo "  ⚠ Serviço php$version-fpm não está ativo"
        fi
        
        break
    fi
done

echo ""
echo "=== Verificando configurações finais ==="
echo ""

# Verificar Nginx
echo "Nginx client_max_body_size:"
grep "client_max_body_size" /etc/nginx/sites-available/longdev.com.br
echo ""

# Verificar PHP via phpinfo
echo "PHP (via CLI - pode diferir do FPM):"
php -r "echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;"
php -r "echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;"
echo ""

echo "=== IMPORTANTE ==="
echo "Para verificar as configurações do PHP-FPM (não CLI), acesse:"
echo "https://longdev.com.br/test-phpinfo.php"
echo ""
echo "Procure por: upload_max_filesize e post_max_size"
echo "Ambos devem estar em 50M"

