#!/bin/bash
# Script para criar estrutura de pastas de upload e configurar permissões

# Criar estrutura de pastas
mkdir -p /var/www/ra-catalog/uploads/thumbnails
mkdir -p /var/www/ra-catalog/uploads/models3d

# Definir proprietário (ajuste conforme seu servidor web)
# Para Apache/Nginx geralmente é www-data
chown -R www-data:www-data /var/www/ra-catalog/uploads

# Definir permissões
# 755 = proprietário pode ler/escrever/executar, grupo e outros podem ler/executar
chmod -R 755 /var/www/ra-catalog/uploads

# Garantir que o PHP pode escrever
chmod -R 775 /var/www/ra-catalog/uploads

echo "Estrutura de uploads criada com sucesso!"
echo "Pastas criadas:"
echo "  - /var/www/ra-catalog/uploads/thumbnails"
echo "  - /var/www/ra-catalog/uploads/models3d"
echo ""
echo "Permissões configuradas:"
ls -la /var/www/ra-catalog/uploads


