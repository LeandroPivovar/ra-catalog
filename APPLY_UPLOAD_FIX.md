# Corrigir Limites de Upload - Instruções

## Problema Identificado

Os logs mostram:
- **Nginx**: `client intended to send too large body: 12551247 bytes` (arquivo de 12MB rejeitado)
- **PHP**: `POST Content-Length of 12551247 bytes exceeds the limit of 8388608 bytes` (limite de 8MB)
- **Resultado**: `_FILES` fica vazio porque o PHP rejeita antes de processar

## Solução

Execute os seguintes comandos **no servidor** (SSH):

### 1. Aplicar configuração do Nginx

```bash
# Copiar configuração atualizada
sudo cp /var/www/ra-catalog/nginx-config.conf /etc/nginx/sites-available/longdev.com.br

# OU editar manualmente e adicionar estas linhas após "root /var/www/ra-catalog;":
# client_max_body_size 50M;
# client_body_buffer_size 50M;

# Testar configuração
sudo nginx -t

# Recarregar Nginx
sudo systemctl reload nginx
```

### 2. Ajustar PHP.ini do PHP-FPM

```bash
# Encontrar o arquivo php.ini do PHP-FPM
sudo find /etc/php -name "php.ini" -path "*/fpm/*"

# Editar o arquivo (geralmente /etc/php/8.1/fpm/php.ini)
sudo nano /etc/php/8.1/fpm/php.ini
```

**Altere estas linhas:**

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

**OU use sed para alterar automaticamente:**

```bash
# Fazer backup
sudo cp /etc/php/8.1/fpm/php.ini /etc/php/8.1/fpm/php.ini.backup

# Ajustar valores
sudo sed -i 's/^upload_max_filesize = .*/upload_max_filesize = 50M/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/^post_max_size = .*/post_max_size = 50M/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/^max_execution_time = .*/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/^max_input_time = .*/max_input_time = 300/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/^memory_limit = .*/memory_limit = 256M/' /etc/php/8.1/fpm/php.ini

# Reiniciar PHP-FPM
sudo systemctl restart php8.1-fpm
```

### 3. Verificar configurações

```bash
# Verificar Nginx
grep "client_max_body_size" /etc/nginx/sites-available/longdev.com.br

# Verificar PHP (criar arquivo de teste)
echo "<?php phpinfo(); ?>" | sudo tee /var/www/ra-catalog/test-phpinfo.php
# Acesse: https://longdev.com.br/test-phpinfo.php
# Procure por: upload_max_filesize, post_max_size
```

### 4. Testar upload novamente

Após aplicar as mudanças, tente fazer upload do arquivo GLB novamente.

## Script Automatizado

Se preferir, use o script `fix-upload-limits.sh`:

```bash
# No servidor
cd /var/www/ra-catalog
sudo bash fix-upload-limits.sh
```

## Valores Recomendados

- **Nginx**: `client_max_body_size 50M`
- **PHP upload_max_filesize**: `50M`
- **PHP post_max_size**: `50M` (deve ser >= upload_max_filesize)
- **PHP max_execution_time**: `300` (5 minutos)
- **PHP max_input_time**: `300` (5 minutos)
- **PHP memory_limit**: `256M`

