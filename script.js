// Função para controlar o carrossel
function initCarousel() {
    const carousels = document.querySelectorAll('.product-carousel');
    const prevButtons = document.querySelectorAll('.carousel-btn.prev');
    const nextButtons = document.querySelectorAll('.carousel-btn.next');

    // Função para rolar o carrossel
    function scrollCarousel(carousel, direction) {
        const cardWidth = carousel.querySelector('.product-card').offsetWidth;
        const gap = 20; // gap entre os cards
        const scrollAmount = cardWidth + gap;
        
        if (direction === 'next') {
            carousel.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        } else {
            carousel.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        }
    }

    // Adicionar event listeners aos botões
    prevButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const categoryId = btn.getAttribute('data-category');
            const carousel = document.getElementById(categoryId);
            scrollCarousel(carousel, 'prev');
        });
    });

    nextButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            const categoryId = btn.getAttribute('data-category');
            const carousel = document.getElementById(categoryId);
            scrollCarousel(carousel, 'next');
        });
    });

    // Adicionar scroll suave ao carrossel de promoções
    const promoCarousel = document.querySelector('.promo-carousel');
    if (promoCarousel) {
        // Permitir scroll com mouse wheel
        promoCarousel.addEventListener('wheel', (e) => {
            e.preventDefault();
            promoCarousel.scrollBy({
                left: e.deltaY > 0 ? 100 : -100,
                behavior: 'smooth'
            });
        });
    }

    // Adicionar scroll suave aos carrosséis de produtos
    carousels.forEach(carousel => {
        carousel.addEventListener('wheel', (e) => {
            e.preventDefault();
            carousel.scrollBy({
                left: e.deltaY > 0 ? 100 : -100,
                behavior: 'smooth'
            });
        });
    });
}

// Função para redirecionar para página do produto
function initProductClick() {
    const productCards = document.querySelectorAll('.product-card, .promo-card');
    
    productCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Evitar redirecionamento se clicar em elementos filhos específicos
            if (e.target.classList.contains('carousel-btn')) {
                return;
            }
            
            const productName = this.getAttribute('data-product') || this.querySelector('h3').textContent;
            const productPrice = this.getAttribute('data-price') || this.querySelector('.product-price, .promo-price-new').textContent.replace(/[^\d,]/g, '').replace(',', '.');
            const category = this.getAttribute('data-category') || '';
            
            // Redirecionar para página do produto com parâmetros
            const params = new URLSearchParams({
                name: productName,
                price: productPrice,
                category: category
            });
            
            window.location.href = `produto.html?${params.toString()}`;
        });
    });
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
document.addEventListener('DOMContentLoaded', () => {
    initCarousel();
    initProductClick();
    initTouchScroll();
    
    console.log('iFood RA - Página carregada com sucesso!');
});

