<?php
// includes/login-modal.php - Login Modal Overlay Komponente

$login_error = '';
$register_error = '';
$register_success = '';
$active_tab = $_GET['tab'] ?? 'login';

// LOGIN VERARBEITEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $login_type = $_POST['login_type'] ?? 'admin';
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $login_error = 'Ungültige E-Mail Adresse';
    } else {
        $email_safe = $db->real_escape_string($email);
        $result = $db->query("SELECT id, email, password, first_name FROM users WHERE email = '$email_safe'");

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                if ($login_type === 'admin') {
                    $admin_check = $db->query("SELECT * FROM admins WHERE user_id = " . $user['id']);
                    
                    if ($admin_check && $admin_check->num_rows === 1) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_name'] = $user['first_name'];
                        $_SESSION['is_admin'] = true;
                        
                        header('Location: ?page=admin-dashboard');
                        exit;
                    } else {
                        $login_error = 'Kein Admin-Zugang vorhanden';
                    }
                } else {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['first_name'];
                    
                    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '?page=home'));
                    exit;
                }
            } else {
                $login_error = 'Passwort ist falsch';
            }
        } else {
            $login_error = 'User nicht gefunden';
        }
    }
}

// REGISTRIERUNG VERARBEITEN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $email = trim($_POST['reg_email']);
    $password = $_POST['reg_password'];
    $password_confirm = $_POST['reg_password_confirm'];
    $first_name = trim($_POST['reg_first_name']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $register_error = 'Ungültige E-Mail Adresse';
    } elseif (strlen($password) < 6) {
        $register_error = 'Passwort muss mindestens 6 Zeichen lang sein';
    } elseif ($password !== $password_confirm) {
        $register_error = 'Passwörter stimmen nicht überein';
    } elseif (empty($first_name)) {
        $register_error = 'Name erforderlich';
    } else {
        $email_safe = $db->real_escape_string($email);
        $first_name_safe = $db->real_escape_string($first_name);
        
        $check = $db->query("SELECT id FROM users WHERE email = '$email_safe'");
        if ($check->num_rows > 0) {
            $register_error = 'Diese E-Mail-Adresse ist bereits registriert';
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $sql = "INSERT INTO users (email, password, first_name) VALUES ('$email_safe', '$password_hash', '$first_name_safe')";
            
            if ($db->query($sql)) {
                $register_success = 'Account erfolgreich erstellt! Du kannst dich jetzt einloggen.';
                $active_tab = 'login';
            } else {
                $register_error = 'Fehler beim Erstellen des Accounts';
            }
        }
    }
}
?>

<!-- Modal Overlay -->
<div class="auth-modal-overlay" id="authModalOverlay">
    <div class="auth-modal-container">
        <!-- TABS -->
        <div class="auth-tabs">
            <a href="?modal=login&tab=login" class="auth-tab <?php echo $active_tab === 'login' ? 'active' : ''; ?>">
                <span class="icon-text"><?php echo get_icon('user', 18); ?> Anmelden</span>
            </a>
            <a href="?modal=login&tab=register" class="auth-tab <?php echo $active_tab === 'register' ? 'active' : ''; ?>">
                <span class="icon-text"><?php echo get_icon('plus', 18); ?> Registrieren</span>
            </a>
        </div>

        <!-- LOGIN TAB -->
        <?php if ($active_tab === 'login'): ?>
            <div class="auth-tab-content active">
                <h2>Willkommen bei Vier Korken</h2>
                <p class="auth-subtitle">Melde dich an um fortzufahren</p>

                <?php if ($login_error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                        <span class="icon-text"><?php echo get_icon('warning', 16, 'icon-error'); ?> <?php echo safe_output($login_error); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($register_success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                        <span class="icon-text"><?php echo get_icon('check', 16, 'icon-success'); ?> <?php echo safe_output($register_success); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="login">

                    <div class="login-type-selector">
                        <label class="login-type-option">
                            <input type="radio" name="login_type" value="user" checked>
                            <span class="icon-text"><?php echo get_icon('user', 16); ?> User-Login</span>
                        </label>
                        <label class="login-type-option">
                            <input type="radio" name="login_type" value="admin">
                            <span class="icon-text"><?php echo get_icon('star', 16); ?> Admin-Login</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label>E-Mail</label>
                        <input type="email" name="email" placeholder="deine@email.ch" required autofocus>
                    </div>

                    <div class="form-group">
                        <label>Passwort</label>
                        <div class="password-input-wrapper">
                            <input type="password" name="password" id="login-password" placeholder="Passwort" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('login-password', this)" aria-label="Passwort anzeigen">
                                <?php echo get_icon('eye', 20); ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Anmeldedaten merken</label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Anmelden</button>
                </form>

                <p class="auth-footer-text">
                    Noch kein Konto? 
                    <a href="?modal=login&tab=register" class="link-button">Jetzt registrieren</a>
                </p>
            </div>
        <?php endif; ?>

        <!-- REGISTER TAB -->
        <?php if ($active_tab === 'register'): ?>
            <div class="auth-tab-content active">
                <h2>Account erstellen</h2>
                <p class="auth-subtitle">Registriere dich um ein Konto zu erstellen</p>

                <?php if ($register_error): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                        <span class="icon-text"><?php echo get_icon('warning', 16, 'icon-error'); ?> <?php echo safe_output($register_error); ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="reg_first_name" placeholder="Dein Name" required>
                    </div>

                    <div class="form-group">
                        <label>E-Mail</label>
                        <input type="email" name="reg_email" placeholder="deine@email.ch" required>
                    </div>

                    <div class="form-group">
                        <label>Passwort</label>
                        <div class="password-input-wrapper">
                            <input type="password" name="reg_password" id="reg-password" placeholder="Min. 6 Zeichen" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('reg-password', this)" aria-label="Passwort anzeigen">
                                <?php echo get_icon('eye', 20); ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Passwort wiederholen</label>
                        <div class="password-input-wrapper">
                            <input type="password" name="reg_password_confirm" id="reg-password-confirm" placeholder="Passwort wiederholen" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('reg-password-confirm', this)" aria-label="Passwort anzeigen">
                                <?php echo get_icon('eye', 20); ?>
                            </button>
                        </div>
                    </div>

                    <div class="form-group checkbox">
                        <input type="checkbox" id="terms" required>
                        <label for="terms">Ich akzeptiere die <a href="?page=agb" target="_blank">AGB</a> und <a href="?page=datenschutz" target="_blank">Datenschutzerklärung</a></label>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">Registrieren</button>
                </form>

                <p class="auth-footer-text">
                    Du hast bereits ein Konto? 
                    <a href="?modal=login&tab=login" class="link-button">Hier anmelden</a>
                </p>
            </div>
        <?php endif; ?>

        <!-- CLOSE BUTTON -->
        <button type="button" class="auth-modal-close" onclick="closeAuthModal()"><?php echo get_icon('close', 20); ?></button>
    </div>
</div>

<style>
.auth-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.auth-modal-container {
    background: white;
    border-radius: 15px;
    width: 90%;
    max-width: 420px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    position: relative;
    animation: slideUp 0.4s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.auth-modal-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: transparent;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #999;
    transition: all 0.3s;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}

.auth-modal-close:hover {
    color: var(--primary-color);
    transform: rotate(90deg);
}

.auth-tabs {
    display: grid;
    grid-template-columns: 1fr 1fr;
    border-bottom: 2px solid #f0f0f0;
}

.auth-tab {
    padding: 1.5rem;
    background: transparent;
    border: none;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    color: #999;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    text-decoration: none;
    display: block;
}

.auth-tab:hover {
    color: var(--primary-color);
}

.auth-tab.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
}

.auth-tab-content {
    padding: 2rem;
    display: none;
    animation: fadeIn 0.3s ease;
}

.auth-tab-content.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.auth-tab-content h2 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
    font-size: 1.8rem;
    border: none;
}

.auth-subtitle {
    color: #999;
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
}

.auth-form {
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
}

.login-type-selector {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.login-type-option {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.8rem;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
    font-weight: 500;
}

.login-type-option:hover {
    border-color: var(--primary-color);
    background: rgba(114, 44, 44, 0.05);
}

.login-type-option input[type="radio"] {
    margin: 0;
}

.login-type-option input[type="radio"]:checked + span {
    color: var(--primary-color);
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group label {
    font-weight: 600;
    color: var(--primary-color);
    font-size: 0.95rem;
}

.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="text"] {
    padding: 0.9rem;
    border: 2px solid #f0f0f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s;
}

.form-group input[type="email"]:focus,
.form-group input[type="password"]:focus,
.form-group input[type="text"]:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(114, 44, 44, 0.1);
}

.form-group.checkbox {
    flex-direction: row;
    align-items: flex-start;
    gap: 0.8rem;
}

.form-group.checkbox input[type="checkbox"] {
    margin-top: 0.35rem;
    accent-color: var(--primary-color);
    cursor: pointer;
}

.form-group.checkbox label {
    font-weight: 400;
    font-size: 0.9rem;
    cursor: pointer;
}

.form-group.checkbox a {
    color: var(--primary-color);
    text-decoration: underline;
}

.auth-footer-text {
    text-align: center;
    color: #999;
    font-size: 0.9rem;
    margin-top: 1.5rem;
}

.link-button {
    background: none;
    border: none;
    color: var(--primary-color);
    cursor: pointer;
    font-weight: 600;
    text-decoration: underline;
    padding: 0;
    font-size: 0.9rem;
}

.link-button:hover {
    opacity: 0.7;
}

@media (max-width: 480px) {
    .auth-modal-container {
        max-width: 95%;
    }

    .auth-tab {
        padding: 1rem;
        font-size: 0.9rem;
    }

    .auth-tab-content {
        padding: 1.5rem;
    }

    .login-type-selector {
        grid-template-columns: 1fr;
    }
}

/* Password Toggle Styles */
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

.password-toggle svg {
    width: 20px;
    height: 20px;
}
</style>

<script>
function closeAuthModal() {
    // Entferne das Modal vom DOM
    const overlay = document.getElementById('authModalOverlay');
    if (overlay) {
        overlay.remove();
    }

    // Entferne auch den modal Parameter aus der URL
    const url = new URL(window.location);
    url.searchParams.delete('modal');
    url.searchParams.delete('tab');
    window.history.replaceState({}, document.title, url);
}

// Toggle password visibility
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

// Schließe Modal wenn Overlay geklickt wird (nicht Container)
document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('authModalOverlay');
    const container = document.querySelector('.auth-modal-container');

    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                closeAuthModal();
            }
        });
    }

    // Pre-fill registration form if coming from order
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('from_order') === '1' && urlParams.get('tab') === 'register') {
        // Get stored order data from sessionStorage
        const orderEmail = sessionStorage.getItem('last_order_email');
        const orderAddress = sessionStorage.getItem('last_order_address');

        if (orderEmail) {
            const emailInput = document.querySelector('input[name="reg_email"]');
            if (emailInput) {
                emailInput.value = orderEmail;
            }
        }

        if (orderAddress) {
            try {
                const address = JSON.parse(orderAddress);
                const nameInput = document.querySelector('input[name="reg_first_name"]');
                if (nameInput && address.first_name) {
                    nameInput.value = address.first_name + (address.last_name ? ' ' + address.last_name : '');
                }
            } catch (e) {
                console.error('Error parsing address data:', e);
            }
        }

        // Show helpful message
        const registerForm = document.querySelector('.auth-tab-content.active');
        if (registerForm) {
            const helpText = document.createElement('div');
            helpText.className = 'alert alert-info';
            helpText.style.marginBottom = '1.5rem';
            helpText.innerHTML = '<p style="margin:0;">✨ Erstelle jetzt ein Konto, um deine Bestellung zu verfolgen und zukünftige Bestellungen schneller abzuschließen!</p>';
            registerForm.insertBefore(helpText, registerForm.querySelector('form'));
        }
    }
});
</script>