# Ajustar Limites de Upload para Arquivos GLB

O erro "413 Request Entity Too Large" ocorre porque o Nginx e/ou PHP têm limites de tamanho de upload muito baixos. Siga os passos abaixo para permitir uploads de arquivos GLB maiores (até 50MB).

## 1. Atualizar Configuração do Nginx

O arquivo `nginx-config.conf` já foi atualizado com:
- `client_max_body_size 50M;`
- `client_body_buffer_size 50M;`
- `fastcgi_read_timeout 300;`
- `fastcgi_send_timeout 300;`

**Aplicar as mudanças:**

```bash
# Copiar o arquivo atualizado para o servidor
sudo cp nginx-config.conf /etc/nginx/sites-available/longdev.com.br

# Testar a configuração
sudo nginx -t

# Recarregar o Nginx
sudo systemctl reload nginx
```

## 2. Ajustar Limites do PHP

Edite o arquivo `php.ini` do PHP-FPM:

```bash
# Encontrar o arquivo php.ini do PHP-FPM
php --ini | grep "Loaded Configuration File"

# Ou para PHP-FPM especificamente:
sudo nano /etc/php/8.1/fpm/php.ini  # Ajuste a versão (8.1, 8.2, etc.)
```

**Altere as seguintes linhas:**

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

**Recarregar PHP-FPM:**

```bash
sudo systemctl restart php8.1-fpm  # Ajuste a versão
```

## 3. Verificar Configurações

Crie um arquivo `test-upload-limits.php` no servidor:

```php
<?php
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
?>
```

Acesse via navegador: `https://longdev.com.br/test-upload-limits.php`

## 4. Verificar Logs

Se ainda houver problemas, verifique os logs:

```bash
# Logs do Nginx
sudo tail -f /var/log/nginx/longdev_error.log

# Logs do PHP-FPM
sudo tail -f /var/log/php8.1-fpm.log  # Ajuste a versão
```

## Resumo dos Valores Recomendados

- **Nginx:** `client_max_body_size 50M`
- **PHP:** `upload_max_filesize = 50M` e `post_max_size = 50M`
- **Timeouts:** 300 segundos (5 minutos) para uploads grandes

