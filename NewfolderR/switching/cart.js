// cart.js

class ShoppingCart {
    constructor() {
        this.cart = JSON.parse(localStorage.getItem('cart')) || [];
        this.cartCount = document.querySelector('.cart-count');
        this.addToCartButtons = document.querySelectorAll('.add-to-cart');
        this.init();
    }
    
    init() {
        // Initialize cart count
        this.updateCartCount();
        
        // Add event listeners to all add to cart buttons
        this.addToCartButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const price = parseFloat(button.getAttribute('data-price'));
                
                this.addToCart(id, name, price);
            });
        });
        
        // Create and add the cart modal to the DOM
        this.createCartModal();
        
        // Add event listener to cart icon
        const cartIcon = document.getElementById('cart-icon');
        if (cartIcon) {
            cartIcon.addEventListener('click', () => {
                this.openCartModal();
            });
        }
    }
    
    createCartModal() {
        const cartModal = document.createElement('div');
        cartModal.className = 'cart-modal';
        cartModal.innerHTML = `
            <div class="cart-modal-content">
                <div class="cart-modal-header">
                    <h2>Your Shopping Cart</h2>
                    <button class="cart-modal-close">&times;</button>
                </div>
                <div class="cart-modal-body">
                    <div class="cart-items">
                        <!-- Cart items will be dynamically added here -->
                    </div>
                </div>
                <div class="cart-modal-footer">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="cart-total-price">£0.00</span>
                    </div>
                    <div class="cart-actions">
                        <button class="cart-clear-btn">Clear Cart</button>
                        <button class="cart-checkout-btn">Checkout</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(cartModal);
        
        // Add event listeners
        const closeBtn = cartModal.querySelector('.cart-modal-close');
        closeBtn.addEventListener('click', () => {
            this.closeCartModal();
        });
        
        // Close when clicking outside the modal content
        cartModal.addEventListener('click', (e) => {
            if (e.target === cartModal) {
                this.closeCartModal();
            }
        });
        
        // Clear cart
        const clearBtn = cartModal.querySelector('.cart-clear-btn');
        clearBtn.addEventListener('click', () => {
            this.clearCart();
        });
        
        // Checkout
        const checkoutBtn = cartModal.querySelector('.cart-checkout-btn');
        checkoutBtn.addEventListener('click', () => {
            this.checkout();
        });
        
        // Add styles for the cart modal
        this.addCartStyles();
    }
    
    addCartStyles() {
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .cart-modal {
                display: none;
                position: fixed;
                z-index: 2000;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                align-items: center;
                justify-content: center;
            }
            
            .cart-modal.active {
                display: flex;
            }
            
            .cart-modal-content {
                background-color: var(--card-color);
                border-radius: 10px;
                width: 90%;
                max-width: 600px;
                max-height: 80vh;
                box-shadow: var(--card-shadow);
                display: flex;
                flex-direction: column;
            }
            
            .cart-modal-header {
                padding: 15px 20px;
                border-bottom: 1px solid var(--border-color);
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .cart-modal-header h2 {
                margin: 0;
                color: var(--text-color);
                font-size: 1.5rem;
            }
            
            .cart-modal-close {
                background: none;
                border: none;
                font-size: 1.5rem;
                cursor: pointer;
                color: var(--text-color);
            }
            
            .cart-modal-body {
                padding: 20px;
                overflow-y: auto;
                max-height: 50vh;
            }
            
            .cart-items {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }
            
            .cart-item {
                display: grid;
                grid-template-columns: auto 1fr auto;
                gap: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid var(--border-color);
            }
            
            .cart-item-image {
                width: 60px;
                height: 60px;
                background-color: #f0f0f0;
                border-radius: 5px;
                background-size: cover;
                background-position: center;
            }
            
            .cart-item-details {
                display: flex;
                flex-direction: column;
            }
            
            .cart-item-name {
                font-weight: bold;
                color: var(--text-color);
                margin-bottom: 5px;
            }
            
            .cart-item-price {
                color: var(--accent-color);
                font-weight: 500;
            }
            
            .cart-item-actions {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            
            .cart-item-quantity {
                display: flex;
                align-items: center;
                gap: 5px;
            }
            
            .cart-item-quantity button {
                width: 25px;
                height: 25px;
                background-color: var(--secondary-color);
                border: none;
                border-radius: 5px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }
            
            .cart-item-quantity span {
                font-weight: bold;
                min-width: 25px;
                text-align: center;
            }
            
            .cart-item-remove {
                color: #ff5555;
                cursor: pointer;
                background: none;
                border: none;
                font-size: 1.2rem;
            }
            
            .cart-modal-footer {
                padding: 15px 20px;
                border-top: 1px solid var(--border-color);
            }
            
            .cart-total {
                display: flex;
                justify-content: space-between;
                margin-bottom: 15px;
                font-size: 1.2rem;
                font-weight: bold;
                color: var(--text-color);
            }
            
            .cart-actions {
                display: flex;
                justify-content: space-between;
            }
            
            .cart-clear-btn, .cart-checkout-btn {
                padding: 10px 20px;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-weight: 600;
            }
            
            .cart-clear-btn {
                background-color: var(--secondary-color);
                color: var(--text-color);
            }
            
            .cart-checkout-btn {
                background-color: var(--accent-color);
                color: white;
            }
            
            .empty-cart-message {
                text-align: center;
                padding: 20px;
                color: var(--text-color);
                font-size: 1.1rem;
                opacity: 0.7;
            }
            
            @media (max-width: 768px) {
                .cart-item {
                    grid-template-columns: 50px 1fr auto;
                }
                
                .cart-item-image {
                    width: 50px;
                    height: 50px;
                }
            }
        `;
        
        document.head.appendChild(styleElement);
    }
    
    openCartModal() {
        const cartModal = document.querySelector('.cart-modal');
        this.updateCartItems();
        cartModal.classList.add('active');
    }
    
    closeCartModal() {
        const cartModal = document.querySelector('.cart-modal');
        cartModal.classList.remove('active');
    }
    
    updateCartItems() {
        const cartItems = document.querySelector('.cart-items');
        const cartTotal = document.querySelector('.cart-total-price');
        
        // Clear existing items
        cartItems.innerHTML = '';
        
        if (this.cart.length === 0) {
            cartItems.innerHTML = '<div class="empty-cart-message">Your cart is empty</div>';
            cartTotal.textContent = '£0.00';
            return;
        }
        
        let total = 0;
        
        // Add each item
        this.cart.forEach((item, index) => {
            const itemTotal = item.price * item.quantity;
            total += itemTotal;
            
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-image" style="background-image: url('https://picsum.photos/200/200?random=${index + 5}')"></div>
                <div class="cart-item-details">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">£${item.price.toFixed(2)}</div>
                </div>
                <div class="cart-item-actions">
                    <div class="cart-item-quantity">
                        <button class="cart-decrease-btn" data-index="${index}">-</button>
                        <span>${item.quantity}</span>
                        <button class="cart-increase-btn" data-index="${index}">+</button>
                    </div>
                    <button class="cart-item-remove" data-index="${index}">&times;</button>
                </div>
            `;
            
            cartItems.appendChild(cartItem);
            
            // Add event listeners
            const decreaseBtn = cartItem.querySelector('.cart-decrease-btn');
            decreaseBtn.addEventListener('click', () => {
                this.decreaseQuantity(index);
            });
            
            const increaseBtn = cartItem.querySelector('.cart-increase-btn');
            increaseBtn.addEventListener('click', () => {
                this.increaseQuantity(index);
            });
            
            const removeBtn = cartItem.querySelector('.cart-item-remove');
            removeBtn.addEventListener('click', () => {
                this.removeItem(index);
            });
        });
        
        // Update total
        cartTotal.textContent = `£${total.toFixed(2)}`;
    }
    
    addToCart(id, name, price, quantity = 1) {
        const existingItem = this.cart.find(item => item.id === id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id,
                name,
                price,
                quantity
            });
        }
        
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartCount();
        
        // Show notification
        showNotification('Added to Cart', `${name} has been added to your cart.`);
        
        // Add animation to cart icon
        this.cartCount.classList.add('animate__animated', 'animate__rubberBand');
        setTimeout(() => {
            this.cartCount.classList.remove('animate__animated', 'animate__rubberBand');
        }, 1000);
    }
    
    decreaseQuantity(index) {
        if (this.cart[index].quantity > 1) {
            this.cart[index].quantity--;
            localStorage.setItem('cart', JSON.stringify(this.cart));
            this.updateCartCount();
            this.updateCartItems();
        } else {
            this.removeItem(index);
        }
    }
    
    increaseQuantity(index) {
        this.cart[index].quantity++;
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartCount();
        this.updateCartItems();
    }
    
    removeItem(index) {
        const removedItem = this.cart[index];
        this.cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartCount();
        this.updateCartItems();
        
        // Show notification
        showNotification('Item Removed', `${removedItem.name} has been removed from your cart.`);
    }
    
    clearCart() {
        this.cart = [];
        localStorage.setItem('cart', JSON.stringify(this.cart));
        this.updateCartCount();
        this.updateCartItems();
        
        // Show notification
        showNotification('Cart Cleared', 'All items have been removed from your cart.');
    }
    
    checkout() {
        if (this.cart.length === 0) {
            showNotification('Empty Cart', 'Your cart is empty. Add some items before checking out.');
            return;
        }
        
        // For now, just redirect to a hypothetical checkout page
        showNotification('Checkout', 'Proceeding to checkout... (This would normally redirect to a checkout page)');
        
        // In a real implementation, this would redirect to a checkout page
        // window.location.href = 'checkout.html';
    }
    
    updateCartCount() {
        this.cartCount.textContent = this.cart.reduce((sum, item) => sum + item.quantity, 0);
    }
}

// Initialize the shopping cart when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new ShoppingCart();
});