#!/bin/bash

echo "=== Verificando PHP-FPM ==="
echo ""

# Verificar se PHP-FPM está instalado
if command -v php-fpm &> /dev/null; then
    echo "✅ PHP-FPM está instalado"
else
    echo "❌ PHP-FPM não encontrado"
    echo "   Instale com: sudo apt install php-fpm"
    exit 1
fi

# Verificar versão do PHP
PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo "📦 Versão do PHP: $PHP_VERSION"

# Verificar sockets disponíveis
echo ""
echo "=== Sockets PHP-FPM disponíveis ==="
if [ -d "/var/run/php" ]; then
    ls -la /var/run/php/ | grep -E "\.sock$"
    
    # Tentar encontrar o socket mais recente
    LATEST_SOCKET=$(ls -t /var/run/php/*.sock 2>/dev/null | head -1)
    if [ ! -z "$LATEST_SOCKET" ]; then
        echo ""
        echo "💡 Socket recomendado: $LATEST_SOCKET"
    fi
else
    echo "❌ Pasta /var/run/php não encontrada"
fi

# Verificar processos PHP-FPM
echo ""
echo "=== Processos PHP-FPM rodando ==="
if pgrep -x "php-fpm" > /dev/null; then
    echo "✅ PHP-FPM está rodando"
    ps aux | grep php-fpm | grep -v grep | head -3
else
    echo "❌ PHP-FPM não está rodando"
    echo "   Inicie com: sudo systemctl start php${PHP_VERSION//./}-fpm"
fi

# Verificar configuração do Nginx
echo ""
echo "=== Verificando Nginx ==="
if command -v nginx &> /dev/null; then
    echo "✅ Nginx está instalado"
    
    # Verificar sintaxe
    if sudo nginx -t 2>&1 | grep -q "successful"; then
        echo "✅ Configuração do Nginx está OK"
    else
        echo "❌ Erro na configuração do Nginx"
        sudo nginx -t
    fi
else
    echo "❌ Nginx não encontrado"
fi

# Verificar se a pasta existe
echo ""
echo "=== Verificando pasta do projeto ==="
if [ -d "/var/www/ra-catalog" ]; then
    echo "✅ Pasta /var/www/ra-catalog existe"
    ls -la /var/www/ra-catalog | head -5
else
    echo "❌ Pasta /var/www/ra-catalog não encontrada"
    echo "   Crie a pasta ou ajuste o caminho no Nginx"
fi

echo ""
echo "=== Configuração recomendada para nginx-config.conf ==="
if [ ! -z "$LATEST_SOCKET" ]; then
    SOCKET_NAME=$(basename $LATEST_SOCKET)
    echo "fastcgi_pass unix:/var/run/php/$SOCKET_NAME;"
else
    echo "fastcgi_pass 127.0.0.1:9000;  # Usar TCP como alternativa"
fi


