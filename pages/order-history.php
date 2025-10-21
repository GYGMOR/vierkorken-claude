<?php
// pages/order-history.php - User Order History
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=home&modal=login');
    exit;
}
?>

<div class="order-history-page">
    <div class="container">
        <h1>Meine Bestellungen</h1>

        <div id="orders-list" class="orders-list">
            <p class="loading-text">Lade Bestellungen...</p>
        </div>
    </div>
</div>

<style>
.order-history-page {
    padding: 2rem 0;
    min-height: 70vh;
}

.order-history-page h1 {
    color: var(--primary-color);
    border-bottom: 3px solid var(--accent-gold);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.loading-text {
    text-align: center;
    color: var(--text-light);
    padding: 2rem;
}

.order-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
    border-left: 5px solid var(--primary-color);
    transition: transform 0.3s, box-shadow 0.3s;
}

.order-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}

.order-header {
    padding: 1.5rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    border-bottom: 2px solid #e0e0e0;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: center;
}

.order-number {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0 0 0.5rem 0;
}

.order-date {
    color: var(--text-light);
    font-size: 0.9rem;
    margin: 0;
}

.order-status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.status-pending { background: #fff3cd; color: #856404; }
.status-processing { background: #cfe2ff; color: #084298; }
.status-shipped { background: #d1ecf1; color: #0c5460; }
.status-delivered { background: #d4edda; color: #155724; }
.status-cancelled { background: #f8d7da; color: #721c24; }

.order-body {
    padding: 1.5rem;
}

.order-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.summary-label {
    font-size: 0.85rem;
    color: var(--text-light);
    text-transform: uppercase;
    font-weight: 600;
}

.summary-value {
    font-size: 1.1rem;
    color: var(--text-dark);
    font-weight: 600;
}

.order-items {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid var(--bg-light);
}

.order-items h4 {
    margin: 0 0 1rem 0;
    color: var(--primary-color);
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem;
    background: var(--bg-light);
    border-radius: 6px;
    margin-bottom: 0.8rem;
}

.item-info {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.item-name {
    font-weight: 600;
    color: var(--text-dark);
}

.item-quantity {
    font-size: 0.85rem;
    color: var(--text-light);
}

.item-price {
    font-weight: 600;
    color: var(--primary-color);
    text-align: right;
}

.order-totals {
    margin-top: 1.5rem;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    font-size: 0.95rem;
}

.total-row.grand-total {
    font-size: 1.3rem;
    font-weight: 700;
    color: var(--primary-color);
    border-top: 2px solid #d0d0d0;
    margin-top: 0.5rem;
    padding-top: 1rem;
}

.order-actions {
    margin-top: 1.5rem;
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 10px;
}

.empty-state-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
    opacity: 0.3;
}

.empty-state h2 {
    color: var(--text-light);
    margin: 0 0 1rem 0;
}

.empty-state p {
    color: var(--text-light);
    margin-bottom: 2rem;
}

@media (max-width: 768px) {
    .order-header {
        grid-template-columns: 1fr;
    }

    .order-summary {
        grid-template-columns: 1fr;
    }

    .order-actions {
        flex-direction: column;
    }

    .order-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Load user orders
function loadOrders() {
    fetch('api/orders.php?action=get_user_orders')
        .then(r => r.json())
        .then(d => {
            const container = document.getElementById('orders-list');

            if (!d.success) {
                container.innerHTML = '<p class="loading-text" style="color: #c62828;">Fehler beim Laden der Bestellungen</p>';
                return;
            }

            if (d.orders.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">📦</div>
                        <h2>Noch keine Bestellungen</h2>
                        <p>Du hast bisher noch keine Bestellungen aufgegeben.</p>
                        <a href="?page=shop" class="btn btn-primary">Zum Shop</a>
                    </div>
                `;
                return;
            }

            let html = '';
            d.orders.forEach(order => {
                html += renderOrderCard(order);
            });

            container.innerHTML = html;
        })
        .catch(e => {
            console.error('Fehler:', e);
            document.getElementById('orders-list').innerHTML =
                '<p class="loading-text" style="color: #c62828;">Fehler beim Laden</p>';
        });
}

function renderOrderCard(order) {
    const statusClass = 'status-' + order.order_status;
    const statusLabels = {
        'pending': 'Ausstehend',
        'processing': 'In Bearbeitung',
        'shipped': 'Versandt',
        'delivered': 'Zugestellt',
        'cancelled': 'Storniert'
    };

    const deliveryLabels = {
        'delivery': 'Lieferung',
        'pickup': 'Abholung'
    };

    const paymentLabels = {
        'card': 'Kreditkarte',
        'twint': 'TWINT',
        'cash': 'Bar'
    };

    return `
        <div class="order-card" onclick="viewOrderDetails(${order.id})">
            <div class="order-header">
                <div>
                    <h3 class="order-number">Bestellung ${order.order_number}</h3>
                    <p class="order-date">Bestellt am ${formatDate(order.created_at)}</p>
                </div>
                <span class="order-status-badge ${statusClass}">
                    ${statusLabels[order.order_status] || order.order_status}
                </span>
            </div>

            <div class="order-body">
                <div class="order-summary">
                    <div class="summary-item">
                        <span class="summary-label">Gesamtbetrag</span>
                        <span class="summary-value">CHF ${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Artikel</span>
                        <span class="summary-value">${order.item_count} Artikel</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Lieferung</span>
                        <span class="summary-value">${deliveryLabels[order.delivery_method] || order.delivery_method}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Zahlung</span>
                        <span class="summary-value">${paymentLabels[order.payment_method] || order.payment_method}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function viewOrderDetails(orderId) {
    // Fetch detailed order info
    fetch(`api/orders.php?action=get_order_details&id=${orderId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                alert('Fehler beim Laden der Bestelldetails');
                return;
            }

            showOrderModal(d.order);
        })
        .catch(e => {
            console.error('Fehler:', e);
            alert('Fehler beim Laden');
        });
}

function showOrderModal(order) {
    const statusLabels = {
        'pending': 'Ausstehend',
        'processing': 'In Bearbeitung',
        'shipped': 'Versandt',
        'delivered': 'Zugestellt',
        'cancelled': 'Storniert'
    };

    const deliveryLabels = {
        'delivery': 'Lieferung',
        'pickup': 'Abholung in der Filiale'
    };

    const paymentLabels = {
        'card': 'Kreditkarte / Debitkarte',
        'twint': 'TWINT',
        'cash': 'Barzahlung'
    };

    let itemsHtml = '';
    order.items.forEach(item => {
        const icon = item.item_type === 'event' ? 'Event' : 'Wein';
        itemsHtml += `
            <div class="order-item">
                <div class="item-info">
                    <div class="item-name">${icon} ${item.product_name}</div>
                    <div class="item-quantity">${item.quantity}x CHF ${parseFloat(item.unit_price).toFixed(2)}</div>
                </div>
                <div class="item-price">CHF ${parseFloat(item.total_price).toFixed(2)}</div>
            </div>
        `;
    });

    const addressHtml = order.delivery_method === 'delivery'
        ? `${order.delivery_street}<br>${order.delivery_postal_code} ${order.delivery_city}`
        : 'Abholung in der Filiale<br>Vier Korken Weinlounge, Zürich';

    const modalHtml = `
        <div class="modal-overlay" onclick="closeOrderModal()">
            <div class="modal-content order-details-modal" onclick="event.stopPropagation()">
                <button class="modal-close" onclick="closeOrderModal()">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>

                <h2>Bestelldetails</h2>

                <div style="background: var(--bg-light); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <p style="margin: 0;"><strong>Bestellnummer:</strong> ${order.order_number}</p>
                    <p style="margin: 0.5rem 0 0;"><strong>Datum:</strong> ${formatDate(order.created_at)}</p>
                    <p style="margin: 0.5rem 0 0;"><strong>Status:</strong> ${statusLabels[order.order_status]}</p>
                </div>

                <h3>Bestellte Artikel</h3>
                <div class="order-items">
                    ${itemsHtml}
                </div>

                <div class="order-totals">
                    <div class="total-row">
                        <span>Zwischensumme:</span>
                        <span>CHF ${parseFloat(order.subtotal).toFixed(2)}</span>
                    </div>
                    <div class="total-row">
                        <span>Versandkosten:</span>
                        <span>${order.shipping_cost > 0 ? 'CHF ' + parseFloat(order.shipping_cost).toFixed(2) : 'Kostenlos'}</span>
                    </div>
                    ${order.discount_amount > 0 ? `
                        <div class="total-row" style="color: #27ae60;">
                            <span>Rabatt (${order.coupon_code}):</span>
                            <span>-CHF ${parseFloat(order.discount_amount).toFixed(2)}</span>
                        </div>
                    ` : ''}
                    <div class="total-row grand-total">
                        <span>Gesamtbetrag:</span>
                        <span>CHF ${parseFloat(order.total_amount).toFixed(2)}</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 2rem;">
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Lieferung</h4>
                        <p style="color: var(--text-dark); margin: 0;">${deliveryLabels[order.delivery_method]}</p>
                        <p style="color: var(--text-light); font-size: 0.9rem; margin: 0.5rem 0 0;">
                            ${order.delivery_first_name} ${order.delivery_last_name}<br>
                            ${addressHtml}<br>
                            Tel: ${order.delivery_phone}
                        </p>
                    </div>
                    <div>
                        <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">Zahlung</h4>
                        <p style="color: var(--text-dark); margin: 0;">${paymentLabels[order.payment_method]}</p>
                    </div>
                </div>

                <div class="order-actions">
                    <button class="btn btn-secondary" onclick="closeOrderModal()">Schließen</button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeOrderModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) modal.remove();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString('de-CH', options);
}

// Initialize
document.addEventListener('DOMContentLoaded', loadOrders);
</script>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    padding: 1rem;
}

.modal-content {
    background: white;
    border-radius: 10px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    padding: 2rem;
}

.modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: transparent;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-light);
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s;
}

.modal-close:hover {
    background: var(--bg-light);
    color: var(--primary-color);
}

.order-details-modal h2 {
    color: var(--primary-color);
    margin-top: 0;
}

.order-details-modal h3,
.order-details-modal h4 {
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .modal-content {
        padding: 1.5rem;
    }

    .order-details-modal > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
