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
        <!-- Seção de Categorias -->
        <div class="admin-container">
            <div class="admin-actions">
                <h2>📁 Gerenciar Categorias</h2>
                <button class="btn btn-primary" onclick="loadCategories()">🔄 Atualizar Lista</button>
                <button class="btn btn-success" onclick="showAddCategoryModal()">➕ Adicionar Categoria</button>
            </div>

            <div id="categoriesLoading" class="loading">Carregando categorias...</div>
            <div id="categoriesList" class="categories-list"></div>
        </div>

        <!-- Seção de Produtos -->
        <div class="admin-container" style="margin-top: 30px;">
            <div class="admin-actions">
                <h2>🛍️ Gerenciar Produtos</h2>
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
                        <option value="">Carregando categorias...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="productDescription">Descrição</label>
                    <textarea id="productDescription" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="productThumbnail">Imagem (Thumbnail) *</label>
                    <input type="file" id="productThumbnail" accept="image/*">
                    <small style="color: #666; font-size: 12px;">Formatos: JPG, PNG, GIF, WEBP (máx. 5MB)</small>
                    <div id="thumbnailPreview" style="margin-top: 10px;"></div>
                    <div id="currentThumbnail" style="margin-top: 10px;"></div>
                </div>

                <div class="form-group">
                    <label for="productModel3d">Modelo 3D</label>
                    <input type="file" id="productModel3d" accept=".glb,.gltf,.obj,.fbx">
                    <small style="color: #666; font-size: 12px;">Formatos: GLB, GLTF, OBJ, FBX (máx. 50MB)</small>
                    <div id="currentModel3d" style="margin-top: 10px;"></div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para adicionar/editar categoria -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCategoryModal()">&times;</span>
            <h2 id="categoryModalTitle">Adicionar Categoria</h2>
            <form id="categoryForm" onsubmit="saveCategory(event)">
                <input type="hidden" id="categoryId">
                
                <div class="form-group">
                    <label for="categoryName">Nome da Categoria *</label>
                    <input type="text" id="categoryName" required placeholder="Ex: Eletrônicos">
                </div>

                <div class="form-group">
                    <label for="categoryIcon">Ícone (Emoji)</label>
                    <input type="text" id="categoryIcon" placeholder="Ex: 📱" maxlength="2">
                    <small style="color: #666; font-size: 12px;">Opcional: adicione um emoji para identificar a categoria</small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeCategoryModal()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>

