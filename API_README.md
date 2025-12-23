# API e Sistema de Administração - iFood RA

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache/Nginx) ou PHP built-in server

## 🚀 Instalação

### 1. Configurar Banco de Dados

Edite o arquivo `config/database.php` e ajuste as credenciais:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'ifood_ra');
```

### 2. Executar Instalação

Acesse no navegador:
```
http://localhost/ifood-ra/install.php
```

Ou via linha de comando:
```bash
php install.php
```

A instalação irá:
- Criar o banco de dados `ifood_ra`
- Criar a tabela `produtos`
- Inserir produtos de exemplo
- Criar arquivo `installed.flag` para evitar reinstalação

**Nota:** Para reinstalar, delete o arquivo `installed.flag`.

## 📡 API REST - Produtos

### Base URL
```
/api/products.php
```

### Endpoints

#### GET - Listar Produtos

**Listar todos:**
```
GET /api/products.php
```

**Listar por categoria:**
```
GET /api/products.php?categoria=Eletrônicos
```

**Buscar por ID:**
```
GET /api/products.php?id=1
```

**Resposta:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "nome": "Smartphone XYZ",
            "categoria": "Eletrônicos",
            "descricao": "Descrição do produto",
            "imagem_url": "https://...",
            "modelo_3d_url": "",
            "created_at": "2024-01-01 12:00:00",
            "updated_at": "2024-01-01 12:00:00"
        }
    ],
    "message": "Produtos listados com sucesso"
}
```

#### POST - Criar Produto

```
POST /api/products.php
Content-Type: application/json

{
    "nome": "Novo Produto",
    "categoria": "Eletrônicos",
    "descricao": "Descrição do produto",
    "imagem_url": "https://...",
    "modelo_3d_url": "https://..."
}
```

**Resposta:**
```json
{
    "success": true,
    "data": {
        "id": 10
    },
    "message": "Produto criado com sucesso"
}
```

#### PUT - Atualizar Produto

```
PUT /api/products.php
Content-Type: application/json

{
    "id": 1,
    "nome": "Produto Atualizado",
    "categoria": "Calçados",
    "descricao": "Nova descrição"
}
```

**Resposta:**
```json
{
    "success": true,
    "message": "Produto atualizado com sucesso"
}
```

#### DELETE - Deletar Produto

```
DELETE /api/products.php?id=1
```

**Resposta:**
```json
{
    "success": true,
    "message": "Produto deletado com sucesso"
}
```

## 🛠️ Painel Administrativo

### Acessar Admin

```
http://localhost/ifood-ra/admin/
```

### Funcionalidades

- ✅ Listar todos os produtos
- ✅ Filtrar por categoria
- ✅ Adicionar novo produto
- ✅ Editar produto existente
- ✅ Excluir produto
- ✅ Visualizar produtos em cards

### Estrutura do Banco de Dados

**Tabela: produtos**

| Campo | Tipo | Descrição |
|-------|------|-----------|
| id | INT | Chave primária (auto increment) |
| nome | VARCHAR(255) | Nome do produto |
| categoria | VARCHAR(100) | Categoria (Eletrônicos, Calçados, Eletrodomésticos) |
| descricao | TEXT | Descrição do produto |
| imagem_url | VARCHAR(500) | URL da imagem do produto |
| modelo_3d_url | VARCHAR(500) | URL do modelo 3D (para AR) |
| created_at | TIMESTAMP | Data de criação |
| updated_at | TIMESTAMP | Data de atualização |

## 🔧 Testando a API

### Com cURL

**Listar produtos:**
```bash
curl http://localhost/ifood-ra/api/products.php
```

**Criar produto:**
```bash
curl -X POST http://localhost/ifood-ra/api/products.php \
  -H "Content-Type: application/json" \
  -d '{
    "nome": "Teste",
    "categoria": "Eletrônicos",
    "descricao": "Produto de teste"
  }'
```

**Atualizar produto:**
```bash
curl -X PUT http://localhost/ifood-ra/api/products.php \
  -H "Content-Type: application/json" \
  -d '{
    "id": 1,
    "nome": "Produto Atualizado"
  }'
```

**Deletar produto:**
```bash
curl -X DELETE http://localhost/ifood-ra/api/products.php?id=1
```

## 📝 Notas

- A API retorna sempre JSON
- Todos os endpoints suportam CORS
- Campos obrigatórios: `nome` e `categoria`
- O sistema cria automaticamente timestamps
- A instalação insere produtos de exemplo automaticamente

