<?php
// includes/header.php - UNIFIED Design für alle Devices (Mobile-First)

$all_categories = get_all_categories();
$is_admin = $_SESSION['is_admin'] ?? false;
$is_logged_in = isset($_SESSION['user_id']) && !$is_admin;

// Theme Farben
$header_bg = get_theme_color('header_bg_color', '#693a15');
$header_accent = get_theme_color('header_accent_color', '#ead39c');
$logo_text = get_theme_color('logo_text', 'Vier Korken');

// Warenkorb-Count
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $wine_id => $quantity) {
        $cart_count += $quantity;
    }
}
?>

<header class="header-unified">
    <div class="header-top">
        <!-- Left: Hamburger Menu -->
        <button class="hamburger" id="hamburger-btn" aria-label="Menü">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <!-- Center: Logo (gleich auf allen Devices) -->
        <a href="?page=home" class="logo-link">
            <h1><?php echo safe_output($logo_text); ?></h1>
        </a>

        <!-- Right: Icons (gleich auf allen Devices) -->
        <div class="header-icons">
            <!-- Search -->
            <button class="icon-btn search-toggle" id="search-toggle" aria-label="Suche">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>

            <!-- Login/User -->
            <?php if (!$is_admin && !$is_logged_in): ?>
                <a href="?modal=login" class="icon-btn" aria-label="Anmelden">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                </a>
            <?php elseif ($is_admin): ?>
                <a href="?page=admin-dashboard" class="icon-btn" aria-label="Admin">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="1"></circle>
                        <path d="M12 1v6m0 6v6"></path>
                        <path d="M4.22 4.22l4.24 4.24m5.08 5.08l4.24 4.24"></path>
                    </svg>
                </a>
            <?php else: ?>
                <a href="?page=user-portal" class="icon-btn" aria-label="Mein Account">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </a>
            <?php endif; ?>

            <!-- Wishlist/Merkliste -->
            <a href="?page=wishlist" class="icon-btn wishlist-icon" aria-label="Merkliste">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <span class="cart-badge" id="wishlist-count" style="display: none;">0</span>
            </a>

            <!-- Cart -->
            <a href="?page=cart" class="icon-btn cart-icon" aria-label="Warenkorb">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span class="cart-badge" id="cart-count" style="display: none;">0</span>
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="search-bar" id="search-bar">
        <form action="?page=shop" method="GET" class="search-form">
            <input type="hidden" name="page" value="shop">
            <input type="text" name="search" placeholder="Wein suchen..." class="search-input">
            <button type="submit" class="search-submit">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </button>
        </form>
    </div>
</header>

<!-- Mobile Menü (Sidebar) -->
<nav class="mobile-menu" id="mobile-menu">
    <div class="menu-header">
        <h2><?php echo safe_output($logo_text); ?></h2>
        <button class="close-menu" id="close-menu" aria-label="Menü schließen">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>

    <div class="menu-content">
        <!-- Navigation -->
        <ul class="menu-nav">
            <li><a href="?page=home">Startseite</a></li>
            <li><a href="?page=shop">Shop</a></li>
            <li><a href="?page=events">Events</a></li>
        </ul>

        <!-- Kategorien Section entfernt - nur noch im Shop sichtbar -->

        <!-- Links -->
        <div class="menu-section">
            <a href="?page=newsletter" class="menu-link">Newsletter</a>
            <a href="?page=contact" class="menu-link">Kontakt</a>
        </div>

        <!-- Footer Links -->
        <div class="menu-footer">
            <a href="?page=impressum" class="menu-link-small">Impressum</a>
            <a href="?page=agb" class="menu-link-small">AGB</a>
            <a href="?page=datenschutz" class="menu-link-small">Datenschutz</a>
        </div>

        <!-- User Section -->
        <div class="menu-user">
            <?php if ($is_admin): ?>
                <a href="?page=admin-dashboard" class="menu-link">Admin Dashboard</a>
                <a href="?logout=1" class="menu-logout">Abmelden</a>
            <?php elseif ($is_logged_in): ?>
                <a href="?page=user-portal" class="menu-link">Mein Account</a>
                <a href="?page=order-history" class="menu-link">Meine Bestellungen</a>
                <a href="?logout=1" class="menu-logout">Abmelden</a>
            <?php else: ?>
                <a href="?modal=login" class="menu-login">Anmelden</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<div class="menu-overlay" id="menu-overlay"></div>

<style>
    :root {
        --header-bg: <?php echo $header_bg; ?>;
        --header-accent: <?php echo $header_accent; ?>;
    }

    /* UNIFIED HEADER - Gleich überall! */
    .header-unified {
        background: var(--header-bg);
        color: white;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 1rem;
        max-width: 100%;
    }

    /* Hamburger Menu */
    .hamburger {
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        gap: 4px;
        padding: 8px;
        color: white;
        transition: all 0.3s;
    }

    .hamburger span {
        width: 22px;
        height: 2px;
        background: white;
        border-radius: 2px;
        transition: all 0.3s;
    }

    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }

    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }

    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(6px, -6px);
    }

    /* Logo */
    .logo-link {
        flex: 1;
        text-align: center;
        text-decoration: none;
    }

    .logo-link h1 {
        font-size: 1.1rem;
        margin: 0;
        color: white;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    /* Icons */
    .header-icons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .icon-btn {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        transition: all 0.3s;
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
        top: 0;
        right: 0;
        background: #ff6b6b;
        color: white;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        font-size: 0.65rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Search Bar */
    .search-bar {
        display: none;
        padding: 0.75rem 1rem;
        background: rgba(0,0,0,0.1);
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
        border: none;
        border-radius: 6px;
        background: rgba(255,255,255,0.15);
        color: white;
        font-size: 0.9rem;
    }

    .search-input::placeholder {
        color: rgba(255,255,255,0.6);
    }

    .search-input:focus {
        outline: none;
        background: rgba(255,255,255,0.25);
        box-shadow: 0 0 0 2px var(--header-accent);
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
        transition: all 0.3s;
    }

    .search-submit:hover {
        opacity: 0.9;
    }

    /* Mobile Menu Sidebar */
    .mobile-menu {
        position: fixed;
        left: 0;
        top: 0;
        width: 80%;
        max-width: 350px;
        height: 100vh;
        background: linear-gradient(180deg, var(--header-bg) 0%, #5a2d0f 100%);
        color: white;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1100;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        box-shadow: 5px 0 25px rgba(0,0,0,0.4);
    }

    .mobile-menu.active {
        transform: translateX(0);
    }

    .menu-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem 1.5rem;
        border-bottom: 2px solid rgba(255,255,255,0.15);
        background: rgba(0,0,0,0.2);
        backdrop-filter: blur(10px);
    }

    .menu-header h2 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--header-accent);
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .close-menu {
        background: none;
        border: none;
        cursor: pointer;
        color: white;
        padding: 0;
    }

    .menu-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem 0;
    }

    .menu-nav {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .menu-nav li {
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .menu-nav a {
        display: block;
        padding: 1rem 1.5rem;
        color: white;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 500;
        font-size: 1rem;
        border-left: 3px solid transparent;
    }

    .menu-nav a:hover {
        background: rgba(255,255,255,0.15);
        padding-left: 2rem;
        border-left-color: var(--header-accent);
        color: var(--header-accent);
    }

    /* Menu Sections */
    .menu-section {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }

    .menu-toggle {
        width: 100%;
        background: none;
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        text-align: left;
        font-size: 1rem;
        transition: all 0.3s;
        font-weight: 500;
    }

    .menu-toggle:hover {
        background: rgba(255,255,255,0.1);
    }

    .submenu {
        list-style: none;
        margin: 0;
        padding: 0.5rem 0;
        background: rgba(0,0,0,0.2);
        display: none;
    }

    .submenu.active {
        display: block;
        animation: slideDown 0.2s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .submenu li {
        list-style: none;
    }

    .submenu-header {
        padding: 1rem 1.5rem 0.5rem 1.5rem !important;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: var(--header-accent) !important;
        background: rgba(0,0,0,0.2);
        margin-top: 0.5rem;
        border-left: 3px solid var(--header-accent);
        display: block !important;
    }

    .submenu-header:first-child {
        margin-top: 0;
    }

    .submenu li a {
        padding: 0.8rem 1.5rem 0.8rem 2.5rem !important;
        font-size: 0.95rem;
        color: rgba(255,255,255,0.9);
        display: flex;
        align-items: center;
        border-left: 3px solid transparent;
        transition: all 0.3s;
    }

    .submenu li a:before {
        content: '▸';
        margin-right: 0.8rem;
        color: var(--header-accent);
        font-size: 1.1rem;
    }

    .submenu li a:hover {
        background: rgba(255,255,255,0.05);
        border-left-color: var(--header-accent);
        padding-left: 2.7rem !important;
        color: var(--header-accent);
    }

    .menu-link {
        display: block;
        padding: 0.75rem 1.5rem;
        color: white;
        text-decoration: none;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        transition: all 0.3s;
    }

    .menu-link:hover {
        background: rgba(255,255,255,0.1);
        padding-left: 2rem;
    }

    .menu-link-small {
        display: block;
        padding: 0.5rem 1.5rem;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.3s;
    }

    .menu-link-small:hover {
        color: white;
        padding-left: 2rem;
    }

    .menu-footer {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 1rem 0;
        margin: 1rem 0;
    }

    .menu-user {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: auto;
        padding-top: 1rem;
    }

    .menu-login {
        display: block;
        padding: 0.8rem 1.5rem;
        background: #17a2b8;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        margin: 0.5rem 1rem;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s;
    }

    .menu-login:hover {
        background: #138496;
    }

    .menu-logout {
        display: block;
        padding: 0.8rem 1.5rem;
        background: #ff6b6b;
        color: white;
        text-decoration: none;
        border-radius: 6px;
        margin: 0.5rem 1rem;
        text-align: center;
        font-weight: 600;
        transition: all 0.3s;
    }

    .menu-logout:hover {
        background: #ff5252;
    }

    /* Menu Overlay */
    .menu-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: none;
        z-index: 1050;
    }

    .menu-overlay.active {
        display: block;
    }

    /* WICHTIG: Kein unterschiedliches Design mehr nach Screensize! */
    /* Überall gleich - nur unterschiedliche Breite des Menüs */
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburger = document.getElementById('hamburger-btn');
        const menu = document.getElementById('mobile-menu');
        const overlay = document.getElementById('menu-overlay');
        const closeBtn = document.getElementById('close-menu');
        const searchToggle = document.getElementById('search-toggle');
        const searchBar = document.getElementById('search-bar');

        // Hamburger Toggle
        hamburger.addEventListener('click', function() {
            this.classList.toggle('active');
            menu.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        // Close Menu
        closeBtn.addEventListener('click', function() {
            hamburger.classList.remove('active');
            menu.classList.remove('active');
            overlay.classList.remove('active');
        });

        overlay.addEventListener('click', function() {
            hamburger.classList.remove('active');
            menu.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Search Toggle
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            searchBar.classList.toggle('active');
            if (searchBar.classList.contains('active')) {
                searchBar.querySelector('input').focus();
            }
        });

        // Category Toggle
        const menuToggles = document.querySelectorAll('.menu-toggle');
        menuToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const target = document.getElementById(targetId);
                target.classList.toggle('active');
                this.classList.toggle('active');
            });
        });

        // Close menu on nav item click
        const menuLinks = document.querySelectorAll('.menu-nav a, .menu-link, .menu-login, .menu-logout');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                hamburger.classList.remove('active');
                menu.classList.remove('active');
                overlay.classList.remove('active');
            });
        });

        // Update Cart Count from localStorage
        function updateHeaderCartCount() {
            const cartData = localStorage.getItem('vier_korken_cart');
            const cartBadge = document.getElementById('cart-count');

            if (cartBadge) {
                let count = 0;

                if (cartData) {
                    try {
                        const cart = JSON.parse(cartData);
                        if (Array.isArray(cart)) {
                            count = cart.reduce((sum, item) => sum + (item.quantity || 0), 0);
                        }
                    } catch (e) {
                        console.error('Error parsing cart data:', e);
                    }
                }

                cartBadge.textContent = count;
                cartBadge.style.display = count > 0 ? 'flex' : 'none';
            }
        }

        // Initial update
        updateHeaderCartCount();

        // Listen for storage changes (from other tabs)
        window.addEventListener('storage', function(e) {
            if (e.key === 'vier_korken_cart') {
                updateHeaderCartCount();
            }
        });

        // Listen for custom cart update events
        window.addEventListener('cartUpdated', updateHeaderCartCount);
    });
</script>