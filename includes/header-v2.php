<?php
// includes/header-v2.php - Neue Mobile-optimierte Header Version
// Mit Admin-Customization Support

// Theme Settings laden
function get_theme_color($key, $default = '') {
    global $db;
    $key_safe = $db->real_escape_string($key);
    $result = $db->query("SELECT setting_value FROM theme_settings WHERE setting_key = '$key_safe'");
    
    if ($result && $row = $result->fetch_assoc()) {
        return $row['setting_value'];
    }
    return $default;
}

// CSS Variablen aus DB
$header_bg = get_theme_color('header_bg_color', '#1a3a52');
$header_accent = get_theme_color('header_accent_color', '#d4af37');
$header_text = get_theme_color('header_text_color', '#ffffff');
$logo_text = get_theme_color('logo_text', 'Vier Korken');
$tagline_text = get_theme_color('tagline_text', 'Schweizer Wein');

$all_categories = get_all_categories();
$is_admin = $_SESSION['is_admin'] ?? false;
$is_logged_in = isset($_SESSION['user_id']) && !$is_admin;

// Warenkorb-Count
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $wine_id => $quantity) {
        $cart_count += $quantity;
    }
}
?>

<header class="header-v2">
    <!-- Mobile/Tablet Header (0-1024px) -->
    <div class="header-top-mobile">
        <div class="header-container">
            <!-- Left: Hamburger Menu -->
            <button class="hamburger-menu" id="hamburger-btn" aria-label="Menü">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <!-- Center: Logo -->
            <a href="?page=home" class="logo-link">
                <div class="logo-mobile">
                    <h1><?php echo safe_output($logo_text); ?></h1>
                </div>
            </a>

            <!-- Right: Icons (Search, User, Cart) -->
            <div class="header-icons-right">
                <a href="#" class="icon-btn search-toggle" id="search-toggle" aria-label="Suche">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </a>

                <?php if ($is_admin): ?>
                    <a href="?page=admin-dashboard" class="icon-btn" aria-label="Admin">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="1"></circle>
                            <path d="M12 1v6m0 6v6"></path>
                            <path d="M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24"></path>
                        </svg>
                    </a>
                <?php elseif ($is_logged_in): ?>
                    <a href="?page=user-portal" class="icon-btn" aria-label="Mein Account">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </a>
                <?php else: ?>
                    <a href="?modal=login" class="icon-btn login-icon" aria-label="Login">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                    </a>
                <?php endif; ?>

                <!-- Cart Icon with Badge -->
                <a href="?page=cart" class="icon-btn cart-icon-mobile" aria-label="Warenkorb">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </div>
        </div>

        <!-- Search Bar (Hidden by default, shown on toggle) -->
        <div class="search-bar" id="search-bar">
            <form action="?page=shop" method="GET" class="search-form">
                <input type="hidden" name="page" value="shop">
                <input type="text" name="search" placeholder="Weine suchen..." class="search-input">
                <button type="submit" class="search-submit">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile Side Menu (Hamburger) -->
    <nav class="mobile-menu" id="mobile-menu">
        <div class="mobile-menu-header">
            <h3><?php echo safe_output($logo_text); ?></h3>
            <button class="close-menu" id="close-menu" aria-label="Menü schließen">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="mobile-menu-content">
            <!-- Main Navigation -->
            <ul class="mobile-nav-list">
                <li><a href="?page=home" class="mobile-nav-item">Startseite</a></li>
                <li><a href="?page=shop" class="mobile-nav-item">Shop</a></li>
            </ul>

            <!-- Categories Dropdown -->
            <div class="mobile-menu-section">
                <button class="mobile-menu-toggle" data-toggle="categories">
                    <span>Kategorien</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
                <ul class="mobile-submenu" id="categories" style="display: none;">
                    <li><a href="?page=shop&category=0" class="mobile-nav-item">Alle Weine</a></li>
                    <?php foreach ($all_categories as $cat): ?>
                        <li><a href="?page=shop&category=<?php echo $cat['id']; ?>" class="mobile-nav-item">
                            <?php echo safe_output($cat['name']); ?> (<?php echo count_wines_in_category($cat['id']); ?>)
                        </a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Links -->
            <div class="mobile-menu-section">
                <a href="?page=newsletter" class="mobile-nav-item">Newsletter</a>
                <a href="?page=contact" class="mobile-nav-item">Kontakt</a>
            </div>

            <!-- Footer Links -->
            <div class="mobile-menu-section">
                <a href="?page=impressum" class="mobile-nav-item small">Impressum</a>
                <a href="?page=agb" class="mobile-nav-item small">AGB</a>
                <a href="?page=datenschutz" class="mobile-nav-item small">Datenschutz</a>
            </div>

            <!-- User Section -->
            <div class="mobile-menu-section user-section">
                <?php if ($is_admin): ?>
                    <a href="?logout=1" class="mobile-nav-item logout">Abmelden</a>
                <?php elseif ($is_logged_in): ?>
                    <p class="user-name">Angemeldet als User</p>
                    <a href="?logout=1" class="mobile-nav-item logout">Abmelden</a>
                <?php else: ?>
                    <a href="?modal=login" class="mobile-nav-item login">Anmelden</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<!-- Overlay für Menu -->
<div class="mobile-menu-overlay" id="mobile-menu-overlay"></div>

<style>
    :root {
        --header-bg: <?php echo $header_bg; ?>;
        --header-accent: <?php echo $header_accent; ?>;
        --header-text: <?php echo $header_text; ?>;
    }

    /* Header Mobile/Tablet (0-1024px) */
    .header-v2 {
        background: var(--header-bg);
        color: var(--header-text);
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .header-top-mobile {
        display: none;
    }

    /* Nur auf Mobile/Tablet anzeigen */
    @media (max-width: 1024px) {
        .header-top-mobile {
            display: block;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1rem;
            max-width: 100%;
        }

        /* Hamburger Menu */
        .hamburger-menu {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            gap: 5px;
            padding: 8px;
            color: var(--header-text);
        }

        .hamburger-menu span {
            width: 24px;
            height: 2px;
            background: var(--header-text);
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .hamburger-menu.active span:nth-child(1) {
            transform: rotate(45deg) translate(10px, 10px);
        }

        .hamburger-menu.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger-menu.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -7px);
        }

        /* Logo Mobile */
        .logo-link {
            text-decoration: none;
            flex: 1;
            text-align: center;
        }

        .logo-mobile h1 {
            font-size: 1.1rem;
            margin: 0;
            color: var(--header-text);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* Header Icons */
        .header-icons-right {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--header-text);
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
        }

        .icon-btn:hover {
            background: rgba(255,255,255,0.1);
            color: var(--header-accent);
        }

        .icon-btn svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
        }

        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #ff6b6b;
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 0.7rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Search Bar */
        .search-bar {
            display: none;
            padding: 0.75rem 1rem;
            background: rgba(255,255,255,0.05);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .search-bar.active {
            display: block;
        }

        .search-form {
            display: flex;
            gap: 0.5rem;
        }

        .search-input {
            flex: 1;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            color: var(--header-text);
            font-size: 0.9rem;
        }

        .search-input::placeholder {
            color: rgba(255,255,255,0.6);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--header-accent);
            background: rgba(255,255,255,0.15);
        }

        .search-submit {
            background: var(--header-accent);
            border: none;
            color: #333;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .search-submit:hover {
            opacity: 0.9;
            transform: scale(1.05);
        }

        /* Mobile Menu (Sidebar) */
        .mobile-menu {
            position: fixed;
            left: 0;
            top: 0;
            width: 80%;
            max-width: 350px;
            height: 100vh;
            background: var(--header-bg);
            color: var(--header-text);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1100;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .mobile-menu.active {
            transform: translateX(0);
        }

        .mobile-menu-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .mobile-menu-header h3 {
            margin: 0;
            font-size: 1.1rem;
        }

        .close-menu {
            background: none;
            border: none;
            cursor: pointer;
            color: var(--header-text);
            padding: 0;
        }

        .mobile-menu-content {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0;
        }

        .mobile-nav-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .mobile-nav-item {
            display: block;
            padding: 0.75rem 1.5rem;
            color: var(--header-text);
            text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            transition: all 0.3s ease;
        }

        .mobile-nav-item:hover {
            background: rgba(255,255,255,0.1);
            padding-left: 2rem;
        }

        .mobile-menu-section {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 0.5rem;
            padding-top: 0.5rem;
        }

        .mobile-menu-toggle {
            width: 100%;
            background: none;
            border: none;
            color: var(--header-text);
            padding: 0.75rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            text-align: left;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .mobile-menu-toggle:hover {
            background: rgba(255,255,255,0.05);
        }

        .mobile-submenu {
            list-style: none;
            margin: 0;
            padding: 0;
            background: rgba(0,0,0,0.1);
        }

        .mobile-submenu li a {
            padding-left: 3rem !important;
            font-size: 0.9rem;
        }

        /* User Section */
        .user-section {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: auto;
            padding-top: 1rem;
        }

        .user-name {
            padding: 0.75rem 1.5rem;
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.7;
        }

        .mobile-nav-item.login {
            background: var(--header-accent);
            color: #333;
            font-weight: 600;
            margin: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            text-align: center;
        }

        .mobile-nav-item.logout {
            background: #ff6b6b;
            color: white;
            font-weight: 600;
            margin: 0.5rem 1rem;
            border-radius: 6px;
            border: none;
            text-align: center;
        }

        /* Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 1050;
        }

        .mobile-menu-overlay.active {
            display: block;
        }
    }

    /* Desktop Header (1025px+) */
    @media (min-width: 1025px) {
        .header-v2 {
            background: var(--header-bg);
        }

        .header-top-mobile {
            display: none;
        }

        .mobile-menu,
        .mobile-menu-overlay {
            display: none !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hamburger Menu Toggle
        const hamburger = document.getElementById('hamburger-btn');
        const menu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('mobile-menu-overlay');
        const closeBtn = document.getElementById('close-menu');
        const searchToggle = document.getElementById('search-toggle');
        const searchBar = document.getElementById('search-bar');

        if (hamburger) {
            hamburger.addEventListener('click', function() {
                this.classList.toggle('active');
                menu.classList.toggle('active');
                overlay.classList.toggle('active');
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                hamburger.classList.remove('active');
                menu.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', function() {
                hamburger.classList.remove('active');
                menu.classList.remove('active');
                overlay.classList.remove('active');
            });
        }

        // Search Toggle
        if (searchToggle) {
            searchToggle.addEventListener('click', function(e) {
                e.preventDefault();
                searchBar.classList.toggle('active');
                if (searchBar.classList.contains('active')) {
                    searchBar.querySelector('.search-input').focus();
                }
            });
        }

        // Categories Dropdown
        const menuToggles = document.querySelectorAll('.mobile-menu-toggle');
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-toggle');
                const target = document.getElementById(targetId);
                target.style.display = target.style.display === 'none' ? 'block' : 'none';
                this.classList.toggle('active');
            });
        });

        // Close menu when item clicked
        const navItems = document.querySelectorAll('.mobile-nav-item');
        navItems.forEach(item => {
            item.addEventListener('click', function() {
                if (!this.classList.contains('active')) {
                    hamburger.classList.remove('active');
                    menu.classList.remove('active');
                    overlay.classList.remove('active');
                }
            });
        });
    });
</script>