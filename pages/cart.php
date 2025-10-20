<?php
// pages/cart.php - Warenkorb-Seite mit korrigiertem Layout
?>

<div class="cart-page">
    <div class="cart-header">
        <h1>🛒 Ihr Warenkorb</h1>
    </div>

    <!-- Warenkorb Inhalt -->
    <div class="cart-content">
        <div id="cart-items-container" class="cart-items-container">
            <div style="text-align: center; padding: 3rem;">
                <p>⏳ Warenkorb wird geladen...</p>
            </div>
        </div>

        <!-- Zusammenfassung & Checkout -->
        <aside class="cart-summary-sidebar">
            <div class="summary-box">
                <h3>Zusammenfassung</h3>
                
                <div class="summary-item">
                    <span>Zwischensumme:</span>
                    <span id="subtotal">CHF 0.00</span>
                </div>
                
                <div class="summary-item">
                    <span>Versand:</span>
                    <span id="shipping">CHF 15.00</span>
                </div>
                
                <div class="summary-divider"></div>
                
                <div class="summary-item total">
                    <span>Gesamtbetrag:</span>
                    <span id="total">CHF 0.00</span>
                </div>
                
                <button class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; padding: 1rem;">
                    ➜ Zur Kasse
                </button>
                
                <a href="?page=shop" class="btn btn-secondary" style="width: 100%; margin-top: 0.8rem; padding: 1rem; text-align: center; display: block;">
                    Weiter einkaufen
                </a>
                
                <button onclick="cart.clearCart(); renderCartPage();" 
                        class="btn btn-secondary" 
                        style="width: 100%; margin-top: 0.8rem; padding: 1rem; background: #ffebee; color: #c0392b;">
                    🗑️ Warenkorb leeren
                </button>
            </div>

            <div class="info-box">
                <h4>📦 Versand</h4>
                <p>Versandkosten: <strong>CHF 15.00</strong></p>
                <p><small>Lieferzeit: 2-3 Arbeitstage</small></p>
            </div>

            <div class="info-box">
                <h4>🔒 Sicherheit</h4>
                <p><small>✅ Sichere Zahlung mit SSL<br>✅ Datenschutz gewährleistet<br>✅ Kostenlose Rücksendung</small></p>
            </div>
        </aside>
    </div>
</div>

<style>
.cart-page {
    padding: 2rem 0;
}

.cart-header {
    margin-bottom: 2rem;
}

.cart-header h1 {
    border-bottom: 3px solid var(--accent-gold);
    padding-bottom: 1rem;
}

.cart-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
}

.cart-items-container {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    min-height: 300px;
}

.cart-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* CART ITEM ROW - Das Wichtigste */
.cart-item-row {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
    border-left: 3px solid var(--primary-color);
}

.cart-item-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
}

.cart-item-info h4 {
    margin: 0 0 0.3rem 0;
    font-size: 1rem;
    color: var(--primary-color);
    line-height: 1.3;
}

.cart-item-price {
    color: var(--text-light);
    font-size: 0.9rem;
    margin: 0;
}

.cart-item-delete {
    padding: 0.4rem 0.8rem;
    background: #ffebee;
    color: #c0392b;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
    font-size: 0.85rem;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}

.cart-item-delete:hover {
    background: #e74c3c;
    color: white;
}

/* Quantity Controls */
.cart-item-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: space-between;
    padding: 0.8rem;
    background: white;
    border-radius: 6px;
}

.quantity-buttons {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

.quantity-buttons button {
    width: 32px;
    height: 32px;
    padding: 0;
    border: 2px solid var(--border-color);
    background: white;
    border-radius: 4px;
    cursor: pointer;
    font-size: 1rem;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quantity-buttons button:hover {
    border-color: var(--primary-color);
    background: var(--bg-light);
}

.quantity-input {
    width: 45px;
    padding: 0.5rem;
    border: 2px solid var(--border-color);
    border-radius: 4px;
    text-align: center;
    font-weight: 600;
    font-size: 0.95rem;
}

.quantity-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.cart-item-subtotal {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 1.1rem;
    text-align: right;
    min-width: 80px;
}

/* SUMMARY */
.cart-summary-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.summary-box,
.info-box {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.summary-box h3,
.info-box h4 {
    margin-top: 0;
    color: var(--primary-color);
}

.summary-item {
    display: flex;
    justify-content: space-between;
    padding: 0.8rem 0;
    border-bottom: 1px solid var(--border-color);
    gap: 1rem;
}

.summary-item span:first-child {
    flex: 1;
}

.summary-item span:last-child {
    text-align: right;
    white-space: nowrap;
}

.summary-item.total {
    border-bottom: none;
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    padding-top: 1rem;
}

.summary-divider {
    height: 2px;
    background: var(--border-color);
    margin: 1rem 0;
}

.info-box p {
    margin: 0.5rem 0;
    font-size: 0.9rem;
    color: var(--text-light);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .cart-content {
        grid-template-columns: 1fr 300px;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }

    .cart-summary-sidebar {
        order: -1;
    }

    .cart-item-header {
        flex-direction: column;
        gap: 0.5rem;
    }

    .cart-item-delete {
        width: 100%;
        text-align: center;
    }

    .cart-item-controls {
        padding: 0.6rem;
    }

    .quantity-input {
        width: 50px;
    }
}

@media (max-width: 480px) {
    .cart-items-container {
        padding: 1rem;
    }

    .cart-item-row {
        padding: 0.8rem;
        gap: 0.6rem;
    }

    .cart-item-info h4 {
        font-size: 0.95rem;
    }

    .cart-item-price {
        font-size: 0.85rem;
    }

    .quantity-buttons button {
        width: 28px;
        height: 28px;
        font-size: 0.9rem;
    }

    .quantity-input {
        width: 40px;
        font-size: 0.9rem;
    }

    .cart-item-subtotal {
        font-size: 1rem;
    }

    .summary-item {
        padding: 0.6rem 0;
        font-size: 0.95rem;
    }

    .summary-item.total {
        font-size: 1.1rem;
    }
}
</style>

<script>
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
                <div class="cart-item-header">
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        <p class="cart-item-price">CHF ${item.price.toFixed(2)} pro Stück</p>
                    </div>
                    <button class="cart-item-delete" onclick="cart.removeItem(${item.id}); renderCartPage();">✕ Löschen</button>
                </div>
                
                <div class="cart-item-controls">
                    <div class="quantity-buttons">
                        <button onclick="decreaseQuantity(${item.id})">−</button>
                        <input type="number" value="${item.quantity}" min="1" max="99" class="quantity-input" onchange="cart.updateQuantity(${item.id}, parseInt(this.value)); renderCartPage();">
                        <button onclick="increaseQuantity(${item.id})">+</button>
                    </div>
                    <div class="cart-item-subtotal">CHF ${subtotal.toFixed(2)}</div>
                </div>
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
</script>