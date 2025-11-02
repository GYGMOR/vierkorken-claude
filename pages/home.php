<?php
// pages/home.php - Startseite mit optimiertem Design + neue Features
require_once 'includes/editable.php';

// EDIT_MODE Fehler beheben
if (!isset($EDIT_MODE)) {
    $EDIT_MODE = $_SESSION['edit_mode'] ?? false;
}

$hero_title = get_setting('hero_title', 'Willkommen bei Vier Korken');
$hero_subtitle = get_setting('hero_subtitle', 'Die feinste Schweizer Weinauswahl für Sie');
$about_title = get_setting('about_title', 'Warum Vier Korken?');

$header_banner_image = get_setting('header_banner_image', '');
$hero_background_image = get_setting('hero_background_image', '');
$about_image = get_setting('about_section_image', '');
$about_text = get_setting('about_section_text', 'Willkommen bei Vier Korken, Ihrem Zugang zu den erlesenen Weinen der Schweiz.');
$about_shop_link = get_setting('about_shop_link', '?page=shop');

// Neuheiten laden - Alle Featured Items (Klara-Produkte, Events, Custom News)
$featured_products = get_klara_featured_products(10);
$featured_events = get_featured_events(10);
$custom_news = get_custom_news(10);

// Konvertiere alle in News-Item Format
$news_items = [];

// Klara-Produkte
foreach ($featured_products as $product) {
    $news_items[] = [
        'id' => $product['id'],
        'type' => 'wine',
        'title' => $product['name'],
        'description' => $product['short_description'] ?? '',
        'image_url' => $product['image_url'] ?? 'assets/images/placeholder-wine.jpg',
        'link_url' => '?page=product&id=' . $product['id'],
        'price' => $product['price'],
        'bg_color' => $product['featured_bg_color'] ?? '#722c2c',
        'text_color' => $product['featured_text_color'] ?? '#ffffff',
        'wine_data' => $product
    ];
}

// Events
foreach ($featured_events as $event) {
    $news_items[] = [
        'id' => $event['id'],
        'type' => 'event',
        'title' => $event['name'] ?? $event['title'] ?? 'Event',
        'content' => $event['description'] ?? '',
        'image_url' => $event['image_url'] ?? 'assets/images/placeholder-event.jpg',
        'link_url' => '?page=event&id=' . $event['id'],
        'price' => $event['price'],
        'bg_color' => $event['featured_bg_color'] ?? '#2c5282',
        'text_color' => $event['featured_text_color'] ?? '#ffffff',
        'event_data' => $event
    ];
}

// Custom News/Aktionen
foreach ($custom_news as $news) {
    $news_items[] = [
        'id' => $news['id'],
        'type' => 'news',
        'title' => $news['title'],
        'content' => $news['content'],
        'image_url' => $news['image_url'] ?? 'assets/images/placeholder-news.jpg',
        'link_url' => '#',
        'price' => null,
        'bg_color' => $news['featured_bg_color'] ?? '#c27c0e',
        'text_color' => $news['featured_text_color'] ?? '#ffffff'
    ];
}

// Begrenze auf 6 Items für die Anzeige
$news_items = array_slice($news_items, 0, 6);
?>

<!-- HEADER BANNER -->
<?php if (!empty($header_banner_image)): ?>
<div class="header-banner-section">
    <div class="header-banner" style="background-image: url('<?php echo safe_output($header_banner_image); ?>');">
        <div class="header-banner-overlay"></div>
    </div>
</div>
<?php endif; ?>

<!-- HERO SECTION -->
<div class="home-hero <?php echo !empty($hero_background_image) ? 'has-background' : ''; ?>"
     <?php if (!empty($hero_background_image)): ?>
     style="background-image: url('<?php echo safe_output($hero_background_image); ?>'); background-size: cover; background-position: center;"
     <?php endif; ?>>
    
    <div class="hero-overlay"></div>
    
    <div class="hero-content">
        <?php if ($EDIT_MODE): ?>
            <div style="position: relative; margin-bottom: 1rem;">
                <span class="edit-badge">Hero Title</span>
            </div>
        <?php endif; ?>
        
        <?php editable('hero_title', $hero_title, 'h1'); ?>
        
        <?php if ($EDIT_MODE): ?>
            <div style="position: relative; margin-bottom: 1rem;">
                <span class="edit-badge">Hero Subtitle</span>
            </div>
        <?php endif; ?>
        
        <?php editable_textarea('hero_subtitle', $hero_subtitle, 'p', ['hero-subtitle']); ?>
        
        <a href="?page=shop" class="btn btn-primary"><?php echo get_setting('hero_button_text', 'Jetzt zum Shop'); ?></a>
    </div>
</div>

<!-- NEWS / NEUHEITEN SECTION -->
<section class="news-section">
    <div class="container">
        <h2>News / Neuheiten</h2>

        <div class="news-scroll-container">
            <div class="news-grid-horizontal">
            <?php if (!empty($news_items)): ?>
                <?php foreach ($news_items as $item): ?>
                    <?php
                    // Bestimme Badge-Text basierend auf Typ
                    $badge_text = 'Neuheit';
                    if ($item['type'] === 'wine') {
                        $badge_text = $item['wine_data']['cat_name'] ?? 'Wein';
                    } elseif ($item['type'] === 'event') {
                        $badge_text = 'Event';
                    }

                    // Bestimme Link
                    $link = $item['link_url'] ?: '#';
                    ?>

                    <?php
                    // Individuelle Farben aus Datenbank
                    $bg_color = $item['bg_color'] ?? '#722c2c';
                    $text_color = $item['text_color'] ?? '#ffffff';
                    $card_style = "background: {$bg_color}; color: {$text_color};";
                    ?>
                    <a href="<?php echo safe_output($link); ?>" class="news-card news-card-link" style="<?php echo $card_style; ?>">
                        <div class="news-badge" style="background: rgba(255,255,255,0.2); color: <?php echo $text_color; ?>;">
                            <?php echo safe_output($badge_text); ?>
                        </div>

                        <div class="news-image">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?php echo safe_output($item['image_url']); ?>" alt="<?php echo safe_output($item['title']); ?>">
                            <?php else: ?>
                                <div class="wine-image-placeholder">
                                    <?php
                                    $icon = $item['type'] === 'event' ? 'calendar' : 'wine';
                                    echo get_icon($icon, 60, 'icon-secondary');
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php
                            // Kantonswappen anzeigen (nur bei Weinen)
                            if ($item['type'] === 'wine' && !empty($item['wine_data']['kanton'])):
                                require_once 'includes/kantone.php';
                            ?>
                                <div class="kanton-wappen-badge">
                                    <?php echo render_kanton_wappen($item['wine_data']['kanton'], 30); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="news-info">
                            <h3><?php echo safe_output($item['title']); ?></h3>

                            <?php if ($item['type'] === 'wine' && isset($item['wine_data'])): ?>
                                <!-- WEIN-SPEZIFISCHE INFORMATIONEN -->
                                <?php $wine = $item['wine_data']; ?>

                                <?php if (!empty($wine['producer'])): ?>
                                    <p class="wine-producer"><?php echo safe_output($wine['producer']); ?></p>
                                <?php endif; ?>

                                <?php if (!empty($wine['short_description'])): ?>
                                    <p class="wine-description"><?php echo safe_output(substr($wine['short_description'], 0, 100)); ?><?php echo strlen($wine['short_description']) > 100 ? '...' : ''; ?></p>
                                <?php endif; ?>

                                <div class="wine-details">
                                    <?php if (!empty($wine['vintage'])): ?>
                                        <span class="detail-item"><?php echo get_icon('calendar', 14); ?> <?php echo intval($wine['vintage']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($wine['region'])): ?>
                                        <span class="detail-item"><?php echo get_icon('map-pin', 14); ?> <?php echo safe_output($wine['region']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($wine['alcohol_content'])): ?>
                                        <span class="detail-item"><?php echo get_icon('droplet', 14); ?> <?php echo number_format($wine['alcohol_content'], 1); ?>%</span>
                                    <?php endif; ?>
                                </div>

                                <p class="wine-price"><strong><?php echo format_price($wine['price']); ?></strong></p>

                            <?php elseif ($item['type'] === 'event' && isset($item['event_data'])): ?>
                                <!-- EVENT-SPEZIFISCHE INFORMATIONEN -->
                                <?php $event = $item['event_data']; ?>
                                <p class="event-date">
                                    <?php echo date('d.m.Y, H:i', strtotime($event['event_date'])); ?> Uhr
                                </p>
                                <?php if (!empty($event['location'])): ?>
                                    <p class="event-location"><?php echo safe_output($event['location']); ?></p>
                                <?php endif; ?>
                                <p class="wine-price"><?php echo format_price($event['price']); ?> / Ticket</p>
                                <p class="event-tickets">
                                    <?php echo $event['available_tickets']; ?> Tickets verfügbar
                                </p>

                            <?php else: ?>
                                <!-- ALLGEMEINE NEWS -->
                                <?php if (!empty($item['content'])): ?>
                                    <p class="news-content"><?php echo safe_output(substr($item['content'], 0, 100)); ?><?php echo strlen($item['content']) > 100 ? '...' : ''; ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: var(--text-light);">
                    <?php if ($EDIT_MODE): ?>
                        <p>📌 Noch keine Neuheiten hinzugefügt. Im Admin-Panel News-Items erstellen.</p>
                    <?php else: ?>
                        <p>Noch keine Neuheiten verfügbar.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            </div>
        </div>

        <!-- Admin: Neuheiten-Management -->
        <?php if ($EDIT_MODE): ?>
            <div class="featured-wines-admin" style="margin-top: 3rem; background: #f0f0f0; padding: 2rem; border-radius: 10px;">
                <h4>📌 Neuheiten verwalten</h4>
                
                <div class="featured-section">
                    <h5>Wein hinzufügen:</h5>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Aus existierenden Weinen wählen:</label>
                            <select id="featured-wine-select" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ccc;">
                                <option value="">-- Wein auswählen --</option>
                                <?php
                                $all_wines = $db->query("SELECT id, name, producer FROM wines ORDER BY name ASC");
                                while ($w = $all_wines->fetch_assoc()):
                                ?>
                                    <option value="<?php echo $w['id']; ?>"><?php echo safe_output($w['name']); ?> (<?php echo safe_output($w['producer']); ?>)</option>
                                <?php endwhile; ?>
                            </select>
                            <button onclick="addFeaturedWine()" class="btn btn-success" style="margin-top: 0.5rem;">Hinzufügen</button>
                        </div>
                        
                        <div>
                            <label>oder neuen Wein erstellen:</label>
                            <input type="text" id="new-wine-name" placeholder="Weinname" style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ccc;">
                            <button onclick="createNewFeaturedWine()" class="btn btn-success" style="margin-top: 0.5rem;">Neu erstellen & hinzufügen</button>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <h5>Aktuelle Neuheiten:</h5>
                    <div id="featured-wines-list"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ABOUT SECTION -->
<section class="about-showcase-section">
    <div class="container">
        <div class="about-showcase-grid">
            <div class="about-image-side">
                <?php if (!empty($about_image)): ?>
                    <a href="?page=shop" class="about-image-link">
                        <img src="<?php echo safe_output($about_image); ?>" alt="Über uns" class="about-showcase-image">
                        <div class="about-image-overlay">
                            <span class="about-image-cta">Zum Shop →</span>
                        </div>
                    </a>
                <?php else: ?>
                    <a href="?page=shop" class="about-image-link">
                        <div class="about-image-placeholder">
                            <?php if ($EDIT_MODE): ?>
                                <p>Bild hochladen</p>
                            <?php else: ?>
                                <p><?php echo get_icon('wine', 64, 'icon-secondary'); ?></p>
                                <p style="margin-top: 1rem; font-size: 1.1rem; color: var(--primary-color); font-weight: 600;">Zum Shop</p>
                            <?php endif; ?>
                        </div>
                        <?php if (!$EDIT_MODE): ?>
                        <div class="about-image-overlay">
                            <span class="about-image-cta">Zum Shop →</span>
                        </div>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($EDIT_MODE): ?>
                    <div class="about-image-upload" style="margin-top: 1rem; background: #ffc107; padding: 1rem; border-radius: 8px; text-align: center;">
                        <input type="file" id="about-image-upload" accept="image/*" style="margin-bottom: 0.5rem;">
                        <p style="font-size: 0.9rem; margin: 0; color: #333;">Klick um About-Bild hochzuladen</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="about-text-side">
                <h2><?php echo safe_output($about_title); ?></h2>
                
                <div class="about-showcase-text">
                    <?php editable_textarea('about_section_text', $about_text, 'p', ['about-main-text']); ?>
                </div>
                
                <?php if ($EDIT_MODE): ?>
                    <div style="margin-top: 1.5rem; background: #f0f0f0; padding: 1rem; border-radius: 8px;">
                        <label for="about-shop-link" style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Shop-Link:</label>
                        <input type="text" id="about-shop-link" value="<?php echo safe_output($about_shop_link); ?>" 
                               style="width: 100%; padding: 0.8rem; border-radius: 5px; border: 1px solid #ccc;">
                        <button onclick="saveAboutLink()" class="btn btn-success" style="margin-top: 0.5rem;">Link speichern</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- STATISTIKEN -->
<section class="stats-section">
    <div class="container">
        <h2>Unsere Leidenschaft für Wein</h2>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo count_total_wines(); ?></div>
                <div class="stat-label">Qualitätsweine im Sortiment</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">8</div>
                <div class="stat-label">Verschiedene Kategorien</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">CH</div>
                <div class="stat-label">100% Schweizer Weine</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-number">20+</div>
                <div class="stat-label">Jahre Erfahrung</div>
            </div>
        </div>
    </div>
</section>

<!-- KATEGORIEN PREVIEW -->
<section class="categories-preview">
    <div class="container">
        <h2>Entdecken Sie unsere Kategorien</h2>

        <div class="categories-scroll-container">
            <div class="categories-grid-horizontal">
                <?php
                // Klara-Kategorien laden
                $klara_categories = klara_get_categories();
                $all_articles = klara_get_articles();

                // Produkte pro Kategorie zählen (nur verfügbare) - nach ID!
                $category_counts = [];
                foreach ($all_articles as $article) {
                    if (!empty($article['categories']) && $article['stock'] > 0) {
                        foreach ($article['categories'] as $cat_id) {
                            if (!isset($category_counts[$cat_id])) {
                                $category_counts[$cat_id] = 0;
                            }
                            $category_counts[$cat_id]++;
                        }
                    }
                }

                // Icons Definition - Flexible Zuordnung
                $icon_mapping = [
                    'champagner' => get_icon('champagne', 48, 'icon-primary'),
                    'prosecco' => get_icon('champagne', 48, 'icon-primary'),
                    'rosé' => get_icon('flower', 48, 'icon-primary'),
                    'rosewein' => get_icon('flower', 48, 'icon-primary'),
                    'weisswein' => get_icon('champagne', 48, 'icon-primary'),
                    'weißwein' => get_icon('champagne', 48, 'icon-primary'),
                    'rotwein' => get_icon('grapes', 48, 'icon-primary'),
                    'süsswein' => get_icon('droplet', 48, 'icon-primary'),
                    'süßwein' => get_icon('droplet', 48, 'icon-primary'),
                    'dessertwein' => get_icon('droplet', 48, 'icon-primary'),
                    'spirituosen' => get_icon('wine', 48, 'icon-primary'),
                    'gutschein' => get_icon('gift', 48, 'icon-primary'),
                    'geschenk' => get_icon('gift', 48, 'icon-primary'),
                    'zubehör' => get_icon('package', 48, 'icon-primary'),
                    'accessoire' => get_icon('package', 48, 'icon-primary')
                ];

                // Funktion um Icon zu finden
                function getIconForCategory($cat_name, $icon_mapping) {
                    $name_lower = strtolower($cat_name);
                    foreach ($icon_mapping as $key => $icon) {
                        if (stripos($name_lower, $key) !== false) {
                            return $icon;
                        }
                    }
                    return get_icon('wine', 48, 'icon-primary');
                }

                // Wein-Keywords für Kategorisierung (inkl. Alkoholfrei)
                $wine_keywords = ['wein', 'rosé', 'champagner', 'prosecco', 'alkoholfrei'];

                // Kategorien gruppieren (NUR mit Produkten!)
                $wine_cats = [];
                $other_cats = [];

                foreach ($klara_categories as $cat) {
                    if (!$cat['active']) continue;

                    $cat_id = $cat['id'];
                    $cat_name = $cat['name'];
                    $product_count = $category_counts[$cat_id] ?? 0;

                    // NUR Kategorien mit mindestens 1 Produkt anzeigen
                    if ($product_count <= 0) {
                        continue;
                    }

                    $is_wine = false;

                    foreach ($wine_keywords as $keyword) {
                        if (stripos(strtolower($cat_name), $keyword) !== false) {
                            $is_wine = true;
                            break;
                        }
                    }

                    if ($is_wine) {
                        $wine_cats[] = $cat;
                    } else {
                        $other_cats[] = $cat;
                    }
                }

                // WEINE SEKTION
                if (count($wine_cats) > 0):
                    echo '<div class="category-header-card"><h3>Weine</h3></div>';

                    foreach ($wine_cats as $cat):
                        $cat_id = $cat['id'];
                        $cat_name = $cat['name'];
                        $product_count = $category_counts[$cat_id] ?? 0;
                ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <?php echo getIconForCategory($cat_name, $icon_mapping); ?>
                        </div>
                        <h3><?php echo safe_output($cat_name); ?></h3>
                        <p class="category-count"><?php echo $product_count; ?> Wein<?php echo $product_count !== 1 ? 'e' : ''; ?></p>
                        <a href="?page=shop&category=<?php echo urlencode($cat_id); ?>" class="btn btn-secondary">Anschauen</a>
                    </div>
                <?php
                    endforeach;
                endif;

                // DIVERSES SEKTION
                if (count($other_cats) > 0):
                    echo '<div class="category-header-card"><h3>Diverses</h3></div>';

                    foreach ($other_cats as $cat):
                        $cat_id = $cat['id'];
                        $cat_name = $cat['name'];
                        $product_count = $category_counts[$cat_id] ?? 0;
                ?>
                    <div class="category-card">
                        <div class="category-icon">
                            <?php echo getIconForCategory($cat_name, $icon_mapping); ?>
                        </div>
                        <h3><?php echo safe_output($cat_name); ?></h3>
                        <p class="category-count"><?php echo $product_count; ?> Produkt<?php echo $product_count !== 1 ? 'e' : ''; ?></p>
                        <a href="?page=shop&category=<?php echo urlencode($cat_id); ?>" class="btn btn-secondary">Anschauen</a>
                    </div>
                <?php
                    endforeach;
                endif;

                // Events & Erlebnisse Sektion
                $upcoming_events = get_all_events(true, true);
                if (count($upcoming_events) > 0):
                    echo '<div class="category-header-card"><h3>Events & Erlebnisse</h3></div>';
                ?>
                    <div class="category-card category-card-events">
                        <div class="category-icon">
                            <?php echo get_icon('calendar', 48, 'icon-primary'); ?>
                        </div>
                        <h3>Events & Verkostungen</h3>
                        <p class="category-count"><?php echo count($upcoming_events); ?> bevorstehende Events</p>
                        <a href="?page=events" class="btn btn-secondary">Entdecken</a>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </div>
</section>

<!-- ABOUT CARDS SECTION -->
<section class="about-section">
    <div class="container">
        <h3 style="text-align: center; margin-bottom: 2rem;">Warum uns vertrauen?</h3>
        
        <div class="about-grid">
            <div class="about-card">
                <div class="about-icon"><?php echo get_icon('check-circle', 32); ?></div>
                <h4><?php editable('about_1_title', 'Sorgfältig ausgewählt', 'span'); ?></h4>
                <p><?php editable_textarea('about_1_desc', 'Jeder Wein in unserem Sortiment wird mit großer Sorgfalt von unseren Experten ausgewählt.', 'span'); ?></p>
            </div>

            <div class="about-card">
                <div class="about-icon"><?php echo get_icon('award', 32); ?></div>
                <h4><?php editable('about_2_title', '100% Schweizer', 'span'); ?></h4>
                <p><?php editable_textarea('about_2_desc', 'Wir legen Wert auf hochwertige Schweizer Weine von erstklassigen Produzenten.', 'span'); ?></p>
            </div>

            <div class="about-card">
                <div class="about-icon"><?php echo get_icon('truck', 32); ?></div>
                <h4><?php editable('about_3_title', 'Schnelle Lieferung', 'span'); ?></h4>
                <p><?php editable_textarea('about_3_desc', 'Ihre Bestellung wird schnell und zuverlässig zu Ihnen nach Hause gebracht.', 'span'); ?></p>
            </div>

            <div class="about-card">
                <div class="about-icon"><?php echo get_icon('message-circle', 32); ?></div>
                <h4><?php editable('about_4_title', 'Persönliche Beratung', 'span'); ?></h4>
                <p><?php editable_textarea('about_4_desc', 'Unser Team steht Ihnen bei Fragen gerne zur Seite und berät Sie fachkundig.', 'span'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- NEWSLETTER SECTION -->
<section class="newsletter-section">
    <div class="container">
        <div class="newsletter-box">
            <h2>Bleiben Sie informiert</h2>
            <p>Erhalten Sie exklusive Angebote und Neuheiten direkt in Ihr Postfach</p>
            
            <form id="newsletter-form" class="newsletter-form">
                <input type="email" placeholder="Ihre E-Mail-Adresse" required>
                <button type="submit" class="btn btn-primary">Abonnieren</button>
            </form>
            
            <p class="newsletter-note">Wir respektieren Ihre Privatsphäre. Kein Spam, versprochen!</p>
        </div>
    </div>
</section>

<style>
/* Header Banner */
.header-banner-section {
    margin-bottom: 2rem;
    margin-left: -20px;
    margin-right: -20px;
}

.header-banner {
    height: 400px;
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    position: relative;
    border-bottom: 4px solid var(--accent-gold);
}

.header-banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.2);
}

/* Hero Section */
.home-hero {
    position: relative;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    text-align: center;
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    margin-bottom: 3rem;
    overflow: hidden;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 1;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 800px;
    padding: 2rem;
}

.home-hero h1 {
    font-size: 3.5rem;
    color: white;
    border: none;
    padding-bottom: 0;
    margin-bottom: 1rem;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.95);
    margin-bottom: 2rem;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
}

/* News Section */
.news-section {
    background: var(--bg-light);
    padding: 3rem 0;
    margin-bottom: 3rem;
    border-top: 3px solid var(--accent-gold);
    border-bottom: 3px solid var(--accent-gold);
}

/* News Horizontal Scroll Container */
.news-scroll-container {
    margin-top: 2rem;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) #f0f0f0;
}

.news-scroll-container::-webkit-scrollbar {
    height: 8px;
}

.news-scroll-container::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.news-scroll-container::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 10px;
}

.news-scroll-container::-webkit-scrollbar-thumb:hover {
    background: var(--accent-gold);
}

.news-grid-horizontal {
    display: flex;
    gap: 1.5rem;
    padding-bottom: 1rem;
}

.news-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.news-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    text-decoration: none;
    color: inherit;
    min-width: 280px;
    max-width: 280px;
    flex-shrink: 0;
}

.news-card-link {
    cursor: pointer;
}

.news-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(114, 44, 44, 0.2);
}

.news-badge {
    background: var(--primary-color);
    color: white;
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.news-badge-event {
    background: #2196F3;
}

.news-badge-general {
    background: #4CAF50;
}

.event-date {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.event-location {
    color: var(--text-light);
    font-size: 0.85rem;
    margin-bottom: 0.5rem;
}

.event-tickets {
    color: var(--accent-gold);
    font-size: 0.9rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.news-content {
    color: var(--text-light);
    font-size: 0.9rem;
    line-height: 1.6;
}

.news-image {
    width: 100%;
    height: 280px;
    overflow: hidden;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    position: relative; /* Für Wappen-Badge */
}

.news-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.wine-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
}

.news-info {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.news-info h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.1rem;
    color: var(--primary-color);
}

.wine-producer {
    color: var(--text-light);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
    font-style: italic;
}

.wine-details {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.8rem;
}

.detail-item {
    font-size: 0.8rem;
    color: var(--text-light);
}

.wine-rating-news {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0.5rem 0;
    padding: 0.5rem;
    background: #f8f9fa;
    border-radius: 5px;
}

.rating-stars-news {
    color: #ffc107;
    font-size: 0.95rem;
    font-weight: 600;
}

.rating-text-news {
    font-size: 0.8rem;
    color: var(--text-light);
}

.wine-price {
    font-size: 1.3rem;
    color: var(--primary-color);
    font-weight: 700;
    margin-bottom: 1rem;
    flex: 1;
}

/* About Showcase Section */
.about-showcase-section {
    background: white;
    padding: 3rem 0;
    margin-bottom: 3rem;
}

.about-showcase-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

.about-image-side {
    position: relative;
}

.about-image-link {
    display: block;
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    z-index: 1;
    text-decoration: none;
}

.about-showcase-image {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.3s ease;
}

.about-image-link:hover .about-showcase-image {
    transform: scale(1.05);
}

.about-image-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    pointer-events: none;
}

.about-image-link:hover .about-image-overlay {
    background: rgba(0, 0, 0, 0.5);
}

.about-image-cta {
    color: white;
    font-size: 1.3rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0;
    transform: scale(0.8);
    transition: all 0.3s ease;
}

.about-image-link:hover .about-image-cta {
    opacity: 1;
    transform: scale(1);
}

.about-image-placeholder {
    width: 100%;
    aspect-ratio: 4/3;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: var(--text-light);
}

.about-image-upload {
    cursor: pointer;
}

.about-image-upload input[type="file"] {
    display: block;
}

.about-text-side h2 {
    font-size: 2.2rem;
    margin-top: 0;
    color: var(--primary-color);
    border: none;
}

.about-showcase-text {
    color: var(--text-light);
    font-size: 1.1rem;
    line-height: 1.8;
    margin-bottom: 1.5rem;
}

.about-main-text {
    text-align: justify;
}

/* Stats Section */
.stats-section {
    background: white;
    padding: 3rem 0;
    margin-bottom: 3rem;
    border-top: 3px solid var(--accent-gold);
    border-bottom: 3px solid var(--accent-gold);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.stat-card {
    text-align: center;
    padding: 2rem;
    border-radius: 10px;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 24px rgba(114, 44, 44, 0.1);
}

.stat-number {
    font-size: 3rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 1rem;
    color: var(--text-light);
    font-weight: 500;
}

/* Categories */
.categories-preview {
    background: var(--bg-light);
    padding: 3rem 0;
    margin-bottom: 3rem;
}

/* Categories Horizontal Scroll Container */
.categories-scroll-container {
    margin-top: 2rem;
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: var(--primary-color) #f0f0f0;
}

.categories-scroll-container::-webkit-scrollbar {
    height: 8px;
}

.categories-scroll-container::-webkit-scrollbar-track {
    background: #f0f0f0;
    border-radius: 10px;
}

.categories-scroll-container::-webkit-scrollbar-thumb {
    background: var(--primary-color);
    border-radius: 10px;
}

.categories-scroll-container::-webkit-scrollbar-thumb:hover {
    background: var(--accent-gold);
}

.categories-grid-horizontal {
    display: flex;
    gap: 1.5rem;
    padding-bottom: 1rem;
}

.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.category-header-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, #8b3a3a 100%);
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 200px;
    max-width: 200px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-header-card h3 {
    color: white;
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
}

.category-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    min-width: 220px;
    max-width: 220px;
    flex-shrink: 0;
}

.category-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(114, 44, 44, 0.15);
}

.category-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.category-card h3 {
    color: var(--primary-color);
    margin-top: 0;
    font-size: 1.3rem;
}

.category-count {
    color: var(--accent-gold);
    font-weight: 600;
    margin-bottom: 1.5rem;
}

/* About Cards */
.about-section {
    background: white;
    padding: 3rem 0;
    margin-bottom: 3rem;
}

.about-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.about-card {
    padding: 2rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    border-radius: 12px;
    text-align: center;
    transition: all 0.3s ease;
    border-left: 4px solid var(--accent-gold);
}

.about-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(114, 44, 44, 0.1);
}

.about-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.about-card h4 {
    color: var(--primary-color);
    margin-top: 0;
    font-size: 1.2rem;
}

.about-card p {
    color: var(--text-light);
    line-height: 1.6;
}

/* Newsletter Section */
.newsletter-section {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
    padding: 4rem 0;
    margin-bottom: 3rem;
    color: white;
}

.newsletter-box {
    text-align: center;
    max-width: 600px;
    margin: 0 auto;
}

.newsletter-box h2 {
    color: white;
    border: none;
    font-size: 2rem;
    margin-top: 0;
}

.newsletter-box p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.1rem;
}

.newsletter-form {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.newsletter-form input {
    flex: 1;
    min-width: 200px;
    padding: 1rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
}

.newsletter-form button {
    padding: 1rem 2rem;
    white-space: nowrap;
}

.newsletter-note {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.7);
}

/* Edit Badge */
.edit-badge {
    display: inline-block;
    background: #4CAF50;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 3px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 1024px) {
    .news-grid-horizontal {
        grid-auto-columns: 280px;
    }

    .news-card h3 {
        font-size: 1rem;
    }

    .event-date,
    .event-location,
    .event-tickets {
        font-size: 0.85rem;
    }

    .category-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .header-banner {
        height: 250px;
    }

    .home-hero {
        min-height: 400px;
    }

    .home-hero h1 {
        font-size: 2rem;
    }

    .hero-subtitle {
        font-size: 1.1rem;
    }

    .about-showcase-grid {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .about-text-side h2 {
        font-size: 1.8rem;
    }

    .stat-number {
        font-size: 2rem;
    }

    .newsletter-form {
        flex-direction: column;
    }

    .newsletter-form input,
    .newsletter-form button {
        width: 100%;
    }

    .news-grid-horizontal {
        grid-auto-columns: 260px;
    }

    .news-card {
        min-height: auto;
    }

    .news-image {
        height: 200px;
    }

    .news-info h3 {
        font-size: 0.95rem;
    }

    .wine-producer,
    .news-content {
        font-size: 0.85rem;
    }

    .category-grid {
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 1rem;
    }

    .category-card {
        padding: 1.2rem;
    }

    .category-icon {
        margin-bottom: 0.8rem;
    }

    .category-card h3 {
        font-size: 1rem;
    }

    .category-count {
        font-size: 0.85rem;
    }
}

@media (max-width: 480px) {
    .home-hero h1 {
        font-size: 1.5rem;
    }

    .hero-subtitle {
        font-size: 1rem;
    }

    .news-scroll-container {
        margin: 0 -1rem;
        padding: 0 1rem;
    }

    .news-grid-horizontal {
        grid-auto-columns: 240px;
        gap: 0.8rem;
    }

    .news-card {
        border-radius: 10px;
    }

    .news-image {
        height: 180px;
    }

    .news-info {
        padding: 1rem;
    }

    .wine-price {
        font-size: 1rem;
    }

    .event-date,
    .event-location,
    .event-tickets {
        font-size: 0.8rem;
    }

    .category-grid {
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }

    .category-card {
        padding: 1rem;
    }

    .category-card h3 {
        font-size: 0.95rem;
    }

    .category-count {
        font-size: 0.8rem;
        margin: 0.3rem 0 0.8rem;
    }
}
</style>

<script>
// 1. Newsletter Handler
document.getElementById('newsletter-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const email = this.querySelector('input[type="email"]').value;
    
    fetch('?page=newsletter', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=subscribe&email=' + encodeURIComponent(email)
    })
    .then(() => {
        alert('Danke für die Anmeldung!');
        document.getElementById('newsletter-form').reset();
    })
    .catch(e => console.error('Fehler:', e));
});

// 2. About Image Upload
document.getElementById('about-image-upload')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    formData.append('type', 'about_section');
    
    fetch('api/upload-banner.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Bild hochgeladen! Seite wird neu geladen...');
            location.reload();
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Upload');
    });
});

// 3. Featured Wine Management
function addFeaturedWine() {
    const select = document.getElementById('featured-wine-select');
    const wineId = select.value;
    
    if (!wineId) {
        alert('Bitte einen Wein auswählen');
        return;
    }
    
    fetch('api/toggle-featured-wine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'wine_id=' + wineId + '&action=add'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Wein hinzugefügt!');
            location.reload();
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Hinzufügen');
    });
}

function createNewFeaturedWine() {
    const name = document.getElementById('new-wine-name').value;
    
    if (!name.trim()) {
        alert('Bitte einen Weinnamen eingeben');
        return;
    }
    
    fetch('api/create-wine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'name=' + encodeURIComponent(name)
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Wein erstellt und hinzugefügt!');
            location.reload();
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Erstellen');
    });
}

function removeFeaturedWine(wineId) {
    if (!confirm('Wirklich entfernen?')) return;
    
    fetch('api/toggle-featured-wine.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'wine_id=' + wineId + '&action=remove'
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Entfernen');
    });
}

function saveAboutLink() {
    const link = document.getElementById('about-shop-link').value;
    
    fetch('api/edit-content.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({key: 'about_shop_link', value: link, type: 'text'})
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Link gespeichert!');
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Speichern');
    });
}

// 4. Load Featured Wines List on Page Load
document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('featured-wines-list');
    if (listContainer) {
        fetch('api/get-featured-wines.php')
            .then(r => r.json())
            .then(wines => {
                if (wines.length > 0) {
                    listContainer.innerHTML = wines.map(w => `
                        <div class="featured-wine-item" style="display: flex; justify-content: space-between; align-items: center; padding: 0.8rem; background: white; border-radius: 5px; margin-bottom: 0.5rem;">
                            <div>
                                <strong>${w.name}</strong><br>
                                <small>${w.producer} - CHF ${parseFloat(w.price).toFixed(2)}</small>
                            </div>
                            <button onclick="removeFeaturedWine(${w.id})" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">Entfernen</button>
                        </div>
                    `).join('');
                } else {
                    listContainer.innerHTML = '<p style="color: var(--text-light);">Keine Neuheiten hinzugefügt.</p>';
                }
            })
            .catch(e => console.error('Fehler beim Laden:', e));
    }
});
</script>