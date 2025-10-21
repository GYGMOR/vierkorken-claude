<?php
// pages/order-confirmation.php - Order Confirmation Page
$order_number = $_GET['order'] ?? '';

if (empty($order_number)) {
    header('Location: ?page=home');
    exit;
}

// Fetch order details
$order_number_safe = $db->real_escape_string($order_number);
$result = $db->query("SELECT * FROM orders WHERE order_number = '$order_number_safe'");

if (!$result || $result->num_rows === 0) {
    header('Location: ?page=home');
    exit;
}

$order = $result->fetch_assoc();

// Fetch order items
$items_result = $db->query("SELECT * FROM order_items WHERE order_id = " . $order['id']);
$order_items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

$delivery_labels = [
    'delivery' => 'Lieferung',
    'pickup' => 'Abholung in der Filiale'
];

$payment_labels = [
    'card' => 'Kreditkarte / Debitkarte',
    'twint' => 'TWINT',
    'cash' => 'Barzahlung'
];
?>

<div class="order-confirmation-page">
    <div class="container">
        <!-- SUCCESS BANNER -->
        <div class="success-banner">
            <div class="success-icon">
                <?php echo get_icon('check', 48); ?>
            </div>
            <h1>Vielen Dank für deine Bestellung!</h1>
            <p class="success-subtitle">Deine Bestellung wurde erfolgreich aufgegeben</p>
            <div class="order-number-display">
                <span class="label">Bestellnummer:</span>
                <span class="number"><?php echo safe_output($order['order_number']); ?></span>
            </div>
        </div>

        <!-- ORDER DETAILS GRID -->
        <div class="order-details-grid">
            <!-- LEFT: Order Items -->
            <div class="order-section">
                <h2><?php echo get_icon('package', 20); ?> Bestellte Artikel</h2>

                <div class="order-items-list">
                    <?php foreach ($order_items as $item): ?>
                        <div class="order-item-card">
                            <div class="item-icon">
                                <?php echo $item['item_type'] === 'event' ? '🎫' : '🍷'; ?>
                            </div>
                            <div class="item-details">
                                <h4><?php echo safe_output($item['product_name']); ?></h4>
                                <p class="item-quantity">Menge: <?php echo $item['quantity']; ?>x</p>
                                <p class="item-unit-price">Einzelpreis: CHF <?php echo number_format($item['unit_price'], 2); ?></p>
                            </div>
                            <div class="item-total">
                                <strong>CHF <?php echo number_format($item['total_price'], 2); ?></strong>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Price Summary -->
                <div class="price-summary">
                    <div class="summary-row">
                        <span>Zwischensumme:</span>
                        <span>CHF <?php echo number_format($order['subtotal'], 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Versandkosten:</span>
                        <span><?php echo $order['shipping_cost'] > 0 ? 'CHF ' . number_format($order['shipping_cost'], 2) : 'Kostenlos'; ?></span>
                    </div>
                    <?php if ($order['discount_amount'] > 0): ?>
                    <div class="summary-row discount">
                        <span>Rabatt (<?php echo safe_output($order['coupon_code']); ?>):</span>
                        <span>-CHF <?php echo number_format($order['discount_amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="summary-divider"></div>
                    <div class="summary-row total">
                        <span><strong>Gesamtbetrag:</strong></span>
                        <span><strong>CHF <?php echo number_format($order['total_amount'], 2); ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Delivery & Payment Info -->
            <div class="order-section">
                <h2><?php echo get_icon('truck', 20); ?> Lieferinformationen</h2>

                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">Methode:</span>
                        <span class="info-value"><?php echo $delivery_labels[$order['delivery_method']]; ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo safe_output($order['delivery_first_name'] . ' ' . $order['delivery_last_name']); ?></span>
                    </div>

                    <?php if ($order['delivery_method'] === 'delivery'): ?>
                    <div class="info-row">
                        <span class="info-label">Adresse:</span>
                        <span class="info-value">
                            <?php echo safe_output($order['delivery_street']); ?><br>
                            <?php echo safe_output($order['delivery_postal_code'] . ' ' . $order['delivery_city']); ?>
                        </span>
                    </div>
                    <?php else: ?>
                    <div class="info-row">
                        <span class="info-label">Abholung:</span>
                        <span class="info-value">
                            Vier Korken Weinlounge<br>
                            Zürich
                        </span>
                    </div>
                    <?php endif; ?>

                    <div class="info-row">
                        <span class="info-label">Telefon:</span>
                        <span class="info-value"><?php echo safe_output($order['delivery_phone']); ?></span>
                    </div>

                    <div class="info-row">
                        <span class="info-label">E-Mail:</span>
                        <span class="info-value"><?php echo safe_output($order['delivery_email']); ?></span>
                    </div>
                </div>

                <h2 style="margin-top: 2rem;"><?php echo get_icon('credit-card', 20); ?> Zahlungsinformationen</h2>

                <div class="info-card">
                    <div class="info-row">
                        <span class="info-label">Zahlungsmethode:</span>
                        <span class="info-value"><?php echo $payment_labels[$order['payment_method']]; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status:</span>
                        <span class="info-value">
                            <span class="status-badge status-pending">Ausstehend</span>
                        </span>
                    </div>
                </div>

                <div class="info-note">
                    <p><strong>Hinweis:</strong> Die Zahlungsintegration wird in Kürze verfügbar sein. Du erhältst eine E-Mail mit weiteren Anweisungen.</p>
                </div>
            </div>
        </div>

        <!-- CONFIRMATION EMAIL NOTICE -->
        <div class="email-confirmation-notice">
            <div class="notice-icon"><?php echo get_icon('mail', 24); ?></div>
            <div class="notice-content">
                <h3>Bestätigung per E-Mail</h3>
                <p>Eine Bestellbestätigung wurde an <strong><?php echo safe_output($order['delivery_email']); ?></strong> gesendet.</p>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="confirmation-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="?page=order-history" class="btn btn-primary">
                    <?php echo get_icon('list', 18); ?> Meine Bestellungen
                </a>
            <?php endif; ?>
            <a href="?page=shop" class="btn btn-secondary">
                <?php echo get_icon('arrow-right', 18); ?> Weiter einkaufen
            </a>
            <a href="?page=home" class="btn btn-secondary">
                <?php echo get_icon('home', 18); ?> Zur Startseite
            </a>
        </div>
    </div>
</div>

<style>
.order-confirmation-page {
    padding: 2rem 0;
    min-height: 70vh;
}

.success-banner {
    background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 15px;
    text-align: center;
    margin-bottom: 3rem;
    box-shadow: 0 4px 20px rgba(39, 174, 96, 0.3);
}

.success-icon {
    width: 80px;
    height: 80px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
}

.success-banner h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2.5rem;
    color: white;
    border: none;
}

.success-subtitle {
    font-size: 1.1rem;
    margin: 0 0 2rem 0;
    opacity: 0.95;
}

.order-number-display {
    background: rgba(255,255,255,0.15);
    padding: 1rem 2rem;
    border-radius: 8px;
    display: inline-block;
    backdrop-filter: blur(10px);
}

.order-number-display .label {
    font-size: 0.9rem;
    opacity: 0.9;
    margin-right: 0.5rem;
}

.order-number-display .number {
    font-size: 1.3rem;
    font-weight: 700;
    letter-spacing: 1px;
}

.order-details-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

.order-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.order-section h2 {
    color: var(--primary-color);
    margin-top: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    border-bottom: 2px solid var(--bg-light);
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
}

.order-items-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin-bottom: 2rem;
}

.order-item-card {
    display: grid;
    grid-template-columns: 50px 1fr auto;
    gap: 1rem;
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
    align-items: center;
}

.item-icon {
    font-size: 2rem;
    text-align: center;
}

.item-details h4 {
    margin: 0 0 0.5rem 0;
    color: var(--text-dark);
}

.item-details p {
    margin: 0.3rem 0;
    color: var(--text-light);
    font-size: 0.9rem;
}

.item-total {
    text-align: right;
    color: var(--primary-color);
    font-size: 1.1rem;
}

.price-summary {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    color: var(--text-dark);
}

.summary-row.discount {
    color: #27ae60;
}

.summary-row.total {
    font-size: 1.3rem;
    color: var(--primary-color);
    padding-top: 1rem;
}

.summary-divider {
    height: 2px;
    background: #e0e0e0;
    margin: 1rem 0;
}

.info-card {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.info-row {
    display: grid;
    grid-template-columns: 130px 1fr;
    gap: 1rem;
    padding: 0.8rem 0;
    border-bottom: 1px solid #e0e0e0;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-light);
}

.info-value {
    color: var(--text-dark);
}

.status-badge {
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.info-note {
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    padding: 1rem;
    border-radius: 5px;
    margin-top: 1rem;
}

.info-note p {
    margin: 0;
    color: #856404;
}

.email-confirmation-notice {
    background: #e3f2fd;
    border-left: 4px solid #2196F3;
    padding: 1.5rem;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.notice-icon {
    width: 50px;
    height: 50px;
    background: #2196F3;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.notice-content h3 {
    margin: 0 0 0.5rem 0;
    color: #1976D2;
}

.notice-content p {
    margin: 0;
    color: #0c5460;
}

.confirmation-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.confirmation-actions .btn {
    padding: 0.8rem 2rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .order-details-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .success-banner {
        padding: 2rem 1rem;
    }

    .success-banner h1 {
        font-size: 1.8rem;
    }

    .order-section {
        padding: 1.5rem;
    }

    .order-item-card {
        grid-template-columns: 40px 1fr;
    }

    .item-total {
        grid-column: 2;
        text-align: left;
        margin-top: 0.5rem;
    }

    .info-row {
        grid-template-columns: 1fr;
        gap: 0.3rem;
    }

    .confirmation-actions {
        flex-direction: column;
    }

    .confirmation-actions .btn {
        width: 100%;
    }
}

/* Account Creation Modal */
.account-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.account-modal-backdrop.active {
    opacity: 1;
}

.account-modal {
    background: white;
    border-radius: 15px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.account-modal-backdrop.active .account-modal {
    transform: scale(1);
}

.account-modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 2.5rem 2rem 2rem;
    text-align: center;
}

.modal-icon {
    font-size: 3rem;
    margin-bottom: 0.5rem;
}

.account-modal-header h2 {
    margin: 0 0 0.5rem 0;
    font-size: 1.8rem;
    border: none;
    color: white;
}

.modal-subtitle {
    margin: 0;
    opacity: 0.95;
    font-size: 1rem;
}

.account-modal-body {
    padding: 2rem;
}

.benefits-list {
    list-style: none;
    padding: 0;
    margin: 0 0 1.5rem 0;
}

.benefits-list li {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.8rem 0;
    font-size: 1.05rem;
    border-bottom: 1px solid #e0e0e0;
}

.benefits-list li:last-child {
    border-bottom: none;
}

.benefit-icon {
    font-size: 1.5rem;
    width: 35px;
    text-align: center;
}

.modal-note {
    background: #e8f5e9;
    padding: 1rem;
    border-radius: 8px;
    margin: 0;
    text-align: center;
    color: #2e7d32;
}

.account-modal-actions {
    display: flex;
    gap: 1rem;
    padding: 0 2rem 2rem;
}

.account-modal-actions .btn {
    flex: 1;
    padding: 1rem;
    font-size: 1rem;
}

@media (max-width: 600px) {
    .account-modal {
        width: 95%;
    }

    .account-modal-actions {
        flex-direction: column;
    }

    .account-modal-actions .btn {
        width: 100%;
    }
}
</style>

<script>
// Check if we should offer account creation
document.addEventListener('DOMContentLoaded', function() {
    const offerAccount = sessionStorage.getItem('offer_account_creation');
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    if (offerAccount === '1' && !isLoggedIn) {
        // Clear the flag
        sessionStorage.removeItem('offer_account_creation');

        // Show account creation modal after a short delay
        setTimeout(function() {
            showAccountCreationModal();
        }, 1500);
    }
});

// Show styled account creation modal
function showAccountCreationModal() {
    const modal = document.createElement('div');
    modal.className = 'account-modal-backdrop';
    modal.innerHTML = `
        <div class="account-modal">
            <div class="account-modal-header">
                <div class="modal-icon">✨</div>
                <h2>Konto erstellen?</h2>
                <p class="modal-subtitle">Verwalte deine Bestellungen ganz einfach!</p>
            </div>
            <div class="account-modal-body">
                <ul class="benefits-list">
                    <li>
                        <span class="benefit-icon">📦</span>
                        <span>Deine Bestellungen verfolgen</span>
                    </li>
                    <li>
                        <span class="benefit-icon">📍</span>
                        <span>Adressen speichern</span>
                    </li>
                    <li>
                        <span class="benefit-icon">⚡</span>
                        <span>Schneller bestellen</span>
                    </li>
                    <li>
                        <span class="benefit-icon">🎉</span>
                        <span>Exklusive Angebote erhalten</span>
                    </li>
                </ul>
                <p class="modal-note"><strong>Deine Daten werden automatisch übernommen!</strong></p>
            </div>
            <div class="account-modal-actions">
                <button onclick="closeAccountModal()" class="btn btn-secondary">Zurzeit nicht</button>
                <button onclick="proceedToRegister()" class="btn btn-primary">Jetzt Konto erstellen</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    // Add fade-in animation
    setTimeout(() => modal.classList.add('active'), 10);
}

function closeAccountModal() {
    const modal = document.querySelector('.account-modal-backdrop');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.remove(), 300);
    }
}

function proceedToRegister() {
    window.location.href = '?page=register-after-order';
}
</script>
