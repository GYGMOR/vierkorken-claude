<?php
// pages/shop.php - Weinshop mit Kategorien und Produkten
// Der Benutzer kann Weine filtern und in den Warenkorb legen

// KLARA Integration: Kategorien und Artikel von Klara API holen
$category_id = isset($_GET['category']) ? trim($_GET['category']) : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Debug-Modus (entfernen nach Test)
$debug_mode = isset($_GET['debug']);

// Kategorien von Klara API
$categories = klara_get_categories();
if ($debug_mode) {
    echo "<!-- DEBUG: Kategorien gefunden: " . count($categories) . " -->\n";
}

// Artikel von Klara API (mit Filter)
$wines = klara_get_articles($category_id, $search);
if ($debug_mode) {
    echo "<!-- DEBUG: Artikel gefunden: " . count($wines) . " -->\n";
}

// Artikel-Anzahl pro Kategorie berechnen (Optimiert)
$all_articles = klara_get_articles(); // Alle Artikel holen
$category_counts = [];
foreach ($all_articles as $article) {
    foreach ($article['categories'] as $cat_id) {
        if (!isset($category_counts[$cat_id])) {
            $category_counts[$cat_id] = 0;
        }
        $category_counts[$cat_id]++;
    }
}

$current_category = null;
if ($category_id !== '') {
    foreach ($categories as $cat) {
        if ($cat['id'] === $category_id) {
            $current_category = $cat;
            break;
        }
    }
}
?>

<div class="shop-container">
    <!-- Shop Header -->
    <div class="shop-header">
        <h1><span class="icon-text"><?php echo get_icon('wine', 28, 'icon-primary'); ?> Unser Weinangebot</span></h1>
        <p class="shop-subtitle">
            <?php 
            if ($search) {
                echo "Suchergebnisse für: <strong>" . safe_output($search) . "</strong>";
            } elseif ($current_category) {
                echo "Kategorie: <strong>" . safe_output($current_category['name']) . "</strong>";
            } else {
                echo "Entdecken Sie unsere komplette Auswahl";
            }
            ?>
        </p>
    </div>

    <!-- Suchleiste & Filter -->
    <div class="shop-controls">
        <form method="GET" class="search-form">
            <input type="hidden" name="page" value="shop">
            <input type="text" name="search" placeholder="Nach Wein, Produzent suchen..."
                   value="<?php echo safe_output($search); ?>" class="search-input">
            <button type="submit" class="btn btn-primary"><?php echo get_icon('search', 18); ?> Suchen</button>
            <?php if ($search || $category_id !== ''): ?>
                <a href="?page=shop" class="btn btn-secondary"><?php echo get_icon('close', 18); ?> Filter zurücksetzen</a>
            <?php endif; ?>
        </form>

        <!-- Mobile Category Filter (aufklappbar) -->
        <div class="mobile-category-filter">
            <button class="category-toggle-btn" id="mobile-category-toggle">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <span>Kategorien</span>
                <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="mobile-category-list" id="mobile-category-list">
                <a href="?page=shop" class="mobile-cat-link <?php echo ($category_id === '' && !$search) ? 'active' : ''; ?>">
                    Alle Produkte
                </a>

                <?php
                // KLARA Kategorien anzeigen
                foreach ($categories as $cat):
                    if (!$cat['active']) continue;
                    $wine_count = $category_counts[$cat['id']] ?? 0;
                    if ($wine_count > 0):
                ?>
                    <a href="?page=shop&category=<?php echo safe_output($cat['id']); ?>"
                       class="mobile-cat-link <?php echo $category_id === $cat['id'] ? 'active' : ''; ?>">
                        <?php echo safe_output($cat['name']); ?>
                        <span class="mobile-cat-count">(<?php echo $wine_count; ?>)</span>
                    </a>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </div>
    </div>

    <div class="shop-layout">
        <!-- SIDEBAR - Kategorien -->
        <aside class="shop-sidebar">
            <h3>Kategorien</h3>
            <div class="category-list">
                <a href="?page=shop" class="cat-link <?php echo ($category_id === '' && !$search) ? 'active' : ''; ?>">
                    <span class="icon-text"><?php echo get_icon('list', 18); ?> Alle Produkte</span>
                </a>

                <?php
                // KLARA Kategorien anzeigen
                foreach ($categories as $cat):
                    if (!$cat['active']) continue; // Inaktive überspringen
                    $wine_count = $category_counts[$cat['id']] ?? 0;
                    if ($wine_count > 0):
                ?>
                    <a href="?page=shop&category=<?php echo safe_output($cat['id']); ?>"
                       class="cat-link <?php echo $category_id === $cat['id'] ? 'active' : ''; ?>">
                        <?php echo safe_output($cat['name']); ?>
                        <span class="cat-count">(<?php echo $wine_count; ?>)</span>
                    </a>
                <?php
                    endif;
                endforeach;
                ?>
            </div>
        </aside>

        <!-- MAIN CONTENT - Weine Grid -->
        <main class="shop-main">
            <?php if (!empty($wines)): ?>
                <div class="wines-grid">
                    <?php foreach ($wines as $wine): ?>
                        <a href="?page=product&id=<?php echo $wine['id']; ?>" class="wine-card wine-card-link">
                            <!-- Bild Placeholder -->
                            <div class="wine-image-container">
                                <?php if (!empty($wine['image_url'])): ?>
                                    <img src="<?php echo safe_output($wine['image_url']); ?>" alt="<?php echo safe_output($wine['name']); ?>" class="wine-image">
                                <?php else: ?>
                                    <div class="wine-image-placeholder"><?php echo get_icon('wine', 60, 'icon-secondary'); ?></div>
                                <?php endif; ?>
                                <?php if ($wine['stock'] <= 5): ?>
                                    <div class="stock-badge"><?php echo get_icon('warning', 16); ?> Nur noch <?php echo $wine['stock']; ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Wein Info -->
                            <div class="wine-info">
                                <h4 class="wine-name"><?php echo safe_output($wine['name']); ?></h4>
                                
                                <?php if (!empty($wine['producer'])): ?>
                                    <p class="wine-producer"><span class="icon-text"><?php echo get_icon('user', 14); ?> <?php echo safe_output($wine['producer']); ?></span></p>
                                <?php endif; ?>
                                
                                <!-- RATING ANZEIGE -->
                                <?php if ($wine['avg_rating']): ?>
                                    <div class="wine-rating-mini">
                                        <?php echo get_rating_stars($wine['avg_rating'], 5, 14); ?>
                                        <span class="rating-text"><?php echo number_format($wine['avg_rating'], 1); ?> (<?php echo $wine['rating_count']; ?>)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($wine['vintage'])): ?>
                                    <p class="wine-vintage"><span class="icon-text"><?php echo get_icon('calendar', 14); ?> Jahrgang <?php echo $wine['vintage']; ?></span></p>
                                <?php endif; ?>

                                <?php if (!empty($wine['region'])): ?>
                                    <p class="wine-region"><span class="icon-text"><?php echo get_icon('map', 14); ?> <?php echo safe_output($wine['region']); ?></span></p>
                                <?php endif; ?>

                                <?php if (!empty($wine['alcohol_content'])): ?>
                                    <p class="wine-alcohol"><span class="icon-text"><?php echo get_icon('droplet', 14); ?> <?php echo number_format($wine['alcohol_content'], 1); ?>% Vol.</span></p>
                                <?php endif; ?>
                                
                                <!-- Preis & Action -->
                                <div class="wine-footer">
                                    <div class="wine-price">
                                        <span class="price-label">CHF</span>
                                        <span class="price-value"><?php echo number_format($wine['price'], 2); ?></span>
                                    </div>

                                    <div class="wine-actions">
                                        <button onclick="event.preventDefault(); event.stopPropagation(); quickAddToCart(<?php echo $wine['id']; ?>, '<?php echo addslashes($wine['name']); ?>', <?php echo $wine['price']; ?>);"
                                                class="btn-icon-cart"
                                                title="In Warenkorb">
                                            <?php echo get_icon('shopping-cart', 20); ?>
                                        </button>
                                        <button onclick="event.preventDefault(); event.stopPropagation(); toggleWishlist('<?php echo $wine['id']; ?>', '<?php echo addslashes($wine['name']); ?>');"
                                                class="btn-icon-wishlist"
                                                data-wishlist-id="<?php echo $wine['id']; ?>"
                                                title="Zur Merkliste hinzufügen">
                                            <?php echo get_icon('heart', 20); ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>

                <!-- Ergebnis-Info -->
                <div class="shop-footer">
                    <p><?php echo count($wines); ?> Wein(e) gefunden</p>
                </div>

            <?php else: ?>
                <!-- Keine Weine gefunden -->
                <div class="no-results">
                    <div class="no-results-content">
                        <div class="no-results-icon">
                            <?php echo get_icon('search', 60, 'icon-secondary'); ?>
                        </div>
                        <h3>Keine Weine gefunden</h3>
                        <p>
                            <?php 
                            if ($search) {
                                echo "Deine Suche nach \"" . safe_output($search) . "\" ergab keine Ergebnisse.";
                            } else {
                                echo "Diese Kategorie ist leider leer.";
                            }
                            ?>
                        </p>
                        <a href="?page=shop" class="btn btn-primary" style="margin-top: 1rem;">
                            Alle Weine anschauen
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<style>
/* Shop Page Styles */

.shop-container {
    padding: 2rem 0;
}

.shop-header {
    text-align: center;
    margin-bottom: 2rem;
}

.shop-header h1 {
    border-bottom: 3px solid var(--accent-gold);
    padding-bottom: 1rem;
}

.shop-subtitle {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-top: 1rem;
}

/* Suchleiste */
.shop-controls {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.search-form {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.search-input {
    flex: 1;
    min-width: 250px;
    padding: 0.8rem 1.2rem;
    border: 2px solid var(--border-color);
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.search-input:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Shop Layout */
.shop-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 2rem;
}

/* Sidebar */
.shop-sidebar {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    height: fit-content;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: sticky;
    top: 100px;
}

.shop-sidebar h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: var(--primary-color);
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.cat-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.8rem 1rem;
    border-left: 3px solid transparent;
    background: var(--bg-light);
    border-radius: 5px;
    transition: all 0.3s ease;
    color: var(--text-dark);
}

.cat-link:hover {
    background: #f0ebe5;
    border-left-color: var(--accent-gold);
}

.cat-link.active {
    background: var(--primary-color);
    color: white;
    border-left-color: var(--accent-gold);
    font-weight: 600;
}

.cat-count {
    background: rgba(0,0,0,0.1);
    padding: 0.2rem 0.6rem;
    border-radius: 3px;
    font-size: 0.85rem;
}

.cat-link.active .cat-count {
    background: rgba(255,255,255,0.3);
}

.cat-section-header {
    font-weight: 700;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-light);
    padding: 1rem 1rem 0.5rem;
    margin-top: 0.5rem;
    border-top: 1px solid #f0f0f0;
}

.cat-section-header:first-of-type {
    margin-top: 0;
    border-top: none;
}

/* Main Content */
.shop-main {
    min-height: 400px;
}

/* Weine Grid */
.wines-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 1.5rem;
}

.wine-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
}

.wine-card-link {
    cursor: pointer;
}

.wine-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(114, 44, 44, 0.15);
}

.wine-image-container {
    position: relative;
    height: 200px;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.wine-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wine-image-placeholder {
    font-size: 5rem;
}

.stock-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: var(--warning);
    color: white;
    padding: 0.5rem 0.8rem;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: 600;
}

.wine-info {
    padding: 1.2rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.wine-name {
    margin: 0 0 0.5rem 0;
    font-size: 1rem;
    color: var(--primary-color);
}

.wine-producer {
    font-size: 0.85rem;
    color: var(--text-light);
    margin: 0.2rem 0;
}

.wine-rating-mini {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.rating-stars {
    color: #ffc107;
    font-size: 0.95rem;
    font-weight: 600;
}

.rating-text {
    font-size: 0.8rem;
    color: var(--text-light);
}

.wine-vintage,
.wine-region,
.wine-alcohol {
    font-size: 0.85rem;
    color: var(--text-light);
    margin: 0.2rem 0;
}

.wine-footer {
    margin-top: auto;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.wine-price {
    display: flex;
    align-items: baseline;
    gap: 0.3rem;
    margin-bottom: 0.8rem;
}

.price-label {
    font-size: 0.85rem;
    color: var(--text-light);
}

.price-value {
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* No Results */
.no-results {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border-radius: 10px;
}

.no-results-emoji {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.no-results h3 {
    margin-top: 0;
}

.no-results p {
    color: var(--text-light);
}

/* Shop Footer */
.shop-footer {
    text-align: center;
    margin-top: 2rem;
    padding: 1rem;
    color: var(--text-light);
}

/* Quick Action Buttons */
.wine-actions {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.btn-icon-cart,
.btn-icon-wishlist {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 2px solid var(--primary-color);
    background: white;
    color: var(--primary-color);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-icon-cart:hover {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
}

.btn-icon-wishlist {
    border-color: #e74c3c;
    color: #e74c3c;
}

.btn-icon-wishlist:hover {
    background: #e74c3c;
    color: white;
    transform: scale(1.1);
}

.btn-icon-wishlist.active {
    background: #e74c3c;
    color: white;
}

/* Mobile Category Filter - Aufklappbar unter Suchbalken */
.mobile-category-filter {
    display: none; /* Standardmäßig versteckt, auf Mobile sichtbar */
    margin-top: 1rem;
}

.category-toggle-btn {
    width: 100%;
    padding: 0.9rem 1.2rem;
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 1rem;
    font-weight: 500;
    color: var(--text-dark);
}

.category-toggle-btn:hover {
    border-color: var(--primary-color);
    background: #f8f9fa;
}

.category-toggle-btn svg {
    color: var(--primary-color);
}

.category-toggle-btn .toggle-icon {
    margin-left: auto;
    transition: transform 0.3s ease;
}

.category-toggle-btn.active .toggle-icon {
    transform: rotate(180deg);
}

.mobile-category-list {
    display: none;
    margin-top: 0.8rem;
    background: white;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}

.mobile-category-list.active {
    display: block;
    animation: slideDown 0.3s ease-out;
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

.mobile-cat-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.2rem;
    color: var(--text-dark);
    text-decoration: none;
    border-bottom: 1px solid var(--border-color);
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.mobile-cat-link:last-child {
    border-bottom: none;
}

.mobile-cat-link:hover {
    background: #f8f9fa;
    padding-left: 1.5rem;
}

.mobile-cat-link.active {
    background: var(--primary-color);
    color: white;
    font-weight: 600;
}

.mobile-cat-count {
    background: rgba(0,0,0,0.1);
    padding: 0.2rem 0.6rem;
    border-radius: 4px;
    font-size: 0.85rem;
}

.mobile-cat-link.active .mobile-cat-count {
    background: rgba(255,255,255,0.3);
}

/* Responsive Design - Perfekt für alle Geräte */

/* Tablet (1024px und kleiner) */
@media (max-width: 1024px) {
    .shop-layout {
        grid-template-columns: 200px 1fr;
    }

    .wines-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }

    /* Mobile Category Filter auf Tablets anzeigen */
    .mobile-category-filter {
        display: block;
    }

    /* Desktop Sidebar auf Tablets verstecken */
    .shop-sidebar {
        display: none;
    }
}

/* Mobile (768px und kleiner) */
@media (max-width: 768px) {
    .shop-container {
        padding: 1rem 0;
    }

    .shop-header h1 {
        font-size: 1.5rem;
    }

    .shop-layout {
        grid-template-columns: 1fr;
    }

    /* Mobile Category Filter anzeigen */
    .mobile-category-filter {
        display: block;
    }

    /* Desktop Sidebar verstecken */
    .shop-sidebar {
        display: none;
    }

    .search-form {
        flex-direction: column;
    }

    .search-input {
        min-width: auto;
        width: 100%;
    }

    .search-form .btn {
        width: 100%;
    }

    .wines-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}

/* Small Mobile (480px und kleiner) */
@media (max-width: 480px) {
    .shop-header h1 {
        font-size: 1.3rem;
    }

    .shop-subtitle {
        font-size: 0.9rem;
    }

    .wines-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.8rem;
    }

    .wine-card {
        font-size: 0.9rem;
    }

    .wine-image-container {
        height: 150px;
    }

    .wine-footer {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.8rem;
    }

    .btn-icon-cart,
    .btn-icon-wishlist {
        width: 36px;
        height: 36px;
    }

    .category-toggle-btn {
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
    }

    .mobile-cat-link {
        padding: 0.8rem 1rem;
        font-size: 0.9rem;
    }
}

/* Extra Small Mobile (360px und kleiner) */
@media (max-width: 360px) {
    .wines-grid {
        grid-template-columns: 1fr;
    }

    .shop-controls {
        padding: 1rem;
    }
}
</style>

<script>
// Quick Add to Cart
function quickAddToCart(wineId, wineName, price) {
    if (typeof cart !== 'undefined') {
        cart.addItem(wineId, wineName, price, 1);
    } else {
        console.error('Cart system not loaded');
        alert('Warenkorb-System nicht verfügbar');
    }
}

// Wishlist Toggle
function toggleWishlist(wineId) {
    const btn = document.getElementById('wishlist-btn-' + wineId);

    fetch('api/wishlist.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=toggle&wine_id=' + wineId
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.added) {
                btn.classList.add('active');
                showNotification('Zur Merkliste hinzugefügt', 'success');
            } else {
                btn.classList.remove('active');
                showNotification('Von Merkliste entfernt', 'success');
            }
        } else {
            alert('Fehler: ' + (data.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Speichern');
    });
}

// Load wishlist status on page load
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['user_id'])): ?>
    fetch('api/wishlist.php?action=get_all')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.wine_ids) {
                data.wine_ids.forEach(wineId => {
                    const btn = document.getElementById('wishlist-btn-' + wineId);
                    if (btn) {
                        btn.classList.add('active');
                    }
                });
            }
        })
        .catch(e => console.error('Error loading wishlist:', e));
    <?php endif; ?>

    // Mobile Category Toggle
    const categoryToggleBtn = document.getElementById('mobile-category-toggle');
    const categoryList = document.getElementById('mobile-category-list');

    if (categoryToggleBtn && categoryList) {
        categoryToggleBtn.addEventListener('click', function() {
            this.classList.toggle('active');
            categoryList.classList.toggle('active');
        });
    }
});
</script>