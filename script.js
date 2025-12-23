// Inicializar carrossel uma vez (usa delegação de eventos)
let carouselInitialized = false;

function initCarousel() {
    if (carouselInitialized) return;
    carouselInitialized = true;
    
    // Usar delegação de eventos para funcionar com elementos dinâmicos
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('carousel-btn')) {
            const btn = e.target;
            const categoryId = btn.getAttribute('data-category');
            const carousel = document.getElementById(categoryId);
            
            if (!carousel) return;
            
            // Verificar se há produtos no carrossel
            const firstCard = carousel.querySelector('.product-card');
            if (!firstCard) return;
            
            const cardWidth = firstCard.offsetWidth;
            const gap = 20; // gap entre os cards
            const scrollAmount = cardWidth + gap;
            
            if (btn.classList.contains('next')) {
                carousel.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            } else if (btn.classList.contains('prev')) {
                carousel.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            }
        }
    });
}

// Função para inicializar scroll de um carrossel específico
function initCarouselScroll(carousel) {
    // Adicionar scroll com mouse wheel (se ainda não tiver)
    if (!carousel.hasAttribute('data-scroll-initialized')) {
        carousel.setAttribute('data-scroll-initialized', 'true');
        carousel.addEventListener('wheel', (e) => {
            e.preventDefault();
            carousel.scrollBy({
                left: e.deltaY > 0 ? 100 : -100,
                behavior: 'smooth'
            });
        });
    }
}

// Função para redirecionar para página do produto
function initProductClick() {
    // Usar delegação de eventos para funcionar com elementos dinâmicos
    document.addEventListener('click', function(e) {
        const productCard = e.target.closest('.product-card');
        if (!productCard) return;
        
        // Evitar redirecionamento se clicar em elementos filhos específicos
        if (e.target.classList.contains('carousel-btn')) {
            return;
        }
        
            const productName = productCard.getAttribute('data-product') || productCard.querySelector('h3')?.textContent;
            const productId = productCard.getAttribute('data-id');
            const category = productCard.getAttribute('data-category') || '';
            
            if (productId) {
                // Redirecionar para página do produto com ID
                window.location.href = `produto.html?id=${productId}`;
            } else if (productName) {
                // Fallback para parâmetros antigos
                const params = new URLSearchParams({
                    name: productName,
                    category: category
                });
                window.location.href = `produto.html?${params.toString()}`;
            }
    });
}

// Função para gerar ID único baseado no nome da categoria
function generateCategoryId(categoryName) {
    return categoryName
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
}

// Função para buscar categorias e produtos da API
async function loadCategoriesAndProducts() {
    const container = document.getElementById('categoriesContainer');
    if (!container) return;
    
    try {
        // Buscar categorias e produtos em paralelo
        const [categoriesResponse, productsResponse] = await Promise.all([
            fetch('api/categories.php'),
            fetch('api/products.php')
        ]);
        
        const categoriesResult = await categoriesResponse.json();
        const productsResult = await productsResponse.json();
        
        if (!categoriesResult.success || !categoriesResult.data) {
            container.innerHTML = '<div class="loading-products">Erro ao carregar categorias.</div>';
            return;
        }
        
        const categories = categoriesResult.data;
        const products = productsResult.success && productsResult.data ? productsResult.data : [];
        
        // Agrupar produtos por categoria
        const productsByCategory = {};
        products.forEach(product => {
            const categoryName = product.categoria;
            if (!productsByCategory[categoryName]) {
                productsByCategory[categoryName] = [];
            }
            productsByCategory[categoryName].push(product);
        });
        
        // Renderizar categorias
        if (categories.length === 0) {
            container.innerHTML = '<div class="loading-products">Nenhuma categoria encontrada.</div>';
            return;
        }
        
        container.innerHTML = categories.map(category => {
            const categoryId = generateCategoryId(category.nome);
            const categoryProducts = productsByCategory[category.nome] || [];
            const icon = category.icone || '📦';
            
            return `
                <section class="category-section">
                    <div class="category-header">
                        <h2 class="section-title">${icon} ${escapeHtml(category.nome)}</h2>
                        <div class="carousel-controls">
                            <button class="carousel-btn prev" data-category="${categoryId}">‹</button>
                            <button class="carousel-btn next" data-category="${categoryId}">›</button>
                        </div>
                    </div>
                    <div class="carousel-container">
                        <div class="product-carousel" id="${categoryId}">
                            ${categoryProducts.length > 0 ? '' : '<div class="loading-products">Nenhum produto nesta categoria.</div>'}
                        </div>
                    </div>
                </section>
            `;
        }).join('');
        
        // Renderizar produtos em cada categoria
        categories.forEach(category => {
            const categoryId = generateCategoryId(category.nome);
            const categoryProducts = productsByCategory[category.nome] || [];
            renderProducts(categoryId, categoryProducts);
        });
        
        // Inicializar scroll touch para novos carrosséis
        setTimeout(() => {
            const carousels = document.querySelectorAll('.product-carousel');
            carousels.forEach(carousel => {
                initCarouselScroll(carousel);
            });
            initTouchScroll();
        }, 100);
        
    } catch (error) {
        console.error('Erro ao carregar categorias e produtos:', error);
        container.innerHTML = '<div class="loading-products">Erro ao carregar conteúdo. Tente recarregar a página.</div>';
    }
}

// Função para renderizar produtos em um carrossel
function renderProducts(carouselId, products) {
    const carousel = document.getElementById(carouselId);
    if (!carousel) return;
    
    if (products.length === 0) {
        carousel.innerHTML = '<div class="loading-products">Nenhum produto encontrado nesta categoria.</div>';
        return;
    }
    
    carousel.innerHTML = products.map(product => {
        // Determinar caminho da imagem (suporta URLs e arquivos locais)
        let imageUrl = `https://via.placeholder.com/180x180/2196F3/FFFFFF?text=${encodeURIComponent(product.nome)}`;
        if (product.imagem_url) {
            if (product.imagem_url.startsWith('http')) {
                imageUrl = product.imagem_url;
            } else {
                // Arquivo local - usar caminho relativo
                imageUrl = product.imagem_url;
            }
        }
        
        return `
            <div class="product-card" 
                 data-id="${product.id}" 
                 data-product="${escapeHtml(product.nome)}" 
                 data-category="${escapeHtml(product.categoria)}">
                <img src="${imageUrl}" 
                     alt="${escapeHtml(product.nome)}" 
                     class="product-image"
                     onerror="this.src='https://via.placeholder.com/180x180/2196F3/FFFFFF?text=Sem+Imagem'">
                <div class="product-info">
                    <h3>${escapeHtml(product.nome)}</h3>
                </div>
            </div>
        `;
    }).join('');
    
    // Reinicializar scroll do carrossel após renderizar
    initCarouselScroll(carousel);
}


// Função para escapar HTML (prevenir XSS)
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Função para mostrar notificação
function showNotification(message) {
    // Criar elemento de notificação
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 25px;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        animation: slideIn 0.3s ease;
    `;
    
    // Adicionar animação CSS
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Remover após 3 segundos
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Função para melhorar a experiência de scroll em dispositivos móveis
function initTouchScroll() {
    const carousels = document.querySelectorAll('.product-carousel, .promo-carousel');
    
    carousels.forEach(carousel => {
        let isDown = false;
        let startX;
        let scrollLeft;

        carousel.addEventListener('mousedown', (e) => {
            isDown = true;
            carousel.style.cursor = 'grabbing';
            startX = e.pageX - carousel.offsetLeft;
            scrollLeft = carousel.scrollLeft;
        });

        carousel.addEventListener('mouseleave', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });

        carousel.addEventListener('mouseup', () => {
            isDown = false;
            carousel.style.cursor = 'grab';
        });

        carousel.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carousel.offsetLeft;
            const walk = (x - startX) * 2;
            carousel.scrollLeft = scrollLeft - walk;
        });

        // Adicionar cursor grab
        carousel.style.cursor = 'grab';
    });
}

// Inicializar tudo quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', async () => {
    // Inicializar funcionalidades primeiro (usam delegação de eventos)
    initCarousel();
    initProductClick();
    
    // Carregar categorias e produtos do banco de dados
    await loadCategoriesAndProducts();
    
    console.log('iFood RA - Página carregada com sucesso!');
});

