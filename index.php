<?php
// index.php - FINAL VERSION mit neuem Mobile Header

ob_start();
session_start();

require_once 'config/database.php';
require_once 'includes/functions.php';
require_once 'includes/editable.php';
require_once 'includes/icons.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?page=home');
    exit;
}

$page = isset($_GET['page']) ? trim($_GET['page']) : 'home';

$allowed_pages = [
    'home', 'shop', 'product', 'cart', 'checkout',
    'admin-login', 'admin-dashboard', 'admin-theme',
    'user-portal',
    'impressum', 'agb', 'datenschutz', 'newsletter', 'contact'
];

if (!in_array($page, $allowed_pages)) {
    $page = 'home';
}

if ($page === 'admin-dashboard' && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) {
    header('Location: ?page=home');
    exit;
}

if ($page === 'admin-theme' && (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin'])) {
    header('Location: ?page=home');
    exit;
}

// Theme Farben laden
$theme_colors = get_all_theme_settings();

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Vier Korken - Die feinste Schweizer Weinauswahl">
    <meta name="keywords" content="Wein, Schweiz, Rotwein, Weißwein, Rosé, Online Shop">
    
    <title>
        <?php
        $titles = [
            'home' => 'Vier Korken - Schweizer Wein',
            'shop' => 'Weinshop - Vier Korken',
            'product' => 'Produkt - Vier Korken',
            'cart' => 'Warenkorb - Vier Korken',
            'checkout' => 'Checkout - Vier Korken',
            'admin-theme' => 'Design-Einstellungen - Admin',
            'user-portal' => 'Mein Account - Vier Korken',
            'impressum' => 'Impressum - Vier Korken',
            'agb' => 'AGB - Vier Korken',
            'newsletter' => 'Newsletter - Vier Korken',
            'datenschutz' => 'Datenschutz - Vier Korken',
            'contact' => 'Kontakt - Vier Korken'
        ];
        echo $titles[$page] ?? 'Vier Korken';
        ?>
    </title>
    
    <!-- CSS in der richtigen Reihenfolge -->
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/icons.css">
    <link rel="stylesheet" href="assets/css/dynamic-colors.php">
    <link rel="stylesheet" href="assets/css/responsive.css">
    
    <!-- Favicon - Professional ohne Emoji -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%23693a15' width='100' height='100' rx='20'/><text x='50' y='70' font-size='50' fill='%23ead39c' text-anchor='middle' font-family='serif' font-weight='bold'>V</text></svg>">
    
    <!-- CSS Variablen für Theme -->
    <style>
        :root {
            --primary-color: <?php echo $theme_colors['header_bg_color'] ?? '#693a15'; ?>;
            --accent-color: <?php echo $theme_colors['header_accent_color'] ?? '#ead39c'; ?>;
            --text-color: <?php echo $theme_colors['header_text_color'] ?? '#ffffff'; ?>;
        }
    </style>
</head>
<body>
    <!-- HEADER (Neuer mobiler Header) -->
    <?php include 'includes/header.php'; ?>
    
    <!-- MAIN CONTENT -->
    <main>
        <div class="container">
            <?php
            $page_file = "pages/$page.php";
            if (file_exists($page_file)) {
                include $page_file;
            } else {
                include "pages/home.php";
            }
            ?>
        </div>
    </main>
    
    <!-- FOOTER -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- LOGIN MODAL -->
    <?php if (isset($_GET['modal']) && $_GET['modal'] === 'login'): ?>
        <?php include 'includes/login-modal.php'; ?>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/cart.js"></script>
    
    <?php 
    if (isset($_SESSION['user_id']) && isset($_GET['modal']) && $_GET['modal'] === 'login') {
        echo "<script>
            setTimeout(function() {
                window.location.href = '?page=user-portal';
            }, 1000);
        </script>";
    }
    ?>
    
</body>
</html>
<?php ob_end_flush(); ?>