const API_URL = '../api/products.php';
const CATEGORIES_API_URL = '../api/categories.php';

let currentProductId = null;
let currentCategoryId = null;

// Carregar dados ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    loadCategories();
    loadProducts();
    loadCategoriesIntoSelect();
    
    // Preview de imagem ao selecionar arquivo
    const thumbnailInput = document.getElementById('productThumbnail');
    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnailPreview').innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 8px; margin-top: 5px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Carregar lista de produtos
async function loadProducts() {
    const loading = document.getElementById('loading');
    const productsList = document.getElementById('productsList');
    const filterCategory = document.getElementById('filterCategory').value;
    
    loading.classList.add('show');
    productsList.innerHTML = '';
    
    try {
        let url = API_URL;
        if (filterCategory) {
            url += `?categoria=${encodeURIComponent(filterCategory)}`;
        }
        
        const response = await fetch(url);
        const result = await response.json();
        
        loading.classList.remove('show');
        
        if (result.success && result.data) {
            if (result.data.length === 0) {
                productsList.innerHTML = `
                    <div class="empty-state">
                        <h3>Nenhum produto encontrado</h3>
                        <p>Adicione um novo produto para começar.</p>
                    </div>
                `;
            } else {
                productsList.innerHTML = result.data.map(product => {
                    // Determinar caminho da imagem
                    let imagePath = 'https://via.placeholder.com/300x200/2196F3/FFFFFF?text=Sem+Imagem';
                    if (product.imagem_url) {
                        imagePath = product.imagem_url.startsWith('http') 
                            ? product.imagem_url 
                            : '../' + product.imagem_url;
                    }
                    
                    return `
                        <div class="product-card">
                            <img src="${imagePath}" 
                                 alt="${escapeHtml(product.nome)}" 
                                 class="product-image"
                                 onerror="this.src='https://via.placeholder.com/300x200/2196F3/FFFFFF?text=Sem+Imagem'">
                            <h3 class="product-name">${escapeHtml(product.nome)}</h3>
                            <span class="product-category">${escapeHtml(product.categoria)}</span>
                            <p class="product-description">${escapeHtml(product.descricao || 'Sem descrição')}</p>
                            <div class="product-actions">
                                <button class="btn btn-edit" onclick="editProduct(${product.id})">✏️ Editar</button>
                                <button class="btn btn-danger" onclick="deleteProduct(${product.id}, '${escapeHtml(product.nome)}')">🗑️ Excluir</button>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        } else {
            productsList.innerHTML = `
                <div class="empty-state">
                    <h3>Erro ao carregar produtos</h3>
                    <p>${result.message || 'Erro desconhecido'}</p>
                </div>
            `;
        }
    } catch (error) {
        loading.classList.remove('show');
        productsList.innerHTML = `
            <div class="empty-state">
                <h3>Erro ao carregar produtos</h3>
                <p>${error.message}</p>
            </div>
        `;
    }
}

// Mostrar modal para adicionar produto
function showAddModal() {
    currentProductId = null;
    document.getElementById('modalTitle').textContent = 'Adicionar Produto';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    document.getElementById('thumbnailPreview').innerHTML = '';
    document.getElementById('currentThumbnail').innerHTML = '';
    document.getElementById('currentModel3d').innerHTML = '';
    document.getElementById('productModal').classList.add('show');
}

// Preview de imagem ao selecionar arquivo
document.addEventListener('DOMContentLoaded', () => {
    const thumbnailInput = document.getElementById('productThumbnail');
    if (thumbnailInput) {
        thumbnailInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('thumbnailPreview').innerHTML = `
                        <img src="${e.target.result}" style="max-width: 200px; max-height: 150px; border-radius: 8px; margin-top: 5px;">
                    `;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Editar produto
async function editProduct(id) {
    try {
        const response = await fetch(`${API_URL}?id=${id}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const product = result.data;
            currentProductId = product.id;
            
            document.getElementById('modalTitle').textContent = 'Editar Produto';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.nome;
            document.getElementById('productCategory').value = product.categoria;
            document.getElementById('productDescription').value = product.descricao || '';
            
            // Limpar previews
            document.getElementById('thumbnailPreview').innerHTML = '';
            document.getElementById('currentThumbnail').innerHTML = '';
            document.getElementById('currentModel3d').innerHTML = '';
            
            // Mostrar imagem atual se existir
            if (product.imagem_url) {
                const imgPath = product.imagem_url.startsWith('http') ? product.imagem_url : '../' + product.imagem_url;
                document.getElementById('currentThumbnail').innerHTML = `
                    <small style="color: #666;">Imagem atual:</small><br>
                    <img src="${imgPath}" style="max-width: 200px; max-height: 150px; margin-top: 5px; border-radius: 8px;" 
                         onerror="this.style.display='none'">
                `;
            }
            
            // Mostrar modelo 3D atual se existir
            if (product.modelo_3d_url) {
                const modelPath = product.modelo_3d_url.startsWith('http') ? product.modelo_3d_url : '../' + product.modelo_3d_url;
                document.getElementById('currentModel3d').innerHTML = `
                    <small style="color: #666;">Modelo 3D atual: ${product.modelo_3d_url.split('/').pop()}</small>
                `;
            }
            
            // Limpar inputs de arquivo
            document.getElementById('productThumbnail').value = '';
            document.getElementById('productModel3d').value = '';
            
            document.getElementById('productModal').classList.add('show');
        } else {
            alert('Erro ao carregar produto: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao carregar produto: ' + error.message);
    }
}

// Salvar produto (criar ou atualizar)
async function saveProduct(event) {
    event.preventDefault();
    
    // Criar FormData para enviar arquivos
    const formData = new FormData();
    formData.append('nome', document.getElementById('productName').value);
    formData.append('categoria', document.getElementById('productCategory').value);
    formData.append('descricao', document.getElementById('productDescription').value);
    
    // Adicionar arquivos se selecionados
    const thumbnailFile = document.getElementById('productThumbnail').files[0];
    if (thumbnailFile) {
        formData.append('thumbnail', thumbnailFile);
    }
    
    const model3dFile = document.getElementById('productModel3d').files[0];
    if (model3dFile) {
        formData.append('model3d', model3dFile);
    }
    
    try {
        let response;
        if (currentProductId) {
            // Atualizar
            formData.append('id', currentProductId);
            response = await fetch(API_URL, {
                method: 'PUT',
                body: formData
            });
        } else {
            // Criar
            response = await fetch(API_URL, {
                method: 'POST',
                body: formData
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message || 'Produto salvo com sucesso!');
            closeModal();
            loadProducts();
        } else {
            alert('Erro ao salvar produto: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao salvar produto: ' + error.message);
    }
}

// Deletar produto
async function deleteProduct(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir o produto "${nome}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_URL}?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Produto excluído com sucesso!');
            loadProducts();
        } else {
            alert('Erro ao excluir produto: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao excluir produto: ' + error.message);
    }
}

// Fechar modal
function closeModal() {
    document.getElementById('productModal').classList.remove('show');
    currentProductId = null;
}

// Fechar modal ao clicar fora
window.onclick = function(event) {
    const productModal = document.getElementById('productModal');
    const categoryModal = document.getElementById('categoryModal');
    if (event.target === productModal) {
        closeModal();
    }
    if (event.target === categoryModal) {
        closeCategoryModal();
    }
}

// ========== FUNÇÕES DE CATEGORIAS ==========

// Carregar lista de categorias
async function loadCategories() {
    const loading = document.getElementById('categoriesLoading');
    const categoriesList = document.getElementById('categoriesList');
    
    if (loading) loading.classList.add('show');
    if (categoriesList) categoriesList.innerHTML = '';
    
    try {
        const response = await fetch(CATEGORIES_API_URL);
        const result = await response.json();
        
        if (loading) loading.classList.remove('show');
        
        if (result.success && result.data) {
            if (result.data.length === 0) {
                if (categoriesList) {
                    categoriesList.innerHTML = `
                        <div class="empty-state">
                            <h3>Nenhuma categoria encontrada</h3>
                            <p>Adicione uma nova categoria para começar.</p>
                        </div>
                    `;
                }
            } else {
                if (categoriesList) {
                    categoriesList.innerHTML = result.data.map(category => `
                        <div class="product-card">
                            <div style="font-size: 48px; text-align: center; margin-bottom: 15px;">
                                ${category.icone || '📁'}
                            </div>
                            <h3 class="product-name">${escapeHtml(category.nome)}</h3>
                            <div class="product-actions">
                                <button class="btn btn-edit" onclick="editCategory(${category.id})">✏️ Editar</button>
                                <button class="btn btn-danger" onclick="deleteCategory(${category.id}, '${escapeHtml(category.nome)}')">🗑️ Excluir</button>
                            </div>
                        </div>
                    `).join('');
                }
            }
        } else {
            if (categoriesList) {
                categoriesList.innerHTML = `
                    <div class="empty-state">
                        <h3>Erro ao carregar categorias</h3>
                        <p>${result.message || 'Erro desconhecido'}</p>
                    </div>
                `;
            }
        }
    } catch (error) {
        if (loading) loading.classList.remove('show');
        if (categoriesList) {
            categoriesList.innerHTML = `
                <div class="empty-state">
                    <h3>Erro ao carregar categorias</h3>
                    <p>${error.message}</p>
                </div>
            `;
        }
    }
}

// Carregar categorias no select
async function loadCategoriesIntoSelect() {
    const select = document.getElementById('productCategory');
    const filterSelect = document.getElementById('filterCategory');
    
    try {
        const response = await fetch(CATEGORIES_API_URL);
        const result = await response.json();
        
        if (result.success && result.data) {
            // Atualizar select do formulário
            if (select) {
                select.innerHTML = '<option value="">Selecione...</option>' + 
                    result.data.map(cat => 
                        `<option value="${escapeHtml(cat.nome)}">${cat.icone || ''} ${escapeHtml(cat.nome)}</option>`
                    ).join('');
            }
            
            // Atualizar select de filtro
            if (filterSelect) {
                filterSelect.innerHTML = '<option value="">Todas as categorias</option>' + 
                    result.data.map(cat => 
                        `<option value="${escapeHtml(cat.nome)}">${cat.icone || ''} ${escapeHtml(cat.nome)}</option>`
                    ).join('');
            }
        }
    } catch (error) {
        console.error('Erro ao carregar categorias:', error);
    }
}

// Mostrar modal para adicionar categoria
function showAddCategoryModal() {
    currentCategoryId = null;
    document.getElementById('categoryModalTitle').textContent = 'Adicionar Categoria';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryModal').classList.add('show');
}

// Editar categoria
async function editCategory(id) {
    try {
        const response = await fetch(`${CATEGORIES_API_URL}?id=${id}`);
        const result = await response.json();
        
        if (result.success && result.data) {
            const category = result.data;
            currentCategoryId = category.id;
            
            document.getElementById('categoryModalTitle').textContent = 'Editar Categoria';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.nome;
            document.getElementById('categoryIcon').value = category.icone || '';
            
            document.getElementById('categoryModal').classList.add('show');
        } else {
            alert('Erro ao carregar categoria: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao carregar categoria: ' + error.message);
    }
}

// Salvar categoria (criar ou atualizar)
async function saveCategory(event) {
    event.preventDefault();
    
    const formData = {
        nome: document.getElementById('categoryName').value.trim(),
        icone: document.getElementById('categoryIcon').value.trim()
    };
    
    try {
        let response;
        if (currentCategoryId) {
            // Atualizar
            formData.id = currentCategoryId;
            response = await fetch(CATEGORIES_API_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
        } else {
            // Criar
            response = await fetch(CATEGORIES_API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message || 'Categoria salva com sucesso!');
            closeCategoryModal();
            loadCategories();
            loadCategoriesIntoSelect();
        } else {
            alert('Erro ao salvar categoria: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao salvar categoria: ' + error.message);
    }
}

// Deletar categoria
async function deleteCategory(id, nome) {
    if (!confirm(`Tem certeza que deseja excluir a categoria "${nome}"?\n\nNota: Categorias com produtos associados não podem ser excluídas.`)) {
        return;
    }
    
    try {
        const response = await fetch(`${CATEGORIES_API_URL}?id=${id}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Categoria excluída com sucesso!');
            loadCategories();
            loadCategoriesIntoSelect();
        } else {
            alert('Erro ao excluir categoria: ' + (result.message || 'Erro desconhecido'));
        }
    } catch (error) {
        alert('Erro ao excluir categoria: ' + error.message);
    }
}

// Fechar modal de categoria
function closeCategoryModal() {
    document.getElementById('categoryModal').classList.remove('show');
    currentCategoryId = null;
}

// Função para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

