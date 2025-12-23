# Sistema de Upload de Arquivos

## 📁 Estrutura de Pastas

O sistema cria automaticamente as seguintes pastas para armazenar arquivos:

```
uploads/
├── thumbnails/    # Imagens dos produtos (JPG, PNG, GIF, WEBP)
└── models3d/      # Modelos 3D (GLB, GLTF, OBJ, FBX)
```

## 🔧 Configuração

### Permissões das Pastas

Certifique-se de que o servidor web tem permissão para escrever nas pastas:

```bash
sudo chown -R www-data:www-data /var/www/ra-catalog/uploads
sudo chmod -R 755 /var/www/ra-catalog/uploads
```

### Limites de Upload (PHP)

Ajuste no `php.ini` se necessário:

```ini
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300
```

## 📤 Formatos Suportados

### Imagens (Thumbnail)
- **Formatos**: JPG, JPEG, PNG, GIF, WEBP
- **Tamanho máximo**: 5MB
- **Recomendado**: 800x600px ou maior

### Modelos 3D
- **Formatos**: GLB, GLTF, OBJ, FBX
- **Tamanho máximo**: 50MB
- **Recomendado**: GLB (formato mais eficiente)

## 🎯 Como Usar

### No Admin

1. **Adicionar Produto**:
   - Preencha nome e categoria
   - Selecione uma imagem (thumbnail) - **obrigatório**
   - Opcionalmente, selecione um modelo 3D
   - Clique em "Salvar"

2. **Editar Produto**:
   - A imagem atual será exibida
   - Selecione uma nova imagem para substituir
   - O modelo 3D atual será mantido se não selecionar novo

### Preview

- Ao selecionar uma imagem, um preview será exibido automaticamente
- A imagem atual será mostrada ao editar um produto

## 🔒 Segurança

- Validação de extensões de arquivo
- Validação de tamanho máximo
- Nomes de arquivo únicos (evita sobrescrita)
- Arquivos salvos fora do diretório web root (relativo)

## 📝 Notas

- Arquivos antigos não são deletados automaticamente ao atualizar
- Para limpar arquivos não utilizados, faça manualmente
- URLs antigas ainda funcionam (compatibilidade retroativa)
- Modelos 3D são opcionais, mas recomendados para AR

## 🐛 Troubleshooting

### Erro: "Erro ao salvar arquivo"
- Verifique permissões das pastas
- Verifique limites do PHP (upload_max_filesize)
- Verifique espaço em disco

### Imagem não aparece
- Verifique se o caminho está correto
- Verifique permissões de leitura
- Verifique se o arquivo foi realmente enviado

### Modelo 3D não carrega no AR
- Verifique se o formato é suportado (GLB recomendado)
- Verifique se o arquivo não está corrompido
- Verifique se o navegador suporta WebXR/AR

