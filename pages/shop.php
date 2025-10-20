<?php
// pages/shop.php - Weinshop mit Kategorien und Produkten
// Der Benutzer kann Weine filtern und in den Warenkorb legen

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$categories = get_all_categories();

$wines = [];
if ($search) {
    $search_safe = $db->real_escape_string($search);
    $query = "SELECT * FROM wines 
              WHERE (name LIKE '%$search_safe%' OR description LIKE '%$search_safe%' OR producer LIKE '%$search_safe%')
              AND stock > 0 
              ORDER BY name ASC";
} elseif ($category_id > 0) {
    $query = "SELECT * FROM wines 
              WHERE category_id = $category_id AND stock > 0 
              ORDER BY name ASC";
} else {
    $query = "SELECT * FROM wines WHERE stock > 0 ORDER BY name ASC";
}

$result = $db->query($query);
if ($result) {
    $wines = $result->fetch_all(MYSQLI_ASSOC);
}

$current_category = null;
if ($category_id > 0) {
    $current_category = array_filter($categories, fn($c) => $c['id'] == $category_id);
    $current_category = reset($current_category);
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
            <?php if ($search || $category_id > 0): ?>
                <a href="?page=shop" class="btn btn-secondary"><?php echo get_icon('close', 18); ?> Filter zurücksetzen</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="shop-layout">
        <!-- SIDEBAR - Kategorien -->
        <aside class="shop-sidebar">
            <h3>Kategorien</h3>
            <div class="category-list">
                <a href="?page=shop" class="cat-link <?php echo ($category_id == 0 && !$search) ? 'active' : ''; ?>">
                    <span class="icon-text"><?php echo get_icon('list', 18); ?> Alle Weine</span>
                </a>
                
                <?php foreach ($categories as $cat): 
                    $wine_count = count_wines_in_category($cat['id']);
                    if ($wine_count > 0):
                ?>
                    <a href="?page=shop&category=<?php echo $cat['id']; ?>" 
                       class="cat-link <?php echo $category_id == $cat['id'] ? 'active' : ''; ?>">
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

/* Responsive */
@media (max-width: 1024px) {
    .shop-layout {
        grid-template-columns: 200px 1fr;
    }
    
    .wines-grid {
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    }
}

@media (max-width: 768px) {
    .shop-layout {
        grid-template-columns: 1fr;
    }
    
    .shop-sidebar {
        position: static;
        top: auto;
    }
    
    .search-form {
        flex-direction: column;
    }
    
    .search-input {
        min-width: auto;
    }
    
    .wines-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
    }
}

@media (max-width: 480px) {
    .wines-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>