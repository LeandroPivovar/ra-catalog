# Correção do Erro de Instalação

## Problema
A tabela `produtos` foi criada sem a coluna `categoria_id`, mas o código tenta inserir dados com essa coluna.

## Solução

Execute o script de migração:

```bash
php migrate.php
```

Ou acesse no navegador:
```
http://localhost/ifood-ra/migrate.php
```

Isso irá:
1. Adicionar a coluna `categoria_id` se não existir
2. Criar o índice
3. Criar a foreign key
4. Atualizar produtos existentes com os IDs das categorias

## Alternativa: Reinstalar

Se preferir, delete o arquivo `installed.flag` e execute `install.php` novamente. O código agora verifica se a coluna existe antes de tentar usá-la.

