<?php
// pages/product.php - Einzelwein Produktseite mit Ratings

$wine_id = (int)($_GET['id'] ?? 0);

if ($wine_id <= 0) {
    echo '<div class="alert alert-error">Wein nicht gefunden</div>';
    exit;
}

$wine = get_wine_by_id($wine_id);

if (!$wine) {
    echo '<div class="alert alert-error">Dieser Wein existiert nicht</div>';
    exit;
}

$category = get_category_name($wine['category_id']);
?>

<div class="product-page">
    <!-- BREADCRUMB -->
    <div class="breadcrumb">
        <a href="?page=home">Home</a> / 
        <a href="?page=shop">Shop</a> / 
        <a href="?page=shop&category=<?php echo $wine['category_id']; ?>"><?php echo safe_output($category); ?></a> / 
        <span><?php echo safe_output($wine['name']); ?></span>
    </div>

    <div class="product-container">
        <!-- LINKE SEITE - BILD -->
        <div class="product-image-section">
            <div class="product-image-container">
                <?php if (!empty($wine['image_url'])): ?>
                    <img src="<?php echo safe_output($wine['image_url']); ?>" alt="<?php echo safe_output($wine['name']); ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image-placeholder">🍷</div>
                <?php endif; ?>
            </div>

            <!-- RATING STERNE (kurz) -->
            <div class="product-rating-mini">
                <?php if ($wine['avg_rating']): ?>
                    <div style="color: #ffc107; font-size: 1.2rem; margin-bottom: 0.5rem;">
                        <?php 
                        $full = floor($wine['avg_rating']);
                        echo str_repeat('★', $full) . str_repeat('☆', 5 - $full);
                        ?>
                    </div>
                    <p style="margin: 0; color: var(--text-light);">
                        <strong><?php echo number_format($wine['avg_rating'], 1); ?>/5</strong> 
                        (<?php echo $wine['rating_count']; ?> Bewertungen)
                    </p>
                <?php else: ?>
                    <p style="margin: 0; color: var(--text-light);">Noch keine Bewertungen</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- RECHTE SEITE - DETAILS -->
        <div class="product-details-section">
            <h1 id="wine-name"><?php echo safe_output($wine['name']); ?></h1>
            
            <p class="product-category">
                <?php echo safe_output($category); ?>
                <?php if ($wine['vintage']): ?>
                    • <strong>Jahrgang:</strong> <?php echo $wine['vintage']; ?>
                <?php endif; ?>
            </p>

            <!-- PRODUCER -->
            <?php if ($wine['producer']): ?>
                <p class="product-producer">
                    <strong>Produzent:</strong> <?php echo safe_output($wine['producer']); ?>
                </p>
            <?php endif; ?>

            <!-- PREIS -->
            <div class="product-price">
                <h2 id="wine-price">CHF <?php echo number_format($wine['price'], 2); ?></h2>
            </div>

            <!-- STOCK STATUS -->
            <div class="product-stock">
                <?php if ($wine['stock'] > 0): ?>
                    <span class="stock-available">✓ Lagerbestand: <?php echo $wine['stock']; ?> Stück</span>
                <?php else: ?>
                    <span class="stock-unavailable">✗ Nicht verfügbar</span>
                <?php endif; ?>
            </div>

            <!-- BESCHREIBUNG -->
            <?php if ($wine['description']): ?>
                <div class="product-description">
                    <h3>Beschreibung</h3>
                    <p><?php echo safe_output($wine['description']); ?></p>
                </div>
            <?php endif; ?>

            <!-- DETAILS TABELLE -->
            <div class="product-specs">
                <h3>Eigenschaften</h3>
                <table class="specs-table">
                    <?php if ($wine['region']): ?>
                        <tr>
                            <td><strong>Region:</strong></td>
                            <td><?php echo safe_output($wine['region']); ?></td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php if ($wine['alcohol_content']): ?>
                        <tr>
                            <td><strong>Alkoholgehalt:</strong></td>
                            <td><?php echo $wine['alcohol_content']; ?>%</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php if ($wine['volume_ml']): ?>
                        <tr>
                            <td><strong>Volumen:</strong></td>
                            <td><?php echo $wine['volume_ml']; ?> ml</td>
                        </tr>
                    <?php endif; ?>
                    
                    <?php if ($wine['sku']): ?>
                        <tr>
                            <td><strong>SKU:</strong></td>
                            <td><?php echo safe_output($wine['sku']); ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>

            <!-- ADD TO CART FORM -->
            <div class="product-actions">
                <?php if ($wine['stock'] > 0): ?>
                    <div class="add-to-cart-form">
                        <div class="quantity-selector">
                            <label for="quantity">Menge:</label>
                            <input type="number" id="quantity" name="quantity" min="1" max="<?php echo $wine['stock']; ?>" value="1">
                        </div>
                        <button type="button" class="btn btn-primary btn-large" onclick="addToCart(<?php echo $wine_id; ?>, '<?php echo addslashes($wine['name']); ?>', <?php echo $wine['price']; ?>, document.getElementById('quantity').value); document.getElementById('quantity').value = 1;">
                            🛒 In den Warenkorb
                        </button>
                    </div>

                    <!-- FAVORITE BUTTON -->
                    <button id="favorite-btn-<?php echo $wine_id; ?>" class="btn btn-secondary btn-favorite" onclick="toggleFavorite(<?php echo $wine_id; ?>)">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <span id="favorite-icon-<?php echo $wine_id; ?>">❤️ Zu Favoriten</span>
                        <?php else: ?>
                            <span>❤️ Zu Favoriten (Anmelden nötig)</span>
                        <?php endif; ?>
                    </button>
                <?php else: ?>
                    <div class="alert alert-warning">
                        Dieser Wein ist leider momentan nicht verfügbar. Bitte versuchen Sie es später erneut.
                    </div>
                <?php endif; ?>
            </div>

            <!-- VERSANDINFO -->
            <div class="product-shipping-info">
                <p>📦 <strong>Versand:</strong> <?php echo get_setting('shipping_days', '2-3 Arbeitstage'); ?></p>
                <p>💳 <strong>Versandkosten:</strong> CHF <?php echo get_setting('shipping_cost', '15.00'); ?></p>
            </div>
        </div>
    </div>

    <!-- RATING SECTION -->
    <div class="product-rating-full">
        <?php 
        if (file_exists('components/wine-rating-section.php')) {
            include 'components/wine-rating-section.php';
        }
        ?>
    </div>

    <!-- ÄHNLICHE WEINE -->
    <div class="similar-wines-section">
        <h2>Ähnliche Weine</h2>
        
        <?php
        $similar = $db->query("
            SELECT * FROM wines 
            WHERE category_id = {$wine['category_id']} 
            AND id != $wine_id 
            AND stock > 0
            ORDER BY RAND()
            LIMIT 4
        ")->fetch_all(MYSQLI_ASSOC);
        
        if (count($similar) > 0):
        ?>
            <div class="wines-grid">
                <?php foreach ($similar as $w): ?>
                    <div class="wine-card">
                        <div class="wine-image-placeholder">🍷</div>
                        <div class="wine-info">
                            <h4><?php echo safe_output($w['name']); ?></h4>
                            <p class="wine-producer"><?php echo safe_output($w['producer'] ?? 'Schweizer Wein'); ?></p>
                            
                            <?php if ($w['avg_rating']): ?>
                                <div style="color: #ffc107; font-size: 0.9rem; margin: 0.5rem 0;">
                                    ★ <?php echo number_format($w['avg_rating'], 1); ?> (<?php echo $w['rating_count']; ?>)
                                </div>
                            <?php endif; ?>
                            
                            <p class="wine-price">CHF <?php echo number_format($w['price'], 2); ?></p>
                            <a href="?page=product&id=<?php echo $w['id']; ?>" class="btn btn-secondary btn-small">Anschauen</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.product-page {
    padding: 2rem 0;
}

.breadcrumb {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 2rem;
}

.breadcrumb a {
    color: var(--primary-color);
}

.product-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.product-image-section {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.product-image-container {
    border-radius: 10px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
}

.product-image {
    width: 100%;
    height: auto;
    display: block;
}

.product-image-placeholder {
    width: 100%;
    aspect-ratio: 3/4;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 5rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
}

.product-rating-mini {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
}

.product-details-section h1 {
    border: none;
    padding-bottom: 0;
    margin-bottom: 0.5rem;
    font-size: 2rem;
}

.product-category {
    color: var(--text-light);
    font-size: 0.95rem;
    margin-bottom: 1rem;
}

.product-producer {
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.product-price h2 {
    margin: 0;
    color: var(--primary-color);
    border: none;
    font-size: 2rem;
}

.product-stock {
    margin: 1.5rem 0;
}

.stock-available {
    background: #d4edda;
    color: #155724;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: 600;
}

.stock-unavailable {
    background: #f8d7da;
    color: #842029;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    font-weight: 600;
}

.product-description {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.product-description h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.product-specs {
    margin: 2rem 0;
}

.product-specs h3 {
    color: var(--primary-color);
}

.specs-table {
    width: 100%;
    border-collapse: collapse;
}

.specs-table tr {
    border-bottom: 1px solid #f0f0f0;
}

.specs-table td {
    padding: 0.8rem 0;
}

.product-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    margin: 2rem 0;
}

.add-to-cart-form {
    display: flex;
    gap: 1rem;
    align-items: flex-end;
}

.quantity-selector {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.quantity-selector label {
    font-weight: 600;
    color: var(--primary-color);
}

.quantity-selector input {
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    width: 100px;
    font-size: 1rem;
}

.btn-large {
    padding: 1rem 2rem !important;
    font-size: 1.1rem !important;
    flex: 1;
}

.btn-favorite {
    padding: 0.8rem 1.5rem !important;
    width: 100%;
}

.product-shipping-info {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
    border-left: 4px solid var(--accent-gold);
}

.product-shipping-info p {
    margin: 0.5rem 0;
}

.product-rating-full {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 3rem;
}

.similar-wines-section {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.similar-wines-section h2 {
    border: none;
    margin-top: 0;
}

@media (max-width: 768px) {
    .product-container {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .add-to-cart-form {
        flex-direction: column;
    }
    
    .btn-large {
        width: 100% !important;
    }
}
</style>

<script>
// Add to Cart
function addToCart(wineId, wineName, price, quantity) {
    quantity = parseInt(quantity) || 1;
    console.log('addToCart:', {wineId, wineName, price, quantity});
    
    if (typeof cart !== 'undefined' && cart.addItem) {
        cart.addItem(wineId, wineName, price, quantity);
        document.getElementById('quantity').value = 1;
    } else {
        console.error('cart.js nicht geladen!');
        alert('Fehler: Warenkorb nicht initialisiert');
    }
}

// Favorite Toggle
function toggleFavorite(wineId) {
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        window.location.href = '?modal=login';
        return;
    }
    
    const icon = document.getElementById('favorite-icon-' + wineId);
    
    fetch('api/user-portal.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add_favorite&wine_id=' + wineId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            icon.textContent = '❌ Aus Favoriten';
        }
    })
    .catch(e => console.error('Fehler:', e));
}
</script>