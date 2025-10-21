<?php
// pages/checkout.php - Multi-Step Checkout (Provisional)
$user_id = $_SESSION['user_id'] ?? 0;
$is_logged_in = isset($_SESSION['user_id']);

// Load user addresses if logged in
$user_addresses = [];
if ($is_logged_in) {
    $result = $db->query("SELECT * FROM user_addresses WHERE user_id = $user_id ORDER BY is_default DESC");
    if ($result) {
        $user_addresses = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div class="checkout-page">
    <div class="container">
        <h1>Zur Kasse</h1>

        <?php if (!$is_logged_in): ?>
        <div class="alert alert-info" style="margin-bottom: 1.5rem;">
            <p style="margin: 0;">
                <strong>Tipp:</strong> Du hast bereits ein Konto?
                <a href="?modal=login" class="link-button">Jetzt anmelden</a>
                um deine gespeicherten Adressen zu nutzen und schneller zu bestellen!
            </p>
        </div>
        <?php endif; ?>

        <div class="checkout-layout">
            <!-- LEFT SIDE - Checkout Steps -->
            <div class="checkout-main">

                <!-- STEP 1: DELIVERY METHOD -->
                <div class="checkout-step" id="step-delivery">
                    <div class="step-header">
                        <div class="step-number">1</div>
                        <h2>Lieferung</h2>
                    </div>

                    <div class="step-content">
                        <div class="delivery-options">
                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="delivery" checked onchange="updateDeliveryMethod()">
                                <div class="option-content">
                                    <div class="option-icon"><?php echo get_icon('truck', 24); ?></div>
                                    <div class="option-details">
                                        <h4>Lieferung</h4>
                                        <p>Versandkosten: CHF 15.00</p>
                                        <p class="text-small">Lieferzeit: 2-3 Arbeitstage</p>
                                    </div>
                                </div>
                            </label>

                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="pickup" onchange="updateDeliveryMethod()">
                                <div class="option-content">
                                    <div class="option-icon"><?php echo get_icon('map-pin', 24); ?></div>
                                    <div class="option-details">
                                        <h4>Abholen in der Filiale</h4>
                                        <p>Kostenlos</p>
                                        <p class="text-small">Vier Korken Weinlounge, Zürich</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: DELIVERY ADDRESS -->
                <div class="checkout-step" id="step-address">
                    <div class="step-header">
                        <div class="step-number">2</div>
                        <h2 id="address-step-title">Lieferadresse</h2>
                    </div>

                    <div class="step-content" id="address-content">
                        <?php if ($is_logged_in && count($user_addresses) > 0): ?>
                            <p class="info-text" id="address-info-text">Wähle eine gespeicherte Adresse oder füge eine neue hinzu:</p>

                            <div class="address-list">
                                <?php foreach ($user_addresses as $index => $addr): ?>
                                    <label class="address-option">
                                        <input type="radio" name="address_id" value="<?php echo $addr['id']; ?>"
                                               <?php echo $index === 0 ? 'checked' : ''; ?>>
                                        <div class="address-card">
                                            <?php if ($addr['is_default']): ?>
                                                <span class="badge-default">Standard</span>
                                            <?php endif; ?>
                                            <div><strong><?php echo safe_output($addr['first_name'] . ' ' . $addr['last_name']); ?></strong></div>
                                            <div><?php echo safe_output($addr['street']); ?></div>
                                            <div><?php echo safe_output($addr['postal_code'] . ' ' . $addr['city']); ?></div>
                                            <div><?php echo safe_output($addr['phone'] ?? ''); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            </div>

                            <button onclick="showAddAddressForm()" class="btn btn-secondary" style="margin-top: 1rem;">
                                <?php echo get_icon('plus', 18); ?> Neue Adresse hinzufügen
                            </button>
                        <?php else: ?>
                            <div class="alert alert-info" id="address-alert-text">
                                Bitte gib deine Lieferadresse ein:
                            </div>

                            <form id="new-address-form" class="address-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Vorname *</label>
                                        <input type="text" name="first_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nachname *</label>
                                        <input type="text" name="last_name" required>
                                    </div>
                                </div>

                                <div class="form-group" id="street-field">
                                    <label>Strasse & Hausnummer *</label>
                                    <input type="text" name="street" required>
                                </div>

                                <div class="form-row" id="postal-city-fields">
                                    <div class="form-group">
                                        <label>PLZ *</label>
                                        <input type="text" name="postal_code" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Ort *</label>
                                        <input type="text" name="city" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Telefon *</label>
                                    <input type="tel" name="phone" required>
                                </div>

                                <div class="form-group">
                                    <label>E-Mail *</label>
                                    <input type="email" name="email" required
                                           value="<?php echo $is_logged_in ? safe_output($_SESSION['email'] ?? '') : ''; ?>">
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- STEP 3: PAYMENT METHOD -->
                <div class="checkout-step" id="step-payment">
                    <div class="step-header">
                        <div class="step-number">3</div>
                        <h2>Zahlungsmethode</h2>
                    </div>

                    <div class="step-content">
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="card" checked>
                                <div class="option-content">
                                    <div class="option-icon"><?php echo get_icon('credit-card', 24); ?></div>
                                    <div class="option-details">
                                        <h4>Kreditkarte / Debitkarte</h4>
                                        <p class="text-small">Visa, Mastercard, American Express</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="twint">
                                <div class="option-content">
                                    <div class="option-icon" style="font-size: 1.5rem; font-weight: 700;">T</div>
                                    <div class="option-details">
                                        <h4>TWINT</h4>
                                        <p class="text-small">Bezahlen mit TWINT App</p>
                                    </div>
                                </div>
                            </label>

                            <label class="payment-option" id="payment-option-cash" style="display: none;">
                                <input type="radio" name="payment_method" value="cash">
                                <div class="option-content">
                                    <div class="option-icon"><?php echo get_icon('dollar-sign', 24); ?></div>
                                    <div class="option-details">
                                        <h4>Barzahlung in der Filiale</h4>
                                        <p class="text-small">Bei Abholung bezahlen</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: COUPON CODE -->
                <div class="checkout-step" id="step-coupon">
                    <div class="step-header">
                        <div class="step-number">4</div>
                        <h2>Gutschein / Rabattcode</h2>
                    </div>

                    <div class="step-content">
                        <form id="coupon-form" class="coupon-form">
                            <input type="text" name="coupon_code" id="coupon-code-input" placeholder="Gutscheincode eingeben">
                            <button type="submit" class="btn btn-primary">Einlösen</button>
                        </form>

                        <div id="coupon-result" style="margin-top: 1rem; display: none;"></div>
                    </div>
                </div>

            </div>

            <!-- RIGHT SIDE - Order Summary -->
            <aside class="checkout-sidebar">
                <div class="order-summary">
                    <h3>Bestellübersicht</h3>

                    <div id="order-items-list">
                        <p class="text-small text-light">Lade Warenkorb...</p>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row">
                        <span>Zwischensumme:</span>
                        <span id="summary-subtotal">CHF 0.00</span>
                    </div>

                    <div class="summary-row" id="summary-shipping-row">
                        <span>Versandkosten:</span>
                        <span id="summary-shipping">CHF 15.00</span>
                    </div>

                    <div class="summary-row" id="summary-discount-row" style="display: none;">
                        <span>Rabatt:</span>
                        <span id="summary-discount" style="color: #27ae60;">-CHF 0.00</span>
                    </div>

                    <div class="summary-divider"></div>

                    <div class="summary-row summary-total">
                        <span><strong>Gesamtbetrag:</strong></span>
                        <span id="summary-total"><strong>CHF 0.00</strong></span>
                    </div>

                    <button id="btn-complete-order" class="btn btn-primary btn-lg" style="width: 100%; margin-top: 1.5rem;">
                        <?php echo get_icon('check', 20); ?> Zur Zahlung
                    </button>

                    <p class="text-small text-light" style="text-align: center; margin-top: 1rem;">
                        <?php echo get_icon('lock', 14); ?> Sichere Zahlung mit SSL-Verschlüsselung
                    </p>
                </div>
            </aside>
        </div>
    </div>
</div>

<style>
.checkout-page {
    padding: 2rem 0;
    min-height: 70vh;
}

.checkout-page h1 {
    color: var(--primary-color);
    border-bottom: 3px solid var(--accent-gold);
    padding-bottom: 1rem;
    margin-bottom: 2rem;
}

.checkout-layout {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 2rem;
}

.checkout-main {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.checkout-step {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    overflow: hidden;
}

.step-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.step-number {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    font-weight: 700;
}

.step-header h2 {
    margin: 0;
    font-size: 1.3rem;
    border: none;
}

.step-content {
    padding: 1.5rem;
}

/* Delivery & Payment Options */
.delivery-options,
.payment-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.delivery-option,
.payment-option {
    cursor: pointer;
}

.delivery-option input,
.payment-option input {
    display: none;
}

.option-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    padding: 1.2rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s;
}

.delivery-option input:checked ~ .option-content,
.payment-option input:checked ~ .option-content {
    border-color: var(--primary-color);
    background: var(--bg-light);
}

.option-icon {
    width: 50px;
    height: 50px;
    background: var(--bg-light);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
    flex-shrink: 0;
}

.option-details h4 {
    margin: 0 0 0.3rem 0;
    color: var(--primary-color);
}

.option-details p {
    margin: 0.2rem 0;
    color: var(--text-light);
}

/* Address Options */
.address-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.address-option {
    cursor: pointer;
}

.address-option input {
    display: none;
}

.address-card {
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    position: relative;
    transition: all 0.3s;
}

.address-option input:checked ~ .address-card {
    border-color: var(--primary-color);
    background: var(--bg-light);
}

.badge-default {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: var(--accent-gold);
    color: white;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.address-card div {
    margin: 0.3rem 0;
    color: var(--text-dark);
}

/* Address Form */
.address-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.form-group input {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Coupon Form */
.coupon-form {
    display: flex;
    gap: 1rem;
}

.coupon-form input {
    flex: 1;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}

.coupon-form input:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Order Summary */
.checkout-sidebar {
    position: sticky;
    top: 2rem;
    height: fit-content;
}

.order-summary {
    background: white;
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.order-summary h3 {
    margin-top: 0;
    color: var(--primary-color);
    border-bottom: 2px solid var(--bg-light);
    padding-bottom: 1rem;
}

#order-items-list {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    padding: 0.8rem 0;
    border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
    border-bottom: none;
}

.item-name {
    font-weight: 600;
    color: var(--text-dark);
}

.item-quantity {
    color: var(--text-light);
    font-size: 0.9rem;
}

.summary-divider {
    height: 2px;
    background: var(--bg-light);
    margin: 1rem 0;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    color: var(--text-dark);
}

.summary-total {
    font-size: 1.2rem;
    color: var(--primary-color);
    padding-top: 1rem;
}

.info-text {
    color: var(--text-light);
    margin-bottom: 1rem;
}

.text-small {
    font-size: 0.85rem;
}

.text-light {
    color: var(--text-light);
}

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.alert-info {
    background: #e3f2fd;
    border-left: 4px solid #2196F3;
    color: #1976D2;
}

/* Responsive */
@media (max-width: 1024px) {
    .checkout-layout {
        grid-template-columns: 1fr 350px;
    }
}

@media (max-width: 768px) {
    .checkout-layout {
        grid-template-columns: 1fr;
    }

    .checkout-sidebar {
        position: static;
        order: -1;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .step-header h2 {
        font-size: 1.1rem;
    }

    .option-content {
        padding: 1rem;
        gap: 1rem;
    }

    .option-icon {
        width: 40px;
        height: 40px;
    }
}

@media (max-width: 480px) {
    .checkout-page {
        padding: 1rem 0;
    }

    .step-number {
        width: 35px;
        height: 35px;
        font-size: 1.1rem;
    }

    .step-content {
        padding: 1rem;
    }

    .coupon-form {
        flex-direction: column;
    }
}
</style>

<script>
// Load cart items
function loadCheckoutCart() {
    if (typeof cart === 'undefined') {
        console.error('Cart not loaded');
        return;
    }

    const itemsList = document.getElementById('order-items-list');
    let html = '';

    if (cart.items.length === 0) {
        html = '<p class="text-small text-light">Dein Warenkorb ist leer</p>';
    } else {
        cart.items.forEach(item => {
            const itemType = item.type || 'wine';
            const icon = itemType === 'event' ? '🎫' : '🍷';
            html += `
                <div class="order-item">
                    <div>
                        <div class="item-name">${icon} ${item.name}</div>
                        <div class="item-quantity">${item.quantity}x CHF ${item.price.toFixed(2)}</div>
                    </div>
                    <div><strong>CHF ${(item.price * item.quantity).toFixed(2)}</strong></div>
                </div>
            `;
        });
    }

    itemsList.innerHTML = html;
    updateOrderSummary();
}

// Update order summary
function updateOrderSummary() {
    if (typeof cart === 'undefined') return;

    const subtotal = cart.getTotalPrice();
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    const shipping = deliveryMethod === 'pickup' ? 0 : 15.00;
    const discount = appliedCoupon ? appliedCoupon.discount_amount : 0;
    const total = subtotal + shipping - discount;

    document.getElementById('summary-subtotal').textContent = 'CHF ' + subtotal.toFixed(2);
    document.getElementById('summary-shipping').textContent = shipping > 0 ? 'CHF ' + shipping.toFixed(2) : 'Kostenlos';

    if (discount > 0) {
        document.getElementById('summary-discount').textContent = 'CHF ' + discount.toFixed(2);
        document.getElementById('summary-discount-row').style.display = 'flex';
    }

    document.getElementById('summary-total').textContent = 'CHF ' + total.toFixed(2);
}

// Update delivery method
function updateDeliveryMethod() {
    const method = document.querySelector('input[name="delivery_method"]:checked')?.value;
    const cashOption = document.getElementById('payment-option-cash');
    const addressStepTitle = document.getElementById('address-step-title');
    const addressAlertText = document.getElementById('address-alert-text');
    const addressInfoText = document.getElementById('address-info-text');

    if (method === 'pickup') {
        // Show cash payment option
        cashOption.style.display = 'block';

        // Change titles to "Kontaktdaten" for pickup
        if (addressStepTitle) {
            addressStepTitle.textContent = 'Kontaktdaten';
        }
        if (addressAlertText) {
            addressAlertText.textContent = 'Bitte gib deine Kontaktdaten für die Abholung ein:';
        }
        if (addressInfoText) {
            addressInfoText.textContent = 'Wähle gespeicherte Kontaktdaten oder füge neue hinzu:';
        }
    } else {
        // Hide cash payment option
        cashOption.style.display = 'none';
        const cashRadio = document.querySelector('input[name="payment_method"][value="cash"]');
        if (cashRadio?.checked) {
            document.querySelector('input[name="payment_method"][value="card"]').checked = true;
        }

        // Change titles back to "Lieferadresse"
        if (addressStepTitle) {
            addressStepTitle.textContent = 'Lieferadresse';
        }
        if (addressAlertText) {
            addressAlertText.textContent = 'Bitte gib deine Lieferadresse ein:';
        }
        if (addressInfoText) {
            addressInfoText.textContent = 'Wähle eine gespeicherte Adresse oder füge eine neue hinzu:';
        }
    }

    updateOrderSummary();
}

// Load saved addresses list
function loadSavedAddresses() {
    const addressContent = document.getElementById('address-content');

    fetch('api/user-portal.php?action=get_addresses')
        .then(r => r.json())
        .then(d => {
            if (!d.success || d.addresses.length === 0) {
                // No addresses, show form
                showAddAddressForm();
                return;
            }

            let html = '<p class="info-text" id="address-info-text">Wähle eine gespeicherte Adresse oder füge eine neue hinzu:</p>';
            html += '<div class="address-list">';

            d.addresses.forEach((addr, index) => {
                html += `
                    <label class="address-option">
                        <input type="radio" name="address_id" value="${addr.id}" ${index === 0 ? 'checked' : ''}>
                        <div class="address-card">
                            ${addr.is_default ? '<span class="badge-default">Standard</span>' : ''}
                            <div><strong>${addr.first_name} ${addr.last_name}</strong></div>
                            <div>${addr.street}</div>
                            <div>${addr.postal_code} ${addr.city}</div>
                            <div>${addr.phone || ''}</div>
                        </div>
                    </label>
                `;
            });

            html += '</div>';
            html += '<button onclick="showAddAddressForm()" class="btn btn-secondary" style="margin-top: 1rem;">';
            html += '<?php echo get_icon("plus", 18); ?> Neue Adresse hinzufügen';
            html += '</button>';

            addressContent.innerHTML = html;
        })
        .catch(e => {
            console.error('Error loading addresses:', e);
            showAddAddressForm();
        });
}

// Show add address form
function showAddAddressForm() {
    const addressContent = document.getElementById('address-content');
    const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    addressContent.innerHTML = `
        <div class="alert alert-info" id="address-alert-text">
            Bitte gib deine ${document.querySelector('input[name="delivery_method"]:checked')?.value === 'pickup' ? 'Kontaktdaten' : 'Lieferadresse'} ein:
        </div>

        <form id="new-address-form" class="address-form">
            <div class="form-row">
                <div class="form-group">
                    <label>Vorname *</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Nachname *</label>
                    <input type="text" name="last_name" required>
                </div>
            </div>
            <div class="form-group" id="street-field">
                <label>Strasse & Hausnummer *</label>
                <input type="text" name="street" required>
            </div>
            <div class="form-row" id="postal-city-fields">
                <div class="form-group">
                    <label>PLZ *</label>
                    <input type="text" name="postal_code" required>
                </div>
                <div class="form-group">
                    <label>Ort *</label>
                    <input type="text" name="city" required>
                </div>
            </div>
            <div class="form-group">
                <label>Telefon *</label>
                <input type="tel" name="phone" required>
            </div>
            <div class="form-group">
                <label>E-Mail *</label>
                <input type="email" name="email" required value="<?php echo $is_logged_in ? safe_output($_SESSION['email'] ?? '') : ''; ?>">
            </div>
        </form>

        ${isLoggedIn ? '<button onclick="loadSavedAddresses()" class="btn btn-secondary" style="margin-top: 1rem;">« Zurück zur Adressauswahl</button>' : ''}
    `;
}

// Store applied coupon
let appliedCoupon = null;

// Coupon form submission
document.getElementById('coupon-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const code = document.getElementById('coupon-code-input').value.trim();
    const resultDiv = document.getElementById('coupon-result');

    if (!code) {
        resultDiv.style.display = 'block';
        resultDiv.innerHTML = '<div class="alert" style="background: #ffebee; color: #c62828;">Bitte gib einen Code ein</div>';
        return;
    }

    // Show loading
    resultDiv.style.display = 'block';
    resultDiv.innerHTML = '<p class="text-light">Prüfe Code...</p>';

    // Validate coupon via API
    const formData = new FormData();
    formData.append('action', 'validate');
    formData.append('code', code);
    formData.append('subtotal', cart.getTotalPrice());

    fetch('api/coupons.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            appliedCoupon = d.coupon;
            resultDiv.innerHTML = `
                <div class="alert" style="background: #d4edda; color: #155724; border-left: 4px solid #28a745;">
                    <strong>✓ Gutschein angewendet!</strong><br>
                    ${d.coupon.description}<br>
                    Ersparnis: CHF ${d.coupon.discount_amount.toFixed(2)}
                </div>
            `;
            // Update summary
            document.getElementById('summary-discount').textContent = 'CHF ' + d.coupon.discount_amount.toFixed(2);
            document.getElementById('summary-discount-row').style.display = 'flex';
            updateOrderSummary();
        } else {
            appliedCoupon = null;
            resultDiv.innerHTML = `<div class="alert" style="background: #fff3cd; color: #856404; border-left: 4px solid #ffc107;">${d.error}</div>`;
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        resultDiv.innerHTML = '<div class="alert" style="background: #ffebee; color: #c62828;">Fehler beim Validieren</div>';
    });
});

// Complete order button
document.getElementById('btn-complete-order')?.addEventListener('click', function() {
    // Validate form data
    const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked')?.value;
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value;

    if (!deliveryMethod || !paymentMethod) {
        alert('Bitte wähle Liefer- und Zahlungsmethode aus');
        return;
    }

    // Get address data
    let addressData = {};
    let selectedAddressId = null; // Track if user selected existing address

    if (deliveryMethod === 'delivery') {
        // Check if using saved address or new address
        selectedAddressId = document.querySelector('input[name="address_id"]:checked')?.value;

        if (selectedAddressId) {
            // Parse from selected address card
            const addressCard = document.querySelector(`input[name="address_id"][value="${selectedAddressId}"]`).nextElementSibling;
            const addressText = addressCard.textContent;
            const lines = addressText.split('\n').map(l => l.trim()).filter(l => l);

            addressData = {
                first_name: lines[0]?.split(' ')[0] || '',
                last_name: lines[0]?.split(' ').slice(1).join(' ') || '',
                street: lines[1] || '',
                postal_code: lines[2]?.split(' ')[0] || '',
                city: lines[2]?.split(' ').slice(1).join(' ') || '',
                phone: lines[3] || '',
                email: '<?php echo $_SESSION['email'] ?? ''; ?>'
            };
        } else {
            // Get from form
            const form = document.getElementById('new-address-form');
            if (!form) {
                alert('Bitte gib eine Lieferadresse ein');
                return;
            }

            addressData = {
                first_name: form.querySelector('[name="first_name"]')?.value || '',
                last_name: form.querySelector('[name="last_name"]')?.value || '',
                street: form.querySelector('[name="street"]')?.value || '',
                postal_code: form.querySelector('[name="postal_code"]')?.value || '',
                city: form.querySelector('[name="city"]')?.value || '',
                phone: form.querySelector('[name="phone"]')?.value || '',
                email: form.querySelector('[name="email"]')?.value || ''
            };

            // Validate required fields
            if (!addressData.first_name || !addressData.last_name || !addressData.street ||
                !addressData.postal_code || !addressData.city || !addressData.phone || !addressData.email) {
                alert('Bitte fülle alle Pflichtfelder aus');
                return;
            }
        }
    } else {
        // Pickup - get data from form (same as delivery)
        selectedAddressId = document.querySelector('input[name="address_id"]:checked')?.value;

        if (selectedAddressId) {
            // Parse from selected address card
            const addressCard = document.querySelector(`input[name="address_id"][value="${selectedAddressId}"]`).nextElementSibling;
            const addressText = addressCard.textContent;
            const lines = addressText.split('\n').map(l => l.trim()).filter(l => l);

            addressData = {
                first_name: lines[0]?.split(' ')[0] || '',
                last_name: lines[0]?.split(' ').slice(1).join(' ') || '',
                street: lines[1] || '',
                postal_code: lines[2]?.split(' ')[0] || '',
                city: lines[2]?.split(' ').slice(1).join(' ') || '',
                phone: lines[3] || '',
                email: '<?php echo $_SESSION['email'] ?? ''; ?>'
            };
        } else {
            // Get from form
            const form = document.getElementById('new-address-form');
            if (!form) {
                alert('Bitte gib deine Kontaktdaten ein');
                return;
            }

            addressData = {
                first_name: form.querySelector('[name="first_name"]')?.value || '',
                last_name: form.querySelector('[name="last_name"]')?.value || '',
                street: form.querySelector('[name="street"]')?.value || '',
                postal_code: form.querySelector('[name="postal_code"]')?.value || '',
                city: form.querySelector('[name="city"]')?.value || '',
                phone: form.querySelector('[name="phone"]')?.value || '',
                email: form.querySelector('[name="email"]')?.value || ''
            };

            // Validate required fields
            if (!addressData.first_name || !addressData.last_name || !addressData.phone || !addressData.email) {
                alert('Bitte fülle alle Pflichtfelder aus');
                return;
            }
        }
    }

    // Calculate totals
    const subtotal = cart.getTotalPrice();
    const shipping_cost = deliveryMethod === 'pickup' ? 0 : 15.00;
    const discount_amount = appliedCoupon ? appliedCoupon.discount_amount : 0;
    const total_amount = subtotal + shipping_cost - discount_amount;

    // Prepare order data
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('cart_items', JSON.stringify(cart.items));
    formData.append('delivery_method', deliveryMethod);
    formData.append('payment_method', paymentMethod);
    formData.append('first_name', addressData.first_name);
    formData.append('last_name', addressData.last_name);
    formData.append('street', addressData.street || '');
    formData.append('postal_code', addressData.postal_code || '');
    formData.append('city', addressData.city || '');
    formData.append('phone', addressData.phone);
    formData.append('email', addressData.email);
    formData.append('subtotal', subtotal);
    formData.append('shipping_cost', shipping_cost);
    formData.append('discount_amount', discount_amount);
    formData.append('total_amount', total_amount);

    if (appliedCoupon) {
        formData.append('coupon_code', appliedCoupon.code);
    }

    // Show loading
    const btn = document.getElementById('btn-complete-order');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<?php echo get_icon('loader', 20); ?> Bestellung wird erstellt...';

    // Create order
    fetch('api/orders.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            // Clear cart
            cart.clearCart();

            // Store order data and address for potential account creation
            sessionStorage.setItem('last_order_number', d.order_number);
            sessionStorage.setItem('last_order_email', addressData.email);
            sessionStorage.setItem('last_order_address', JSON.stringify(addressData));

            // Check if user is logged in
            const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

            // Auto-save address for logged-in users if they entered a new address
            if (isLoggedIn && !selectedAddressId) {
                const saveAddressData = new FormData();
                saveAddressData.append('action', 'add_address');
                saveAddressData.append('first_name', addressData.first_name);
                saveAddressData.append('last_name', addressData.last_name);
                saveAddressData.append('street', addressData.street || '');
                saveAddressData.append('postal_code', addressData.postal_code || '');
                saveAddressData.append('city', addressData.city || '');
                saveAddressData.append('country', 'Schweiz');
                saveAddressData.append('label', deliveryMethod === 'pickup' ? 'Kontaktdaten' : 'Lieferadresse');
                // Don't wait for response, save in background
                fetch('api/user-portal.php', {
                    method: 'POST',
                    body: saveAddressData
                }).catch(e => console.log('Address auto-save skipped:', e));
            }

            // Redirect to order confirmation page
            window.location.href = '?page=order-confirmation&order=' + encodeURIComponent(d.order_number);

            // After viewing confirmation, offer account creation for guests
            if (!isLoggedIn) {
                sessionStorage.setItem('offer_account_creation', '1');
            }
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Erstellen der Bestellung');
        btn.disabled = false;
        btn.innerHTML = originalText;
    });
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadCheckoutCart();
    updateDeliveryMethod();

    // Listen to cart changes
    window.addEventListener('cartUpdated', loadCheckoutCart);
});
</script>
