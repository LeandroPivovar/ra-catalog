<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - iFood RA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-container">
            <h1>🛠️ Painel Administrativo</h1>
            <a href="../index.html" class="back-link">← Voltar ao Site</a>
        </div>
    </header>

    <main class="admin-main">
        <div class="admin-container">
            <div class="admin-actions">
                <h2>Gerenciar Produtos</h2>
                <button class="btn btn-primary" onclick="loadProducts()">🔄 Atualizar Lista</button>
                <button class="btn btn-success" onclick="showAddModal()">➕ Adicionar Produto</button>
            </div>

            <div class="filters">
                <label for="filterCategory">Filtrar por categoria:</label>
                <select id="filterCategory" onchange="loadProducts()">
                    <option value="">Todas as categorias</option>
                    <option value="Eletrônicos">Eletrônicos</option>
                    <option value="Calçados">Calçados</option>
                    <option value="Eletrodomésticos">Eletrodomésticos</option>
                </select>
            </div>

            <div id="loading" class="loading">Carregando produtos...</div>
            <div id="productsList" class="products-list"></div>
        </div>
    </main>

    <!-- Modal para adicionar/editar produto -->
    <div id="productModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Adicionar Produto</h2>
            <form id="productForm" onsubmit="saveProduct(event)">
                <input type="hidden" id="productId">
                
                <div class="form-group">
                    <label for="productName">Nome do Produto *</label>
                    <input type="text" id="productName" required>
                </div>

                <div class="form-group">
                    <label for="productCategory">Categoria *</label>
                    <select id="productCategory" required>
                        <option value="">Selecione...</option>
                        <option value="Eletrônicos">Eletrônicos</option>
                        <option value="Calçados">Calçados</option>
                        <option value="Eletrodomésticos">Eletrodomésticos</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="productDescription">Descrição</label>
                    <textarea id="productDescription" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="productImageUrl">URL da Imagem</label>
                    <input type="url" id="productImageUrl" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label for="productModel3dUrl">URL do Modelo 3D</label>
                    <input type="url" id="productModel3dUrl" placeholder="https://...">
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

