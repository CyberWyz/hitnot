// product-display.js - Handles product rendering and UI interactions

class ProductDisplay {
    constructor() {
        this.productsGrid = document.getElementById('products-grid');
        this.currentProducts = [];
        this.currentSort = 'popularity';
        this.currentCategory = 'all';
        this.currentSearch = '';
        
        this.init();
    }
    
    init() {
        this.renderProducts(productDatabase.products);
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => this.handleFilterClick(btn));
        });
        
        // Search input
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearch = e.target.value.trim().toLowerCase();
                this.updateDisplay();
            });
        }
        
        // Sort select
        const sortSelect = document.querySelector('.sort-select');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.currentSort = e.target.value;
                this.updateDisplay();
            });
        }
    }
    
    handleFilterClick(button) {
        document.querySelectorAll('.filter-btn').forEach(btn => 
            btn.classList.remove('active'));
        button.classList.add('active');
        this.currentCategory = button.dataset.category;
        this.updateDisplay();
    }
    
    updateDisplay() {
        // Get filtered products
        let products = productDatabase.getProductsByCategory(this.currentCategory);
        
        // Apply search filter
        if (this.currentSearch) {
            products = products.filter(p => 
                p.name.toLowerCase().includes(this.currentSearch) || 
                p.description.toLowerCase().includes(this.currentSearch)
            );
        }
        
        // Apply sorting
        products = productDatabase.getSortedProducts(products, this.currentSort);
        
        this.renderProducts(products);
    }
    
    renderProducts(products) {
        this.currentProducts = products;
        this.productsGrid.innerHTML = '';
        
        if (products.length === 0) {
            this.productsGrid.innerHTML = `
                <div class="no-results" data-aos="fade-up">
                    <i class="fas fa-search"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your search or filters</p>
                </div>
            `;
            return;
        }
        
        products.forEach(product => {
            this.productsGrid.appendChild(this.createProductCard(product));
        });
        
        // Refresh animations and cart
        if (typeof AOS !== 'undefined') AOS.refresh();
        if (typeof ShoppingCart !== 'undefined') new ShoppingCart();
    }
    
    createProductCard(product) {
        const card = document.createElement('div');
        card.className = 'product-card';
        card.dataset.aos = 'fade-up';
        card.dataset.id = product.id;
        
        const imageUrl = product.images ? product.images[0] : product.image;
        const formattedCategory = this.formatCategory(product.category);
        
        card.innerHTML = `
            <span class="product-category">${formattedCategory}</span>
            <div class="product-image-container">
                <img src="${imageUrl}" alt="${product.name}" class="product-image">
                ${product.stock < 5 ? '<span class="low-stock">Low Stock</span>' : ''}
            </div>
            <div class="product-details">
                <h3 class="product-title">${product.name}</h3>
                <p class="product-description">${product.description}</p>
                <div class="product-price">£${product.price.toFixed(2)}</div>
                <div class="product-actions">
                    <button class="add-to-cart" data-id="${product.id}" 
                            data-name="${product.name}" data-price="${product.price}"
                            ${product.stock <= 0 ? 'disabled' : ''}>
                        <i class="fas fa-shopping-cart"></i>
                        ${product.stock <= 0 ? 'Out of Stock' : 'Add to Cart'}
                    </button>
                    <div class="product-rating">
                        <div class="stars">${this.getStarRating(product.rating)}</div>
                        <span class="rating-count">(${product.reviewCount})</span>
                    </div>
                </div>
            </div>
        `;
        
        return card;
    }
    
    formatCategory(category) {
        return category.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    getStarRating(rating) {
        const fullStars = Math.floor(rating);
        const hasHalfStar = rating % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        return `${'<i class="fas fa-star"></i>'.repeat(fullStars)}
                ${hasHalfStar ? '<i class="fas fa-star-half-alt"></i>' : ''}
                ${'<i class="far fa-star"></i>'.repeat(emptyStars)}`;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => new ProductDisplay());