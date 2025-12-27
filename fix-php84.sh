#!/bin/bash
# Ajustar PHP 8.4-FPM (que está realmente rodando)

PHP_INI="/etc/php/8.4/fpm/php.ini"

if [ -f "$PHP_INI" ]; then
    echo "=== Ajustando PHP 8.4-FPM ==="
    echo ""
    
    # Backup
    cp "$PHP_INI" "$PHP_INI.backup.$(date +%Y%m%d_%H%M%S)"
    echo "✓ Backup criado"
    
    # Ajustar valores
    sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' "$PHP_INI"
    sed -i 's/^post_max_size = .*/post_max_size = 50M/' "$PHP_INI"
    sed -i 's/^max_execution_time = .*/max_execution_time = 300/' "$PHP_INI"
    sed -i 's/^max_input_time = .*/max_input_time = 300/' "$PHP_INI"
    sed -i 's/^memory_limit = .*/memory_limit = 256M/' "$PHP_INI"
    
    echo "✓ Valores ajustados"
    echo ""
    
    # Verificar valores
    echo "Valores atuais no PHP 8.4-FPM:"
    grep "^upload_max_filesize" "$PHP_INI"
    grep "^post_max_size" "$PHP_INI"
    grep "^max_execution_time" "$PHP_INI"
    echo ""
    
    # Reiniciar PHP 8.4-FPM
    echo "Reiniciando php8.4-fpm..."
    systemctl restart php8.4-fpm
    echo "✓ PHP 8.4-FPM reiniciado"
    echo ""
    
    echo "=== Configurações aplicadas! ==="
    echo ""
    echo "Acesse https://longdev.com.br/test-phpinfo.php para verificar"
    echo "Procure por: upload_max_filesize e post_max_size (devem estar em 50M)"
else
    echo "Erro: Arquivo $PHP_INI não encontrado"
    exit 1
fi

