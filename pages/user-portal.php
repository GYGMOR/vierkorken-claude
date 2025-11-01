<?php
// pages/user-portal.php - FINAL VERSION

if (!isset($_SESSION['user_id']) || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'])) {
    header('Location: ?page=home');
    exit;
}

$user_id = $_SESSION['user_id'];
$tab = $_GET['tab'] ?? 'overview';

$user = $db->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
$favorites_count = $db->query("SELECT COUNT(*) as c FROM user_favorites WHERE user_id = $user_id")->fetch_assoc()['c'];
$orders_count = $db->query("SELECT COUNT(*) as c FROM orders WHERE user_id = $user_id")->fetch_assoc()['c'];
$ratings_count = $db->query("SELECT COUNT(*) as c FROM wine_ratings WHERE user_id = $user_id")->fetch_assoc()['c'];
?>

<link rel="stylesheet" href="assets/css/user-portal-extended.css?v=<?php echo time(); ?>">

<div class="user-portal-container">
    <aside class="portal-sidebar">
        <div class="portal-user-header">
            <div class="user-avatar"><?php echo strtoupper(substr($user['first_name'], 0, 1)); ?></div>
            <div>
                <h3><?php echo safe_output($user['first_name']); ?></h3>
                <p><?php echo safe_output($user['email']); ?></p>
            </div>
        </div>

        <nav class="portal-nav">
            <a href="?page=user-portal&tab=overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">Übersicht</a>
            <a href="?page=user-portal&tab=orders" class="<?php echo $tab === 'orders' ? 'active' : ''; ?>">Bestellungen (<?php echo $orders_count; ?>)</a>
            <a href="?page=user-portal&tab=favorites" class="<?php echo $tab === 'favorites' ? 'active' : ''; ?>">Lieblingsweine (<?php echo $favorites_count; ?>)</a>
            <a href="?page=user-portal&tab=ratings" class="<?php echo $tab === 'ratings' ? 'active' : ''; ?>">Bewertungen (<?php echo $ratings_count; ?>)</a>
            <a href="?page=user-portal&tab=addresses" class="<?php echo $tab === 'addresses' ? 'active' : ''; ?>">Adressbuch</a>
            <a href="?page=user-portal&tab=profile" class="<?php echo $tab === 'profile' ? 'active' : ''; ?>">Profil</a>
        </nav>

        <a href="?logout=1" class="btn-logout">Abmelden</a>
    </aside>

    <main class="portal-content">
        
        <?php if ($tab === 'overview'): ?>
            <h1>Willkommen, <?php echo safe_output($user['first_name']); ?>!</h1>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $orders_count; ?></div>
                    <div class="stat-label">Bestellungen</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $favorites_count; ?></div>
                    <div class="stat-label">Lieblingsweine</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $ratings_count; ?></div>
                    <div class="stat-label">Bewertungen</div>
                </div>
            </div>

            <div class="quick-links-modern">
                <h2>Schnellzugriff</h2>
                <div class="quick-links-grid">
                    <a href="?page=shop" class="quick-link-card">
                        <div class="quick-link-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                        </div>
                        <div class="quick-link-text">
                            <h4>Shop</h4>
                            <p>Zum Shop gehen</p>
                        </div>
                    </a>

                    <a href="?page=user-portal&tab=ratings" class="quick-link-card">
                        <div class="quick-link-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                            </svg>
                        </div>
                        <div class="quick-link-text">
                            <h4>Bewertungen</h4>
                            <p>Meine Bewertungen</p>
                        </div>
                    </a>

                    <a href="?page=user-portal&tab=orders" class="quick-link-card">
                        <div class="quick-link-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
                        <div class="quick-link-text">
                            <h4>Bestellungen</h4>
                            <p>Bestellhistorie</p>
                        </div>
                    </a>

                    <a href="?page=user-portal&tab=profile" class="quick-link-card">
                        <div class="quick-link-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div class="quick-link-text">
                            <h4>Profil</h4>
                            <p>Profil bearbeiten</p>
                        </div>
                    </a>
                </div>
            </div>

        <?php elseif ($tab === 'orders'): ?>
            <h1>Meine Bestellungen</h1>
            <div id="orders-container" class="loading">Laden...</div>

        <?php elseif ($tab === 'favorites'): ?>
            <h1>Meine Lieblingsweine</h1>
            <div id="favorites-container" class="loading">Laden...</div>

        <?php elseif ($tab === 'ratings'): ?>
            <h1>Meine Bewertungen</h1>
            <div id="ratings-container" class="loading">Laden...</div>

        <?php elseif ($tab === 'addresses'): ?>
            <h1>Mein Adressbuch</h1>
            <button class="btn btn-primary" onclick="addAddressForm()" style="margin-bottom: 2rem;">+ Neue Adresse hinzufügen</button>
            <div id="addresses-container" class="loading">Laden...</div>

        <?php elseif ($tab === 'profile'): ?>
            <h1>Mein Profil</h1>

            <div class="profile-section">
                <h2>Persönliche Daten</h2>
                <form id="form-profile" class="form-portal" onsubmit="event.preventDefault(); updateProfile()">
                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Vorname</label>
                            <input type="text" id="first_name" value="<?php echo safe_output($user['first_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Nachname</label>
                            <input type="text" id="last_name" value="<?php echo safe_output($user['last_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Telefon</label>
                            <input type="tel" id="phone" value="<?php echo safe_output($user['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>E-Mail (nicht änderbar)</label>
                            <input type="email" value="<?php echo safe_output($user['email']); ?>" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </form>
            </div>

            <div class="profile-section">
                <h2>Passwort ändern</h2>
                <form id="form-password" class="form-portal" onsubmit="event.preventDefault(); changePassword()">
                    <div class="form-group">
                        <label>Aktuelles Passwort</label>
                        <input type="password" id="old_password" required>
                    </div>
                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label>Neues Passwort</label>
                            <input type="password" id="new_password" required>
                        </div>
                        <div class="form-group">
                            <label>Passwort wiederholen</label>
                            <input type="password" id="confirm_password" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Passwort ändern</button>
                </form>
            </div>

            <script>
                function updateProfile() {
                    const formData = new FormData();
                    formData.append('action', 'update_profile');
                    formData.append('first_name', document.getElementById('first_name').value);
                    formData.append('last_name', document.getElementById('last_name').value);
                    formData.append('phone', document.getElementById('phone').value);
                    
                    fetch('api/user-portal.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                showNotification(d.message, 'success');
                            } else {
                                showNotification(d.error || 'Fehler', 'error');
                            }
                        });
                }

                function changePassword() {
                    const formData = new FormData();
                    formData.append('action', 'change_password');
                    formData.append('old_password', document.getElementById('old_password').value);
                    formData.append('new_password', document.getElementById('new_password').value);
                    formData.append('confirm_password', document.getElementById('confirm_password').value);
                    
                    fetch('api/user-portal.php', { method: 'POST', body: formData })
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) {
                                showNotification(d.message, 'success');
                                document.getElementById('form-password').reset();
                            } else {
                                showNotification(d.error || 'Fehler', 'error');
                            }
                        });
                }
            </script>

        <?php endif; ?>

    </main>
</div>

<style>
    .loading {
        text-align: center;
        padding: 3rem;
        color: #999;
    }

    /* Modern Quick Links */
    .quick-links-modern {
        margin-top: 2rem;
    }

    .quick-links-modern h2 {
        margin-bottom: 1.5rem;
        color: var(--primary-color);
    }

    .quick-links-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .quick-link-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        background: white;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .quick-link-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 8px 20px rgba(114, 44, 44, 0.15);
        transform: translateY(-2px);
    }

    .quick-link-icon {
        width: 60px;
        height: 60px;
        background: linear-gradient(135deg, var(--primary-color), #8b3a3a);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(114, 44, 44, 0.2);
    }

    .quick-link-icon svg {
        color: white;
        width: 28px;
        height: 28px;
    }

    .quick-link-text {
        flex: 1;
    }

    .quick-link-text h4 {
        margin: 0 0 0.3rem 0;
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .quick-link-text p {
        margin: 0;
        color: #6b7280;
        font-size: 0.9rem;
    }

    @media (max-width: 768px) {
        .quick-links-grid {
            grid-template-columns: 1fr;
        }

        .quick-link-card {
            padding: 1.2rem;
        }

        .quick-link-icon {
            width: 50px;
            height: 50px;
        }

        .quick-link-icon svg {
            width: 24px;
            height: 24px;
        }
    }
</style>

<script src="assets/js/user-portal.js"></script>