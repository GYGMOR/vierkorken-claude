<?php
// pages/register-after-order.php - Password Setup After Order

// Get order data from sessionStorage via PHP session alternative
$order_email = '';
$order_first_name = '';
$order_last_name = '';
$order_address = [];

// Check if we have stored order data
if (isset($_SESSION['last_order_data'])) {
    $order_data = $_SESSION['last_order_data'];
    $order_email = $order_data['email'] ?? '';
    $order_first_name = $order_data['first_name'] ?? '';
    $order_last_name = $order_data['last_name'] ?? '';
}
?>

<div class="register-after-order-page">
    <div class="container" style="max-width: 600px; padding: 3rem 1rem;">
        <div class="register-card">
            <div class="register-header">
                <div class="header-icon">✨</div>
                <h1>Konto erstellen</h1>
                <p class="subtitle">Erstelle dein Passwort um dein Konto zu vervollständigen</p>
            </div>

            <form id="register-form" class="register-form">
                <div class="form-group">
                    <label>Vorname *</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>

                <div class="form-group">
                    <label>Nachname</label>
                    <input type="text" name="last_name" id="last_name">
                </div>

                <div class="form-group">
                    <label>E-Mail *</label>
                    <input type="email" name="email" id="email" required readonly>
                </div>

                <div class="form-group">
                    <label>Passwort *</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="password" id="password" required minlength="6"
                               placeholder="Mindestens 6 Zeichen">
                        <button type="button" class="password-toggle" onclick="togglePassword('password', this)" aria-label="Passwort anzeigen">
                            <?php echo get_icon('eye', 20); ?>
                        </button>
                    </div>
                    <small class="form-hint">Mindestens 6 Zeichen lang</small>
                </div>

                <div class="form-group">
                    <label>Passwort wiederholen *</label>
                    <div class="password-input-wrapper">
                        <input type="password" name="confirm_password" id="confirm_password" required minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirm_password', this)" aria-label="Passwort anzeigen">
                            <?php echo get_icon('eye', 20); ?>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="accept_terms" required>
                        <span>Ich akzeptiere die <a href="?page=agb" target="_blank">AGB</a> und <a href="?page=datenschutz" target="_blank">Datenschutzerklärung</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-large" id="submit-btn">
                    <?php echo get_icon('check', 20); ?> Konto erstellen
                </button>
            </form>

            <div class="register-footer">
                <p>Du hast bereits ein Konto? <a href="?modal=login">Jetzt anmelden</a></p>
            </div>
        </div>
    </div>
</div>

<style>
.register-after-order-page {
    min-height: 70vh;
    padding: 2rem 0;
    background: linear-gradient(135deg, #f8f4f0 0%, #e8e4e0 100%);
}

.register-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    overflow: hidden;
}

.register-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 3rem 2rem;
    text-align: center;
}

.header-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.register-header h1 {
    margin: 0 0 0.5rem 0;
    color: white;
    border: none;
    font-size: 2rem;
}

.subtitle {
    margin: 0;
    opacity: 0.95;
    font-size: 1.1rem;
}

.register-form {
    padding: 2.5rem 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(114, 44, 44, 0.1);
}

.form-group input[readonly] {
    background: #f5f5f5;
    cursor: not-allowed;
}

.form-hint {
    display: block;
    margin-top: 0.3rem;
    color: var(--text-light);
    font-size: 0.85rem;
}

.checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    margin-top: 0.3rem;
}

.checkbox-label span {
    line-height: 1.5;
}

.checkbox-label a {
    color: var(--primary-color);
    text-decoration: underline;
}

.btn-large {
    width: 100%;
    padding: 1rem;
    font-size: 1.1rem;
    margin-top: 1rem;
}

.register-footer {
    padding: 1.5rem 2rem;
    background: var(--bg-light);
    text-align: center;
    border-top: 1px solid #e0e0e0;
}

.register-footer p {
    margin: 0;
    color: var(--text-light);
}

.register-footer a {
    color: var(--primary-color);
    font-weight: 600;
    text-decoration: none;
}

.register-footer a:hover {
    text-decoration: underline;
}

/* Password toggle styling */
.password-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-input-wrapper input {
    width: 100%;
    padding-right: 3rem;
}

.password-toggle {
    position: absolute;
    right: 0.5rem;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: color 0.2s;
    border-radius: 4px;
}

.password-toggle:hover {
    color: var(--primary-color);
    background: rgba(0, 0, 0, 0.05);
}

.password-toggle:focus {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

@media (max-width: 768px) {
    .register-header {
        padding: 2rem 1.5rem;
    }

    .register-form {
        padding: 2rem 1.5rem;
    }
}
</style>

<script>
// Password toggle function
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);

    if (input.type === 'password') {
        input.type = 'text';
        // Change to eye-off icon
        button.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;
        button.setAttribute('aria-label', 'Passwort verbergen');
    } else {
        input.type = 'password';
        // Change to eye icon
        button.innerHTML = `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
        button.setAttribute('aria-label', 'Passwort anzeigen');
    }
}

// Pre-fill form with order data from sessionStorage
document.addEventListener('DOMContentLoaded', function() {
    const orderEmail = sessionStorage.getItem('last_order_email');
    const orderAddressStr = sessionStorage.getItem('last_order_address');

    if (orderEmail) {
        document.getElementById('email').value = orderEmail;
    }

    if (orderAddressStr) {
        try {
            const orderAddress = JSON.parse(orderAddressStr);
            if (orderAddress.first_name) {
                document.getElementById('first_name').value = orderAddress.first_name;
            }
            if (orderAddress.last_name) {
                document.getElementById('last_name').value = orderAddress.last_name;
            }
        } catch (e) {
            console.error('Error parsing order address:', e);
        }
    }
});

// Handle form submission
document.getElementById('register-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (password !== confirmPassword) {
        alert('Passwörter stimmen nicht überein!');
        return;
    }

    if (password.length < 6) {
        alert('Passwort muss mindestens 6 Zeichen lang sein!');
        return;
    }

    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<?php echo get_icon("loader", 20); ?> Erstelle Konto...';

    const formData = new FormData();
    formData.append('action', 'register');
    formData.append('first_name', document.getElementById('first_name').value);
    formData.append('last_name', document.getElementById('last_name').value);
    formData.append('email', document.getElementById('email').value);
    formData.append('password', password);

    fetch('api/auth.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            // Clear stored order data
            sessionStorage.removeItem('last_order_email');
            sessionStorage.removeItem('last_order_address');
            sessionStorage.removeItem('offer_account_creation');

            // Show success message with linked orders info
            showSuccessMessage(d.linked_orders || 0);
        } else {
            alert('Fehler: ' + (d.error || 'Registrierung fehlgeschlagen'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler bei der Registrierung');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});

function showSuccessMessage(linkedOrders) {
    const container = document.querySelector('.register-card');

    let ordersMessage = '';
    if (linkedOrders > 0) {
        ordersMessage = `<p style="font-size: 1rem; color: #28a745; margin-bottom: 1rem; font-weight: 600;">
            ${linkedOrders} frühere Bestellung${linkedOrders > 1 ? 'en' : ''} wurde${linkedOrders > 1 ? 'n' : ''} deinem Account zugeordnet!
        </p>`;
    }

    container.innerHTML = `
        <div style="padding: 4rem 2rem; text-align: center;">
            <div style="font-size: 3rem; margin-bottom: 1rem; color: var(--primary-color);">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <h2 style="color: var(--primary-color); margin-bottom: 1rem;">Konto erfolgreich erstellt!</h2>
            ${ordersMessage}
            <p style="font-size: 1.1rem; color: var(--text-light); margin-bottom: 2rem;">
                Du kannst dich jetzt anmelden und deine Bestellungen verwalten.
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="?page=user-portal&tab=orders" class="btn btn-primary">Meine Bestellungen</a>
                <a href="?page=shop" class="btn btn-secondary">Weiter einkaufen</a>
            </div>
        </div>
    `;
}
</script>
