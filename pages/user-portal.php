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

<link rel="stylesheet" href="assets/css/user-portal-extended.css">

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

            <div class="quick-links">
                <h2>Schnellzugriff</h2>
                <a href="?page=shop" class="quick-link">Zum Shop gehen</a>
                <a href="?page=user-portal&tab=ratings" class="quick-link">Meine Bewertungen</a>
                <a href="?page=user-portal&tab=orders" class="quick-link">Bestellhistorie</a>
                <a href="?page=user-portal&tab=profile" class="quick-link">Profil bearbeiten</a>
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
</style>

<script src="assets/js/user-portal.js"></script>