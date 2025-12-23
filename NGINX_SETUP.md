# Configuração Nginx para iFood RA

## 📋 Alterações Realizadas

A configuração foi atualizada para:
- ✅ Pasta raiz: `/var/www/ra-catalog`
- ✅ Suporte a PHP para API e admin
- ✅ Proteção de arquivos de configuração
- ✅ CORS habilitado para API
- ✅ Mantém SSL/HTTPS do Certbot
- ✅ Mantém otimizações de cache e compressão

## 🚀 Passos para Aplicar

### 1. Verificar versão do PHP-FPM

Primeiro, descubra qual socket do PHP-FPM você está usando:

```bash
# Verificar sockets disponíveis
ls -la /var/run/php/

# Ou verificar processo
ps aux | grep php-fpm
```

**Opções comuns:**
- `/var/run/php/php8.1-fpm.sock` (PHP 8.1)
- `/var/run/php/php8.2-fpm.sock` (PHP 8.2)
- `/var/run/php/php-fpm.sock` (padrão)
- `127.0.0.1:9000` (TCP, alternativa)

### 2. Editar a configuração

```bash
sudo nano /etc/nginx/sites-available/longdev.com.br
```

**IMPORTANTE:** Ajuste a linha `fastcgi_pass` conforme sua versão PHP:

```nginx
# Para PHP 8.1
fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;

# Para PHP 8.2
fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;

# Ou se usar TCP
fastcgi_pass 127.0.0.1:9000;
```

### 3. Copiar configuração

Copie o conteúdo do arquivo `nginx-config.conf` para o arquivo do Nginx:

```bash
# Backup da configuração atual
sudo cp /etc/nginx/sites-available/longdev.com.br /etc/nginx/sites-available/longdev.com.br.backup

# Copiar nova configuração (ajuste o caminho)
sudo cp nginx-config.conf /etc/nginx/sites-available/longdev.com.br
```

### 4. Verificar sintaxe

```bash
sudo nginx -t
```

Se aparecer "syntax is ok", continue. Caso contrário, corrija os erros.

### 5. Recarregar Nginx

```bash
sudo systemctl reload nginx
# ou
sudo service nginx reload
```

### 6. Verificar permissões

Certifique-se de que o Nginx tem permissão para ler os arquivos:

```bash
# Verificar proprietário da pasta
ls -la /var/www/ra-catalog

# Se necessário, ajustar permissões
sudo chown -R www-data:www-data /var/www/ra-catalog
sudo chmod -R 755 /var/www/ra-catalog
```

### 7. Testar

- Frontend: `https://longdev.com.br`
- API: `https://longdev.com.br/api/products.php`
- Admin: `https://longdev.com.br/admin/`
- Install: `https://longdev.com.br/install.php` (apenas primeira vez)

## 🔒 Segurança Adicional

### Proteger install.php após instalação

Após instalar o sistema, edite o Nginx e descomente as linhas:

```nginx
location = /install.php {
    deny all;
    return 404;
}
```

### Proteger arquivos sensíveis

A configuração já protege a pasta `/config/`, mas você pode adicionar:

```nginx
# Proteger arquivos .env, .htaccess, etc
location ~ /\. {
    deny all;
    return 404;
}
```

## 🐛 Troubleshooting

### Erro 502 Bad Gateway

- Verifique se o PHP-FPM está rodando: `sudo systemctl status php8.1-fpm` (ajuste versão)
- Verifique o socket: `ls -la /var/run/php/`
- Verifique logs: `sudo tail -f /var/log/nginx/longdev_error.log`

### Erro 403 Forbidden

- Verifique permissões: `sudo chown -R www-data:www-data /var/www/ra-catalog`
- Verifique SELinux (se ativo): `sudo setsebool -P httpd_read_user_content 1`

### PHP não executa

- Verifique se `php-fpm` está instalado: `sudo apt install php-fpm`
- Verifique se está rodando: `sudo systemctl status php-fpm`

### CORS não funciona

- Verifique se os headers estão sendo enviados: `curl -I https://longdev.com.br/api/products.php`
- Verifique logs do Nginx para erros

## 📝 Notas

- A configuração mantém todas as otimizações do site anterior
- SSL/HTTPS continua funcionando com Certbot
- Cache e compressão estão configurados
- API está acessível com CORS habilitado

