const API_URL = '../api/products.php';

let currentProductId = null;

// Carregar produtos ao carregar a página
document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
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
                productsList.innerHTML = result.data.map(product => `
                    <div class="product-card">
                        <img src="${product.imagem_url || 'https://via.placeholder.com/300x200/2196F3/FFFFFF?text=Sem+Imagem'}" 
                             alt="${product.nome}" 
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
                `).join('');
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
    document.getElementById('productModal').classList.add('show');
}

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
            document.getElementById('productImageUrl').value = product.imagem_url || '';
            document.getElementById('productModel3dUrl').value = product.modelo_3d_url || '';
            
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
    
    const formData = {
        nome: document.getElementById('productName').value,
        categoria: document.getElementById('productCategory').value,
        descricao: document.getElementById('productDescription').value,
        imagem_url: document.getElementById('productImageUrl').value,
        modelo_3d_url: document.getElementById('productModel3dUrl').value
    };
    
    try {
        let response;
        if (currentProductId) {
            // Atualizar
            formData.id = currentProductId;
            response = await fetch(API_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
        } else {
            // Criar
            response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
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
    const modal = document.getElementById('productModal');
    if (event.target === modal) {
        closeModal();
    }
}

// Função para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

