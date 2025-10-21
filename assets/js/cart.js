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

        const existing = this.items.find(item => item.id === wineId && item.type === 'wine');

        if (existing) {
            existing.quantity += quantity;
        } else {
            this.items.push({
                id: wineId,
                name: wineName,
                price: price,
                quantity: quantity,
                type: 'wine'
            });
        }

        this.saveCart();
        showNotification(`${wineName} zum Warenkorb hinzugefügt! ✅`);
        console.log('✅ Artikel hinzugefügt:', wineName);
    }

    addEventTicket(eventId, eventName, price, quantity = 1, customerData = {}) {
        eventId = parseInt(eventId);
        price = parseFloat(price);
        quantity = parseInt(quantity) || 1;

        // Events cannot be combined - each booking is unique
        this.items.push({
            id: eventId,
            name: eventName,
            price: price,
            quantity: quantity,
            type: 'event',
            customerData: customerData
        });

        this.saveCart();
        showNotification(`${quantity} Event-Ticket(s) zum Warenkorb hinzugefügt! ✅`);
        console.log('✅ Event-Ticket hinzugefügt:', eventName);
    }
    
    removeItem(itemId, itemType = 'wine') {
        itemId = parseInt(itemId);
        this.items = this.items.filter(item => !(item.id === itemId && item.type === itemType));
        this.saveCart();
        showNotification('Artikel aus Warenkorb entfernt');
    }

    removeItemByIndex(index) {
        this.items.splice(index, 1);
        this.saveCart();
        showNotification('Artikel aus Warenkorb entfernt');
    }

    updateQuantity(itemId, quantity, itemType = 'wine') {
        itemId = parseInt(itemId);
        quantity = parseInt(quantity);

        const item = this.items.find(i => i.id === itemId && i.type === itemType);
        if (item) {
            if (quantity <= 0) {
                this.removeItem(itemId, itemType);
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

    cart.items.forEach((item, index) => {
        const subtotal = item.price * item.quantity;
        const itemType = item.type || 'wine';
        const isEvent = itemType === 'event';

        html += `
            <div class="cart-item-row ${isEvent ? 'cart-item-event' : ''}">
                <div class="cart-item-info">
                    <div class="cart-item-type-badge ${itemType}">${isEvent ? '🎫 Event-Ticket' : '🍷 Wein'}</div>
                    <h4>${item.name}</h4>
                    <p class="cart-item-price">CHF ${item.price.toFixed(2)} pro ${isEvent ? 'Ticket' : 'Stück'}</p>
                    ${isEvent && item.customerData ? `
                        <div class="cart-event-details">
                            <small>👤 ${item.customerData.customer_name || ''}</small><br>
                            <small>📧 ${item.customerData.customer_email || ''}</small>
                        </div>
                    ` : ''}
                </div>

                <div class="cart-item-quantity">
                    ${!isEvent ? `
                        <button onclick="decreaseQuantity(${index})" style="padding: 0.3rem 0.6rem; border: none; background: #f0f0f0; cursor: pointer; border-radius: 3px;">−</button>
                        <input type="number"
                               value="${item.quantity}"
                               min="1"
                               max="99"
                               class="quantity-input"
                               onchange="cart.updateQuantity(${item.id}, parseInt(this.value), '${itemType}'); renderCartPage();">
                        <button onclick="increaseQuantity(${index})" style="padding: 0.3rem 0.6rem; border: none; background: #f0f0f0; cursor: pointer; border-radius: 3px;">+</button>
                    ` : `
                        <span class="event-quantity-label">${item.quantity} Ticket(s)</span>
                    `}
                </div>

                <div class="cart-item-subtotal">
                    CHF ${subtotal.toFixed(2)}
                </div>

                <button class="cart-item-delete" onclick="cart.removeItemByIndex(${index}); renderCartPage();">
                    ✕ Löschen
                </button>
            </div>
        `;
    });

    html += '</div>';
    container.innerHTML = html;
    updateSummary();
}

function increaseQuantity(index) {
    const item = cart.items[index];
    if (item && item.quantity < 99 && item.type !== 'event') {
        item.quantity++;
        cart.saveCart();
        renderCartPage();
    }
}

function decreaseQuantity(index) {
    const item = cart.items[index];
    if (item && item.quantity > 1 && item.type !== 'event') {
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