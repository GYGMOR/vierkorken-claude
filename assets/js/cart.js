// assets/js/cart.js
// Warenkorb-Verwaltung mit localStorage

console.log('🛒 Warenkorb Script geladen!');

class ShoppingCart {
    constructor() {
        this.storageKey = 'vier_korken_cart';
        this.loadCart();
    }
    
    loadCart() {
        const saved = localStorage.getItem(this.storageKey);
        this.items = saved ? JSON.parse(saved) : [];
        console.log('🛒 Warenkorb geladen:', this.items);
    }
    
    saveCart() {
        localStorage.setItem(this.storageKey, JSON.stringify(this.items));
        window.dispatchEvent(new Event('cartUpdated'));
        updateCartCount();
    }
    
    addItem(wineId, wineName, price, quantity = 1) {
        wineId = parseInt(wineId);
        price = parseFloat(price);
        quantity = parseInt(quantity) || 1;
        
        const existing = this.items.find(item => item.id === wineId);
        
        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({
                id: wineId,
                name: wineName,
                price: price,
                quantity: quantity
            });
        }
        
        this.saveCart();
        showNotification(`${wineName} zum Warenkorb hinzugefügt! ✅`);
        console.log('✅ Artikel hinzugefügt:', wineName);
    }
    
    removeItem(wineId) {
        wineId = parseInt(wineId);
        this.items = this.items.filter(item => item.id !== wineId);
        this.saveCart();
        showNotification('Artikel aus Warenkorb entfernt');
    }
    
    updateQuantity(wineId, quantity) {
        wineId = parseInt(wineId);
        quantity = parseInt(quantity);
        
        const item = this.items.find(i => i.id === wineId);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(wineId);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }
    
    clearCart() {
        this.items = [];
        this.saveCart();
        showNotification('Warenkorb geleert');
    }
    
    getTotalPrice() {
        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    }
    
    getTotalItems() {
        return this.items.reduce((sum, item) => sum + item.quantity, 0);
    }
    
    toJSON() {
        return {
            items: this.items,
            total: this.getTotalPrice(),
            count: this.getTotalItems(),
            timestamp: new Date().toISOString()
        };
    }
}

const cart = new ShoppingCart();

function addToCart(wineId, wineName, price, quantity) {
    quantity = parseInt(quantity) || 1;
    cart.addItem(wineId, wineName, price, quantity);
}

function formatCHF(amount) {
    return new Intl.NumberFormat('de-CH', {
        style: 'currency',
        currency: 'CHF'
    }).format(amount);
}

function updateCartCount() {
    const count = cart.getTotalItems();
    const cartBadge = document.getElementById('cart-count');
    
    if (cartBadge) {
        cartBadge.innerText = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerText = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        z-index: 10000;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    `;
    
    document.body.appendChild(notification);
    setTimeout(() => notification.remove(), 3000);
}

function renderCartPage() {
    const container = document.getElementById('cart-items-container');
    
    if (!container) return;
    
    if (cart.items.length === 0) {
        container.innerHTML = `
            <div class="cart-empty">
                <div class="cart-empty-emoji">😢</div>
                <h3>Ihr Warenkorb ist leer</h3>
                <p>Entdecken Sie unsere Weinauswahl!</p>
                <a href="?page=shop" class="btn btn-primary" style="margin-top: 1rem;">
                    🛍️ Zum Shop
                </a>
            </div>
        `;
        updateSummary();
        return;
    }

    let html = '<div class="cart-items-list">';
    
    cart.items.forEach(item => {
        const subtotal = item.price * item.quantity;
        html += `
            <div class="cart-item-row">
                <div class="cart-item-info">
                    <h4>${item.name}</h4>
                    <p class="cart-item-price">CHF ${item.price.toFixed(2)} pro Stück</p>
                </div>
                
                <div class="cart-item-quantity">
                    <button onclick="decreaseQuantity(${item.id})" style="padding: 0.3rem 0.6rem; border: none; background: #f0f0f0; cursor: pointer; border-radius: 3px;">−</button>
                    <input type="number" 
                           value="${item.quantity}" 
                           min="1" 
                           max="99"
                           class="quantity-input"
                           onchange="cart.updateQuantity(${item.id}, parseInt(this.value)); renderCartPage();">
                    <button onclick="increaseQuantity(${item.id})" style="padding: 0.3rem 0.6rem; border: none; background: #f0f0f0; cursor: pointer; border-radius: 3px;">+</button>
                </div>
                
                <div class="cart-item-subtotal">
                    CHF ${subtotal.toFixed(2)}
                </div>
                
                <button class="cart-item-delete" onclick="cart.removeItem(${item.id}); renderCartPage();">
                    ✕ Löschen
                </button>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
    updateSummary();
}

function increaseQuantity(wineId) {
    const item = cart.items.find(i => i.id === wineId);
    if (item && item.quantity < 99) {
        item.quantity++;
        cart.saveCart();
        renderCartPage();
    }
}

function decreaseQuantity(wineId) {
    const item = cart.items.find(i => i.id === wineId);
    if (item && item.quantity > 1) {
        item.quantity--;
        cart.saveCart();
        renderCartPage();
    }
}

function updateSummary() {
    const subtotal = cart.getTotalPrice();
    const shipping = 15.00;
    const total = subtotal + shipping;
    
    const subtotalEl = document.getElementById('subtotal');
    const totalEl = document.getElementById('total');
    
    if (subtotalEl) subtotalEl.innerText = 'CHF ' + subtotal.toFixed(2);
    if (totalEl) totalEl.innerText = 'CHF ' + total.toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    renderCartPage();
    updateCartCount();
});

window.addEventListener('cartUpdated', function() {
    renderCartPage();
    updateCartCount();
});

window.addEventListener('storage', function(e) {
    renderCartPage();
    updateCartCount();
});

console.log('✅ Warenkorb-System aktiv!');