<?php
// pages/admin-dashboard.php - KOMPLETTES Admin Dashboard

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header('Location: ?page=admin-login');
    exit;
}

$tab = $_GET['tab'] ?? 'overview';
$wine_id_edit = isset($_GET['wine_id']) ? (int)$_GET['wine_id'] : 0;
$wine_edit = null;

if ($wine_id_edit > 0) {
    $wine_edit = get_wine_by_id($wine_id_edit);
    if ($wine_edit) {
        $tab = 'edit-wine';
    }
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_website') {
        update_setting('hero_title', $_POST['hero_title']);
        update_setting('hero_subtitle', $_POST['hero_subtitle']);
        update_setting('hero_button_text', $_POST['hero_button_text']);
        update_setting('about_title', $_POST['about_title']);
        update_setting('about_1_title', $_POST['about_1_title']);
        update_setting('about_1_desc', $_POST['about_1_desc']);
        update_setting('about_2_title', $_POST['about_2_title']);
        update_setting('about_2_desc', $_POST['about_2_desc']);
        update_setting('about_3_title', $_POST['about_3_title']);
        update_setting('about_3_desc', $_POST['about_3_desc']);
        update_setting('about_4_title', $_POST['about_4_title']);
        update_setting('about_4_desc', $_POST['about_4_desc']);
        update_setting('about_section_text', $_POST['about_section_text']);
        update_setting('about_shop_link', $_POST['about_shop_link']);
        $message = 'Website-Inhalte gespeichert!';
        $message_type = 'success';
    }
    elseif ($action === 'update_colors') {
        // Theme-Farben in theme_settings Tabelle speichern
        update_theme_color('primary_color', $_POST['primary_color'] ?? '#722c2c');
        update_theme_color('primary_dark', $_POST['primary_dark'] ?? '#561111');
        update_theme_color('accent_gold', $_POST['accent_gold'] ?? '#d4a574');
        update_theme_color('bg_light', $_POST['bg_light'] ?? '#f8f4f0');
        update_theme_color('text_dark', $_POST['text_dark'] ?? '#333333');
        update_theme_color('text_light', $_POST['text_light'] ?? '#666666');
        $message = 'Farben gespeichert! Seite neu laden um Änderungen zu sehen.';
        $message_type = 'success';
    }
    elseif ($action === 'update_footer') {
        update_setting('footer_address', $_POST['footer_address']);
        update_setting('footer_phone', $_POST['footer_phone']);
        update_setting('footer_email', $_POST['footer_email']);
        update_setting('footer_open_mo_fr', $_POST['footer_open_mo_fr']);
        update_setting('footer_open_sa', $_POST['footer_open_sa']);
        update_setting('footer_open_su', $_POST['footer_open_su']);
        update_setting('social_instagram', $_POST['social_instagram']);
        update_setting('social_facebook', $_POST['social_facebook']);
        $message = 'Footer & Kontakt gespeichert!';
        $message_type = 'success';
    }
    elseif ($action === 'update_images') {
        update_setting('header_banner_image', $_POST['header_banner_image']);
        update_setting('hero_background_image', $_POST['hero_background_image']);
        update_setting('about_section_image', $_POST['about_section_image']);
        update_setting('wine_month_image', $_POST['wine_month_image']);
        update_setting('wine_month_link', $_POST['wine_month_link']);
        update_setting('wine_month_title', $_POST['wine_month_title']);
        $message = 'Bilder & Banner gespeichert!';
        $message_type = 'success';
    }
    elseif ($action === 'toggle_featured_wine') {
        $wine_id = (int)$_POST['wine_id'];
        $featured = isset($_POST['featured']) ? 1 : 0;
        if ($db->query("UPDATE wines SET is_featured = $featured WHERE id = $wine_id")) {
            $message = $featured ? 'Wein als Neuheit markiert!' : 'Aus Neuheiten entfernt!';
            $message_type = 'success';
        } else {
            $message = 'Fehler: ' . $db->error;
            $message_type = 'error';
        }
    }
    elseif ($action === 'create_featured_wine') {
        $name = $db->real_escape_string(trim($_POST['wine_name']));
        $producer = $db->real_escape_string(trim($_POST['wine_producer']));
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['wine_price'];
        
        if (empty($name)) {
            $message = 'Weinname erforderlich!';
            $message_type = 'error';
        } else {
            $sql = "INSERT INTO wines (name, category_id, producer, price, stock, is_featured, description) 
                    VALUES ('$name', $category_id, '$producer', $price, 0, 1, 'Neuer Wein - Bitte ergänzen')";
            if ($db->query($sql)) {
                $message = 'Neuer Wein erstellt und als Neuheit markiert!';
                $message_type = 'success';
            } else {
                $message = 'Fehler: ' . $db->error;
                $message_type = 'error';
            }
        }
    }
    elseif ($action === 'update_pages') {
        update_setting('newsletter_title', $_POST['newsletter_title']);
        update_setting('newsletter_content', $_POST['newsletter_content']);
        update_setting('impressum_title', $_POST['impressum_title']);
        update_setting('impressum_content', $_POST['impressum_content']);
        update_setting('impressum_company', $_POST['impressum_company']);
        update_setting('impressum_address', $_POST['impressum_address']);
        update_setting('impressum_phone', $_POST['impressum_phone']);
        update_setting('impressum_email', $_POST['impressum_email']);
        update_setting('agb_title', $_POST['agb_title']);
        update_setting('agb_content', $_POST['agb_content']);
        update_setting('datenschutz_title', $_POST['datenschutz_title']);
        update_setting('datenschutz_content', $_POST['datenschutz_content']);
        $message = 'Alle Seiten gespeichert!';
        $message_type = 'success';
    }
    elseif ($action === 'delete_wine') {
        $wine_id = (int)$_POST['wine_id'];
        if ($db->query("DELETE FROM wines WHERE id = $wine_id")) {
            $message = 'Wein gelöscht!';
            $message_type = 'success';
        } else {
            $message = 'Fehler beim Löschen!';
            $message_type = 'error';
        }
    }
    elseif ($action === 'add_wine') {
        $name = $db->real_escape_string(trim($_POST['name']));
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $description = $db->real_escape_string(trim($_POST['description']));
        $producer = $db->real_escape_string(trim($_POST['producer']));
        $vintage = !empty($_POST['vintage']) ? (int)$_POST['vintage'] : 'NULL';
        $region = $db->real_escape_string(trim($_POST['region']));
        $alcohol = !empty($_POST['alcohol_content']) ? (float)$_POST['alcohol_content'] : 'NULL';
        $image_url = $db->real_escape_string(trim($_POST['image_url']));
        $sku = generate_slug($name) . '-' . time();
        
        $sql = "INSERT INTO wines (name, category_id, price, stock, description, producer, vintage, region, alcohol_content, image_url, sku) 
                VALUES ('$name', $category_id, $price, $stock, '$description', '$producer', $vintage, '$region', $alcohol, '$image_url', '$sku')";
        
        if ($db->query($sql)) {
            $message = 'Wein hinzugefügt!';
            $message_type = 'success';
            $tab = 'wines';
        } else {
            $message = 'Fehler: ' . $db->error;
            $message_type = 'error';
        }
    }
    elseif ($action === 'edit_wine') {
        $wine_id = (int)$_POST['wine_id'];
        $name = $db->real_escape_string(trim($_POST['name']));
        $category_id = (int)$_POST['category_id'];
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $description = $db->real_escape_string(trim($_POST['description']));
        $producer = $db->real_escape_string(trim($_POST['producer']));
        $vintage = !empty($_POST['vintage']) ? (int)$_POST['vintage'] : 'NULL';
        $region = $db->real_escape_string(trim($_POST['region']));
        $alcohol = !empty($_POST['alcohol_content']) ? (float)$_POST['alcohol_content'] : 'NULL';
        $image_url = $db->real_escape_string(trim($_POST['image_url']));

        $sql = "UPDATE wines SET
                name='$name', category_id=$category_id, price=$price, stock=$stock,
                description='$description', producer='$producer', vintage=$vintage,
                region='$region', alcohol_content=$alcohol, image_url='$image_url'
                WHERE id=$wine_id";

        if ($db->query($sql)) {
            $message = 'Wein aktualisiert!';
            $message_type = 'success';
            $tab = 'wines';
        } else {
            $message = 'Fehler: ' . $db->error;
            $message_type = 'error';
        }
    }
    elseif ($action === 'add_event') {
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'event_date' => $_POST['event_date'],
            'location' => $_POST['location'] ?? '',
            'price' => $_POST['price'],
            'image_url' => $_POST['image_url'] ?? '',
            'max_participants' => $_POST['max_participants'] ?? 0,
            'available_tickets' => $_POST['available_tickets'] ?? 0,
            'featured_bg_color' => $_POST['featured_bg_color'] ?? '#2c5282',
            'featured_text_color' => $_POST['featured_text_color'] ?? '#ffffff'
        ];

        if (create_event($data)) {
            $message = 'Event erstellt!';
            $message_type = 'success';
            $tab = 'events';
        } else {
            $message = 'Fehler beim Erstellen: ' . $db->error;
            $message_type = 'error';
        }
    }
    elseif ($action === 'edit_event') {
        $event_id = (int)$_POST['event_id'];
        $data = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'event_date' => $_POST['event_date'],
            'location' => $_POST['location'] ?? '',
            'price' => $_POST['price'],
            'image_url' => $_POST['image_url'] ?? '',
            'max_participants' => $_POST['max_participants'] ?? 0,
            'available_tickets' => $_POST['available_tickets'] ?? 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'featured_bg_color' => $_POST['featured_bg_color'] ?? '#2c5282',
            'featured_text_color' => $_POST['featured_text_color'] ?? '#ffffff'
        ];

        if (update_event($event_id, $data)) {
            $message = 'Event aktualisiert!';
            $message_type = 'success';
            $tab = 'events';
        } else {
            $message = 'Fehler beim Aktualisieren: ' . $db->error;
            $message_type = 'error';
        }
    }
}

$settings = get_all_settings();
?>

<div class="admin-dashboard-mega">
    <!-- Mobile Toggle Button -->
    <button class="admin-mobile-toggle" id="admin-menu-toggle" aria-label="Menü">
        <?php echo get_icon('menu', 24, 'icon-white'); ?>
    </button>
    <div class="admin-overlay" id="admin-overlay"></div>

    <aside class="admin-sidebar-mega" id="admin-sidebar">
        <div class="sidebar-header">
            <h2>Admin Panel</h2>
            <p><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></p>
        </div>

        <nav class="sidebar-nav-mega">
            <a href="?page=admin-dashboard&tab=overview" class="<?php echo $tab === 'overview' ? 'active' : ''; ?>">Übersicht</a>
            <a href="?page=admin-dashboard&tab=pages" class="<?php echo $tab === 'pages' ? 'active' : ''; ?>">Seiten</a>
            
            <div class="nav-divider">WEBSEITE</div>
            <a href="?page=admin-dashboard&tab=website" class="<?php echo $tab === 'website' ? 'active' : ''; ?>">Startseite Inhalte</a>
            <a href="?page=admin-dashboard&tab=colors" class="<?php echo $tab === 'colors' ? 'active' : ''; ?>">Farben & Design</a>
            <a href="?page=admin-dashboard&tab=footer" class="<?php echo $tab === 'footer' ? 'active' : ''; ?>">Footer & Kontakt</a>
            
            <div class="nav-divider">STARTSEITE</div>
            <a href="?page=admin-dashboard&tab=featured-wines" class="<?php echo $tab === 'featured-wines' ? 'active' : ''; ?>">Neuheiten verwalten</a>
            <a href="?page=admin-dashboard&tab=images" class="<?php echo $tab === 'images' ? 'active' : ''; ?>">Bilder & Banner</a>
            
            <div class="nav-divider">SHOP</div>
            <a href="?page=admin-dashboard&tab=klara-products" class="<?php echo $tab === 'klara-products' ? 'active' : ''; ?>">Klara-Produkte</a>
            <a href="?page=admin-dashboard&tab=wines" class="<?php echo $tab === 'wines' ? 'active' : ''; ?>">Weine verwalten (alt)</a>
            <a href="?page=admin-dashboard&tab=add-wine" class="<?php echo $tab === 'add-wine' ? 'active' : ''; ?>">Wein hinzufügen (alt)</a>

            <div class="nav-divider">NEWS & EVENTS</div>
            <a href="?page=admin-dashboard&tab=news-items" class="<?php echo $tab === 'news-items' ? 'active' : ''; ?>">News verwalten</a>
            <a href="?page=admin-dashboard&tab=events" class="<?php echo $tab === 'events' ? 'active' : ''; ?>">Events verwalten</a>
            <a href="?page=admin-dashboard&tab=add-event" class="<?php echo $tab === 'add-event' ? 'active' : ''; ?>">Event hinzufügen</a>

            <div class="nav-divider">BESTELLUNGEN</div>
            <a href="?page=admin-dashboard&tab=orders" class="<?php echo $tab === 'orders' ? 'active' : ''; ?>">Bestellungen</a>
            <a href="?page=admin-dashboard&tab=coupons" class="<?php echo $tab === 'coupons' ? 'active' : ''; ?>">Gutscheine</a>

            <div class="nav-divider"></div>
            <a href="?page=home" class="nav-home">
                <?php echo get_icon('home', 18); ?> Zur Webseite
            </a>
            <a href="?logout=1" class="nav-logout" onclick="return confirm('Wirklich abmelden?')">
                <?php echo get_icon('log-out', 18); ?> Abmelden
            </a>
        </nav>
    </aside>

    <main class="admin-content-mega">
        <div class="admin-top-bar">
            <h1><?php echo ucfirst(str_replace('-', ' ', $tab)); ?></h1>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($tab === 'overview'): ?>
            <div class="stats-grid-admin">
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo count(klara_get_articles()); ?></div>
                    <div class="stat-label">Klara-Produkte</div>
                </div>
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c']; ?></div>
                    <div class="stat-label">Bestellungen</div>
                </div>
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c']; ?></div>
                    <div class="stat-label">Benutzer</div>
                </div>
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo count(get_all_events(false, false)); ?></div>
                    <div class="stat-label">Events</div>
                </div>
            </div>

            <div class="welcome-box">
                <h2>Willkommen im Admin Panel!</h2>
                <p>Von hier aus verwaltest du deine komplette Website.</p>
            </div>

        <?php elseif ($tab === 'pages'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_pages">
                
                <div class="form-section-mega">
                    <h3>Newsletter</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="newsletter_title" value="<?php echo safe_output($settings['newsletter_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Beschreibung</label>
                        <textarea name="newsletter_content" rows="5"><?php echo safe_output($settings['newsletter_content'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Impressum</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="impressum_title" value="<?php echo safe_output($settings['impressum_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Haupttext</label>
                        <textarea name="impressum_content" rows="8"><?php echo safe_output($settings['impressum_content'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Firmenname</label>
                            <input type="text" name="impressum_company" value="<?php echo safe_output($settings['impressum_company'] ?? ''); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Adresse</label>
                            <input type="text" name="impressum_address" value="<?php echo safe_output($settings['impressum_address'] ?? ''); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Telefon</label>
                            <input type="text" name="impressum_phone" value="<?php echo safe_output($settings['impressum_phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>E-Mail</label>
                            <input type="email" name="impressum_email" value="<?php echo safe_output($settings['impressum_email'] ?? ''); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>AGB</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="agb_title" value="<?php echo safe_output($settings['agb_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Inhalt (HTML erlaubt)</label>
                        <textarea name="agb_content" rows="10"><?php echo safe_output($settings['agb_content'] ?? ''); ?></textarea>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Datenschutz</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="datenschutz_title" value="<?php echo safe_output($settings['datenschutz_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Inhalt (HTML erlaubt)</label>
                        <textarea name="datenschutz_content" rows="10"><?php echo safe_output($settings['datenschutz_content'] ?? ''); ?></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Alle Seiten speichern</button>
            </form>

        <?php elseif ($tab === 'website'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_website">

                <div class="form-section-mega">
                    <h3>Hero Section</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="hero_title" value="<?php echo safe_output($settings['hero_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Untertitel</label>
                        <textarea name="hero_subtitle" rows="3"><?php echo safe_output($settings['hero_subtitle'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group-mega">
                        <label>Button Text</label>
                        <input type="text" name="hero_button_text" value="<?php echo safe_output($settings['hero_button_text'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>About Section</h3>
                    <div class="form-group-mega">
                        <label>Section Titel</label>
                        <input type="text" name="about_title" value="<?php echo safe_output($settings['about_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>About Text</label>
                        <textarea name="about_section_text" rows="5"><?php echo safe_output($settings['about_section_text'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group-mega">
                        <label>Shop-Link</label>
                        <input type="text" name="about_shop_link" value="<?php echo safe_output($settings['about_shop_link'] ?? ''); ?>" placeholder="?page=shop">
                    </div>
                    
                    <div class="about-cards-edit">
                        <div class="card-edit">
                            <h4>Karte 1</h4>
                            <input type="text" name="about_1_title" placeholder="Titel" value="<?php echo safe_output($settings['about_1_title'] ?? ''); ?>">
                            <textarea name="about_1_desc" rows="3"><?php echo safe_output($settings['about_1_desc'] ?? ''); ?></textarea>
                        </div>
                        <div class="card-edit">
                            <h4>Karte 2</h4>
                            <input type="text" name="about_2_title" placeholder="Titel" value="<?php echo safe_output($settings['about_2_title'] ?? ''); ?>">
                            <textarea name="about_2_desc" rows="3"><?php echo safe_output($settings['about_2_desc'] ?? ''); ?></textarea>
                        </div>
                        <div class="card-edit">
                            <h4>Karte 3</h4>
                            <input type="text" name="about_3_title" placeholder="Titel" value="<?php echo safe_output($settings['about_3_title'] ?? ''); ?>">
                            <textarea name="about_3_desc" rows="3"><?php echo safe_output($settings['about_3_desc'] ?? ''); ?></textarea>
                        </div>
                        <div class="card-edit">
                            <h4>Karte 4</h4>
                            <input type="text" name="about_4_title" placeholder="Titel" value="<?php echo safe_output($settings['about_4_title'] ?? ''); ?>">
                            <textarea name="about_4_desc" rows="3"><?php echo safe_output($settings['about_4_desc'] ?? ''); ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
            </form>

        <?php elseif ($tab === 'colors'): ?>
            <?php $theme_colors = get_all_theme_settings(); ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_colors">
                <div class="form-section-mega">
                    <h3>Website Farben</h3>
                    <p>Nach dem Speichern: Seite neu laden (F5) um Änderungen zu sehen.</p>

                    <div class="colors-grid">
                        <div class="color-picker">
                            <label>Hauptfarbe (Primary)</label>
                            <input type="color" name="primary_color" value="<?php echo $theme_colors['primary_color'] ?? '#722c2c'; ?>">
                            <input type="text" value="<?php echo $theme_colors['primary_color'] ?? '#722c2c'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Dunkle Hauptfarbe</label>
                            <input type="color" name="primary_dark" value="<?php echo $theme_colors['primary_dark'] ?? '#561111'; ?>">
                            <input type="text" value="<?php echo $theme_colors['primary_dark'] ?? '#561111'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Akzentfarbe (Gold)</label>
                            <input type="color" name="accent_gold" value="<?php echo $theme_colors['accent_gold'] ?? '#d4a574'; ?>">
                            <input type="text" value="<?php echo $theme_colors['accent_gold'] ?? '#d4a574'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Hintergrund Hell</label>
                            <input type="color" name="bg_light" value="<?php echo $theme_colors['bg_light'] ?? '#f8f4f0'; ?>">
                            <input type="text" value="<?php echo $theme_colors['bg_light'] ?? '#f8f4f0'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Text Dunkel</label>
                            <input type="color" name="text_dark" value="<?php echo $theme_colors['text_dark'] ?? '#333333'; ?>">
                            <input type="text" value="<?php echo $theme_colors['text_dark'] ?? '#333333'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Text Hell</label>
                            <input type="color" name="text_light" value="<?php echo $theme_colors['text_light'] ?? '#666666'; ?>">
                            <input type="text" value="<?php echo $theme_colors['text_light'] ?? '#666666'; ?>" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Farben speichern</button>
            </form>

        <?php elseif ($tab === 'footer'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_footer">

                <div class="form-section-mega">
                    <h3>Kontakt & Adresse</h3>
                    <div class="form-group-mega">
                        <label>Adresse</label>
                        <input type="text" name="footer_address" value="<?php echo safe_output($settings['footer_address'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Telefon</label>
                        <input type="text" name="footer_phone" value="<?php echo safe_output($settings['footer_phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>E-Mail</label>
                        <input type="email" name="footer_email" value="<?php echo safe_output($settings['footer_email'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Öffnungszeiten</h3>
                    <div class="form-group-mega">
                        <label>Montag - Freitag</label>
                        <input type="text" name="footer_open_mo_fr" value="<?php echo safe_output($settings['footer_open_mo_fr'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Samstag</label>
                        <input type="text" name="footer_open_sa" value="<?php echo safe_output($settings['footer_open_sa'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Sonntag</label>
                        <input type="text" name="footer_open_su" value="<?php echo safe_output($settings['footer_open_su'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Social Media</h3>
                    <div class="form-group-mega">
                        <label>Instagram URL</label>
                        <input type="url" name="social_instagram" value="<?php echo safe_output($settings['social_instagram'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Facebook URL</label>
                        <input type="url" name="social_facebook" value="<?php echo safe_output($settings['social_facebook'] ?? ''); ?>">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
            </form>

        <?php elseif ($tab === 'featured-wines'): ?>
            <div class="admin-info-box">
                <span class="icon-text"><?php echo get_icon('info', 20, 'icon-primary'); ?> Diese Inhalte werden auf der Startseite unter "News / Neuheiten" angezeigt.</span>
            </div>

            <!-- ALLE NEUHEITEN VERWALTEN -->
            <div class="admin-form-mega">
                <h3><?php echo get_icon('star', 24, 'icon-primary'); ?> Alle Neuheiten</h3>

                <?php
                // Lade alle Featured Items (Klara-Produkte, Events, Custom News)
                $featured_products = get_klara_featured_products(20);
                $featured_events = get_featured_events(20);
                $custom_news = get_custom_news(20);

                $all_featured = [];

                // Klara-Produkte
                foreach ($featured_products as $product) {
                    $all_featured[] = [
                        'id' => $product['klara_article_id'], // WICHTIG: Verwende klara_article_id für das Entfernen!
                        'type' => 'product',
                        'title' => $product['name'],
                        'subtitle' => $product['producer'] ?? '',
                        'price' => $product['price'],
                        'image' => $product['image_url'],
                        'edit_url' => '?page=admin-dashboard&tab=klara-products&search=' . urlencode($product['name'])
                    ];
                }

                // Events
                foreach ($featured_events as $event) {
                    $all_featured[] = [
                        'id' => $event['id'],
                        'type' => 'event',
                        'title' => $event['name'] ?? $event['title'] ?? 'Event',
                        'subtitle' => date('d.m.Y', strtotime($event['event_date'])),
                        'price' => $event['price'],
                        'image' => $event['image_url'],
                        'edit_url' => '?page=admin-dashboard&tab=events&event_id=' . $event['id']
                    ];
                }

                // Custom News (nur wenn Titel vorhanden)
                foreach ($custom_news as $news) {
                    if (!empty($news['title'])) {
                        $all_featured[] = [
                            'id' => $news['id'],
                            'type' => 'news',
                            'title' => $news['title'],
                            'subtitle' => substr($news['content'] ?? '', 0, 50) . '...',
                            'price' => null,
                            'image' => $news['image_url'],
                            'edit_url' => '?page=admin-dashboard&tab=news&news_id=' . $news['id']
                        ];
                    }
                }
                ?>

                <?php if (count($all_featured) > 0): ?>
                    <p>Anzahl: <?php echo count($all_featured); ?> Eintr<?php echo count($all_featured) !== 1 ? 'äge' : 'ag'; ?></p>

                    <table class="admin-table-mega">
                        <thead>
                            <tr>
                                <th>Typ</th>
                                <th>Titel</th>
                                <th>Details</th>
                                <th>Preis</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_featured as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['type'] === 'product'): ?>
                                            <?php echo get_icon('wine', 18, 'icon-primary'); ?> Produkt
                                        <?php elseif ($item['type'] === 'event'): ?>
                                            <?php echo get_icon('calendar', 18, 'icon-primary'); ?> Event
                                        <?php else: ?>
                                            <?php echo get_icon('newspaper', 18, 'icon-primary'); ?> News
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo safe_output($item['title']); ?></strong></td>
                                    <td><?php echo safe_output($item['subtitle']); ?></td>
                                    <td><?php echo $item['price'] ? 'CHF ' . number_format($item['price'], 2) : '-'; ?></td>
                                    <td>
                                        <a href="<?php echo $item['edit_url']; ?>" class="btn-small-admin">
                                            <?php echo get_icon('edit', 14); ?> Bearbeiten
                                        </a>
                                        <button onclick="removeFeatured('<?php echo $item['type']; ?>', '<?php echo $item['id']; ?>')"
                                                class="btn-small-admin btn-danger">
                                            <?php echo get_icon('trash', 14); ?> Entfernen
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <?php echo get_icon('alert-triangle', 20); ?> Noch keine Neuheiten hinzugefügt.
                    </div>
                <?php endif; ?>
            </div>

            <!-- SCHNELLZUGRIFF -->
            <div class="admin-grid-2">
                <div class="admin-form-mega">
                    <h3><?php echo get_icon('wine', 24, 'icon-primary'); ?> Klara-Produkt hinzufügen</h3>
                    <p>Markiere ein Klara-Produkt als Neuheit</p>
                    <a href="?page=admin-dashboard&tab=klara-products" class="btn btn-primary">
                        Zu Klara-Produkten
                    </a>
                </div>

                <div class="admin-form-mega">
                    <h3><?php echo get_icon('calendar', 24, 'icon-primary'); ?> Event hinzufügen</h3>
                    <p>Markiere ein Event als Neuheit</p>
                    <a href="?page=admin-dashboard&tab=events" class="btn btn-primary">
                        Zu Events
                    </a>
                </div>

                <div class="admin-form-mega">
                    <h3><?php echo get_icon('plus-circle', 24, 'icon-primary'); ?> Custom News erstellen</h3>
                    <p>Erstelle eine eigene News/Aktion (z.B. "30% Rabatt")</p>
                    <a href="?page=admin-dashboard&tab=news&action=create" class="btn btn-primary">
                        News erstellen
                    </a>
                </div>
            </div>

        <?php elseif ($tab === 'images'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_images">

                <div class="form-section-mega">
                    <h3>Header Banner (Oben)</h3>
                    <div class="form-group-mega">
                        <label>Banner Bild URL</label>
                        <input type="text" name="header_banner_image" value="<?php echo safe_output($settings['header_banner_image'] ?? ''); ?>" placeholder="z.B. assets/images/banner.jpg">
                        <small>Empfohlene Größe: 1920x600px - Dekorativer Banner ganz oben</small>
                    </div>
                    <?php if (!empty($settings['header_banner_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo safe_output($settings['header_banner_image']); ?>" alt="Banner" style="max-width: 100%; height: auto; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-section-mega">
                    <h3>Hero Hintergrundbild</h3>
                    <div class="form-group-mega">
                        <label>Hero Background Bild URL</label>
                        <input type="text" name="hero_background_image" value="<?php echo safe_output($settings['hero_background_image'] ?? ''); ?>" placeholder="z.B. assets/images/hero-bg.jpg">
                        <small>Empfohlene Größe: 1920x1080px - Hintergrund für Hero-Sektion mit Text</small>
                    </div>
                    <?php if (!empty($settings['hero_background_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo safe_output($settings['hero_background_image']); ?>" alt="Hero Background" style="max-width: 100%; height: auto; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-section-mega">
                    <h3>About Section Bild</h3>
                    <div class="form-group-mega">
                        <label>Bild URL</label>
                        <input type="text" name="about_section_image" value="<?php echo safe_output($settings['about_section_image'] ?? ''); ?>" placeholder="z.B. assets/images/about.jpg">
                        <small>Empfohlene Größe: 800x600px</small>
                    </div>
                    <?php if (!empty($settings['about_section_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo safe_output($settings['about_section_image']); ?>" alt="About" style="max-width: 100%; height: auto; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-section-mega">
                    <h3>Wein des Monats</h3>
                    <div class="form-group-mega">
                        <label>Titel</label>
                        <input type="text" name="wine_month_title" value="<?php echo safe_output($settings['wine_month_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Bild URL</label>
                        <input type="text" name="wine_month_image" value="<?php echo safe_output($settings['wine_month_image'] ?? ''); ?>" placeholder="z.B. assets/images/wein-des-monats.jpg">
                        <small>Empfohlene Größe: 800x600px</small>
                    </div>
                    <div class="form-group-mega">
                        <label>Link</label>
                        <input type="text" name="wine_month_link" value="<?php echo safe_output($settings['wine_month_link'] ?? ''); ?>" placeholder="z.B. ?page=shop&category=4">
                    </div>
                    <?php if (!empty($settings['wine_month_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo safe_output($settings['wine_month_image']); ?>" alt="Wein des Monats" style="max-width: 100%; height: auto; border-radius: 8px;">
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
            </form>

        <?php elseif ($tab === 'klara-products'): ?>
            <?php include 'components/admin-klara-products.php'; ?>

        <?php elseif ($tab === 'wines'): ?>
            <?php $wines = $db->query("SELECT w.*, c.name as cat_name FROM wines w LEFT JOIN categories c ON w.category_id = c.id ORDER BY w.name ASC")->fetch_all(MYSQLI_ASSOC); ?>
            
            <div class="wines-management">
                <div class="wines-header">
                    <h3><?php echo count($wines); ?> Weine</h3>
                    <a href="?page=admin-dashboard&tab=add-wine" class="btn btn-primary">Neuer Wein</a>
                </div>

                <table class="admin-table-mega">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Kategorie</th>
                            <th>Preis</th>
                            <th>Bestand</th>
                            <th>Produzent</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wines as $wine): ?>
                            <tr>
                                <td><strong><?php echo safe_output($wine['name']); ?></strong></td>
                                <td><?php echo safe_output($wine['cat_name'] ?? '-'); ?></td>
                                <td>CHF <?php echo number_format($wine['price'], 2); ?></td>
                                <td><span class="stock-badge <?php echo $wine['stock'] < 5 ? 'low' : ''; ?>"><?php echo $wine['stock']; ?></span></td>
                                <td><?php echo safe_output($wine['producer'] ?? '-'); ?></td>
                                <td>
                                    <a href="?page=admin-dashboard&wine_id=<?php echo $wine['id']; ?>" class="btn-small-admin">Bearbeiten</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Wirklich löschen?');">
                                        <input type="hidden" name="action" value="delete_wine">
                                        <input type="hidden" name="wine_id" value="<?php echo $wine['id']; ?>">
                                        <button type="submit" class="btn-small-admin danger">Löschen</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($tab === 'add-wine'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="add_wine">

                <div class="form-section-mega">
                    <h3>Grundinformationen</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Weinname *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Kategorie *</label>
                            <select name="category_id" required>
                                <option value="">-- Wähle Kategorie --</option>
                                <?php foreach (get_all_categories() as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo safe_output($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Preis & Bestand</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Preis (CHF) *</label>
                            <input type="number" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Bestand *</label>
                            <input type="number" name="stock" min="0" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Details</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Produzent</label>
                            <input type="text" name="producer">
                        </div>
                        <div class="form-group-mega">
                            <label>Jahrgang</label>
                            <input type="number" name="vintage" min="1900" max="2099">
                        </div>
                        <div class="form-group-mega">
                            <label>Region</label>
                            <input type="text" name="region">
                        </div>
                        <div class="form-group-mega">
                            <label>Alkohol %</label>
                            <input type="number" name="alcohol_content" step="0.1" min="0" max="20">
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Bild & Beschreibung</h3>
                    <div class="form-group-mega">
                        <label>Bild-URL</label>
                        <input type="text" name="image_url" placeholder="z.B. assets/images/wines/wein1.jpg">
                    </div>
                    <div class="form-group-mega">
                        <label>Beschreibung</label>
                        <textarea name="description" rows="5"></textarea>
                    </div>
                </div>

                <div class="form-actions-mega">
                    <button type="submit" class="btn btn-primary btn-lg">Wein hinzufügen</button>
                    <a href="?page=admin-dashboard&tab=wines" class="btn btn-secondary btn-lg">Zurück</a>
                </div>
            </form>

        <?php elseif ($tab === 'edit-wine' && $wine_edit): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="edit_wine">
                <input type="hidden" name="wine_id" value="<?php echo $wine_edit['id']; ?>">

                <div class="form-section-mega">
                    <h3>Grundinformationen</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Weinname *</label>
                            <input type="text" name="name" value="<?php echo safe_output($wine_edit['name']); ?>" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Kategorie *</label>
                            <select name="category_id" required>
                                <?php foreach (get_all_categories() as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>" <?php echo $cat['id'] == $wine_edit['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo safe_output($cat['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Preis & Bestand</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Preis (CHF) *</label>
                            <input type="number" name="price" step="0.01" value="<?php echo $wine_edit['price']; ?>" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Bestand *</label>
                            <input type="number" name="stock" value="<?php echo $wine_edit['stock']; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Details</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Produzent</label>
                            <input type="text" name="producer" value="<?php echo safe_output($wine_edit['producer']); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Jahrgang</label>
                            <input type="number" name="vintage" value="<?php echo $wine_edit['vintage']; ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Region</label>
                            <input type="text" name="region" value="<?php echo safe_output($wine_edit['region']); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Alkohol %</label>
                            <input type="number" name="alcohol_content" step="0.1" value="<?php echo $wine_edit['alcohol_content']; ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Bild & Beschreibung</h3>
                    <div class="form-group-mega">
                        <label>Bild-URL</label>
                        <input type="text" name="image_url" value="<?php echo safe_output($wine_edit['image_url']); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Beschreibung</label>
                        <textarea name="description" rows="5"><?php echo safe_output($wine_edit['description']); ?></textarea>
                    </div>
                </div>

                <div class="form-actions-mega">
                    <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
                    <a href="?page=admin-dashboard&tab=wines" class="btn btn-secondary btn-lg">Zurück</a>
                </div>
            </form>

        <?php elseif ($tab === 'news-items'): ?>
            <div class="admin-info-box">
                News/Neuheiten können Weine, Events oder allgemeine Nachrichten sein.
            </div>

            <div class="wines-header">
                <h3>News/Neuheiten verwalten</h3>
                <button onclick="showAddNewsModal()" class="btn btn-primary">Neues News-Item</button>
            </div>

            <div id="news-items-list">
                <p>Lade News-Items...</p>
            </div>

            <!-- News Item Edit Modal -->
            <div id="news-edit-modal" class="modal-overlay" style="display: none;">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <h3 id="news-modal-title">News-Item bearbeiten</h3>
                        <button onclick="closeNewsModal()" class="modal-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="news-edit-form" onsubmit="saveNewsItem(event)">
                            <input type="hidden" id="news-id" name="id">

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="news-title">Titel *</label>
                                    <input type="text" id="news-title" name="title" required class="form-control">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="news-type">Typ *</label>
                                    <select id="news-type" name="type" required class="form-control">
                                        <option value="general">Allgemein</option>
                                        <option value="wine">Wein</option>
                                        <option value="event">Event</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="news-display-order">Reihenfolge</label>
                                    <input type="number" id="news-display-order" name="display_order" class="form-control" value="0">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="news-content">Beschreibung</label>
                                    <textarea id="news-content" name="content" rows="4" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="news-image-url">Bild-URL</label>
                                    <input type="text" id="news-image-url" name="image_url" class="form-control" placeholder="z.B. assets/images/news/bild.jpg">
                                    <small class="form-hint">URL zum Bild oder leer lassen</small>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <label for="news-link-url">Link-URL</label>
                                    <input type="text" id="news-link-url" name="link_url" class="form-control" placeholder="z.B. ?page=shop&category=1">
                                    <small class="form-hint">Wohin soll die News verlinken?</small>
                                </div>
                            </div>

                            <div class="form-section-divider">
                                <h4>Farbeinstellungen</h4>
                                <p class="form-hint">Individuelle Farben für diese News (Hex-Codes, z.B. #722c2c)</p>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="news-bg-color">Hintergrundfarbe</label>
                                    <div class="color-picker-group">
                                        <input type="color" id="news-bg-color-picker" value="#722c2c" class="news-color-input">
                                        <input type="text" id="news-bg-color" name="bg_color" value="#722c2c" class="form-control" pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="news-text-color">Textfarbe</label>
                                    <div class="color-picker-group">
                                        <input type="color" id="news-text-color-picker" value="#ffffff" class="news-color-input">
                                        <input type="text" id="news-text-color" name="text_color" value="#ffffff" class="form-control" pattern="^#[0-9A-Fa-f]{6}$">
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group full-width">
                                    <div id="news-color-preview" class="color-preview-box">
                                        <strong>Vorschau:</strong> So sieht die News auf der Startseite aus
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" onclick="closeNewsModal()" class="btn btn-secondary">Abbrechen</button>
                                <button type="submit" class="btn btn-primary">Speichern</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <?php elseif ($tab === 'events'): ?>
            <?php $events = get_all_events(false, false); ?>

            <div class="wines-header">
                <h3><?php echo count($events); ?> Events</h3>
                <a href="?page=admin-dashboard&tab=add-event" class="btn btn-primary">Neues Event</a>
            </div>

            <table class="admin-table-mega">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Datum</th>
                        <th>Ort</th>
                        <th>Preis</th>
                        <th>Tickets</th>
                        <th>Status</th>
                        <th>Neuheit</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><strong><?php echo safe_output($event['name']); ?></strong></td>
                            <td><?php echo date('d.m.Y H:i', strtotime($event['event_date'])); ?></td>
                            <td><?php echo safe_output($event['location'] ?? '-'); ?></td>
                            <td>CHF <?php echo number_format($event['price'], 2); ?></td>
                            <td><?php echo $event['available_tickets']; ?> / <?php echo $event['max_participants'] ?: '∞'; ?></td>
                            <td>
                                <span class="stock-badge <?php echo $event['is_active'] ? '' : 'low'; ?>">
                                    <?php echo $event['is_active'] ? 'Aktiv' : 'Inaktiv'; ?>
                                </span>
                            </td>
                            <td>
                                <label class="featured-toggle">
                                    <input type="checkbox"
                                           <?php echo ($event['is_featured'] ?? 0) ? 'checked' : ''; ?>
                                           onchange="toggleEventFeatured(<?php echo $event['id']; ?>, this.checked)">
                                    <span><?php echo ($event['is_featured'] ?? 0) ? get_icon('star', 18, 'icon-gold') : get_icon('star', 18, 'icon-secondary'); ?></span>
                                </label>
                            </td>
                            <td>
                                <a href="?page=admin-dashboard&tab=edit-event&event_id=<?php echo $event['id']; ?>" class="btn-small-admin">
                                    <?php echo get_icon('edit', 14); ?> Bearbeiten
                                </a>
                                <button onclick="deleteEvent(<?php echo $event['id']; ?>)" class="btn-small-admin danger">
                                    <?php echo get_icon('trash', 14); ?> Löschen
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php elseif ($tab === 'add-event'): ?>
            <form method="POST" class="admin-form-mega" id="add-event-form">
                <input type="hidden" name="action" value="add_event">

                <div class="form-section-mega">
                    <h3>Event-Details</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Event-Name *</label>
                            <input type="text" name="name" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Ort</label>
                            <input type="text" name="location" placeholder="z.B. Vier Korken Weinlounge, Zürich">
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Datum & Preis</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Event-Datum & Uhrzeit *</label>
                            <input type="datetime-local" name="event_date" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Preis pro Ticket (CHF) *</label>
                            <input type="number" name="price" step="0.01" min="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Tickets & Kapazität</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Max. Teilnehmer (0 = unbegrenzt)</label>
                            <input type="number" name="max_participants" min="0" value="0">
                        </div>
                        <div class="form-group-mega">
                            <label>Verfügbare Tickets *</label>
                            <input type="number" name="available_tickets" min="0" value="0" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Beschreibung & Bild</h3>
                    <div class="form-group-mega">
                        <label>Bild-URL</label>
                        <input type="text" name="image_url" placeholder="z.B. assets/images/events/event1.jpg">
                    </div>
                    <div class="form-group-mega">
                        <label>Beschreibung</label>
                        <textarea name="description" rows="5"></textarea>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Neuheiten-Farben</h3>
                    <p class="form-hint">Farben für die Anzeige auf der Startseite (nur wenn als Neuheit markiert)</p>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Hintergrundfarbe</label>
                            <input type="color" name="featured_bg_color" value="#2c5282">
                        </div>
                        <div class="form-group-mega">
                            <label>Textfarbe</label>
                            <input type="color" name="featured_text_color" value="#ffffff">
                        </div>
                    </div>
                </div>

                <div class="form-actions-mega">
                    <button type="submit" class="btn btn-primary btn-lg">Event erstellen</button>
                    <a href="?page=admin-dashboard&tab=events" class="btn btn-secondary btn-lg">Zurück</a>
                </div>
            </form>

        <?php elseif ($tab === 'edit-event'): ?>
            <?php
            $event_id = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;
            $event = get_event_by_id($event_id);
            if (!$event):
            ?>
                <div class="alert alert-error">Event nicht gefunden</div>
            <?php else: ?>
            <form method="POST" class="admin-form-mega" id="edit-event-form">
                <input type="hidden" name="action" value="edit_event">
                <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">

                <div class="form-section-mega">
                    <h3>Event-Details</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Event-Name *</label>
                            <input type="text" name="name" value="<?php echo safe_output($event['name']); ?>" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Ort</label>
                            <input type="text" name="location" value="<?php echo safe_output($event['location']); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Datum & Preis</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Event-Datum & Uhrzeit *</label>
                            <input type="datetime-local" name="event_date" value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
                        </div>
                        <div class="form-group-mega">
                            <label>Preis pro Ticket (CHF) *</label>
                            <input type="number" name="price" step="0.01" value="<?php echo $event['price']; ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Tickets & Kapazität</h3>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Max. Teilnehmer (0 = unbegrenzt)</label>
                            <input type="number" name="max_participants" value="<?php echo $event['max_participants']; ?>" min="0">
                        </div>
                        <div class="form-group-mega">
                            <label>Verfügbare Tickets *</label>
                            <input type="number" name="available_tickets" value="<?php echo $event['available_tickets']; ?>" min="0" required>
                        </div>
                    </div>
                    <div class="form-group-mega">
                        <label>
                            <input type="checkbox" name="is_active" value="1" <?php echo $event['is_active'] ? 'checked' : ''; ?>>
                            Event ist aktiv
                        </label>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Beschreibung & Bild</h3>
                    <div class="form-group-mega">
                        <label>Bild-URL</label>
                        <input type="text" name="image_url" value="<?php echo safe_output($event['image_url']); ?>">
                    </div>
                    <div class="form-group-mega">
                        <label>Beschreibung</label>
                        <textarea name="description" rows="5"><?php echo safe_output($event['description']); ?></textarea>
                    </div>
                </div>

                <div class="form-section-mega">
                    <h3>Neuheiten-Farben</h3>
                    <p class="form-hint">Farben für die Anzeige auf der Startseite (nur wenn als Neuheit markiert)</p>
                    <div class="form-grid-2col">
                        <div class="form-group-mega">
                            <label>Hintergrundfarbe</label>
                            <input type="color" name="featured_bg_color" value="<?php echo safe_output($event['featured_bg_color'] ?? '#2c5282'); ?>">
                        </div>
                        <div class="form-group-mega">
                            <label>Textfarbe</label>
                            <input type="color" name="featured_text_color" value="<?php echo safe_output($event['featured_text_color'] ?? '#ffffff'); ?>">
                        </div>
                    </div>
                </div>

                <div class="form-actions-mega">
                    <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
                    <a href="?page=admin-dashboard&tab=events" class="btn btn-secondary btn-lg">Zurück</a>
                </div>
            </form>
            <?php endif; ?>

        <?php elseif ($tab === 'coupons'): ?>
            <h2>Gutscheine verwalten</h2>

            <button onclick="showAddCouponForm()" class="btn btn-primary" style="margin-bottom: 1.5rem;">
                + Neuer Gutschein
            </button>

            <div id="coupon-form-container" style="display: none; margin-bottom: 2rem;">
                <div class="card">
                    <h3 id="coupon-form-title">Neuer Gutschein</h3>
                    <form id="coupon-form">
                        <input type="hidden" id="coupon-id" name="id">

                        <div class="form-row-2">
                            <div class="form-group-admin">
                                <label>Code *</label>
                                <input type="text" id="coupon-code" name="code" required style="text-transform: uppercase;">
                            </div>
                            <div class="form-group-admin">
                                <label>Rabatt-Typ *</label>
                                <select id="coupon-discount-type" name="discount_type" required>
                                    <option value="percentage">Prozentual (%)</option>
                                    <option value="fixed">Fixer Betrag (CHF)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-admin">
                                <label>Rabatt-Wert *</label>
                                <input type="number" id="coupon-discount-value" name="discount_value" step="0.01" min="0" required>
                            </div>
                            <div class="form-group-admin">
                                <label>Mindestbestellwert (CHF)</label>
                                <input type="number" id="coupon-min-order" name="min_order_amount" step="0.01" min="0" value="0">
                            </div>
                        </div>

                        <div class="form-group-admin">
                            <label>Beschreibung</label>
                            <input type="text" id="coupon-description" name="description">
                        </div>

                        <div class="form-row-3">
                            <div class="form-group-admin">
                                <label>Max. Rabatt (CHF)</label>
                                <input type="number" id="coupon-max-discount" name="max_discount_amount" step="0.01" min="0">
                                <small>Optional: Maximale Rabatthöhe bei %-Rabatten</small>
                            </div>
                            <div class="form-group-admin">
                                <label>Verwendungslimit</label>
                                <input type="number" id="coupon-usage-limit" name="usage_limit" min="1">
                                <small>Optional: Wie oft kann der Code verwendet werden</small>
                            </div>
                            <div class="form-group-admin">
                                <label>
                                    <input type="checkbox" id="coupon-active" name="is_active" checked>
                                    Aktiv
                                </label>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group-admin">
                                <label>Gültig ab</label>
                                <input type="datetime-local" id="coupon-valid-from" name="valid_from">
                            </div>
                            <div class="form-group-admin">
                                <label>Gültig bis</label>
                                <input type="datetime-local" id="coupon-valid-until" name="valid_until">
                            </div>
                        </div>

                        <div class="btn-group">
                            <button type="submit" class="btn btn-success">Speichern</button>
                            <button type="button" class="btn btn-secondary" onclick="hideCouponForm()">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="coupons-list" class="data-table-container">
                <p>Lade Gutscheine...</p>
            </div>

        <?php elseif ($tab === 'orders'): ?>
            <h2>Bestellungen verwalten</h2>

            <div class="filter-tabs" style="margin-bottom: 1.5rem;">
                <button class="filter-tab active" data-status="all">Alle</button>
                <button class="filter-tab" data-status="pending">Ausstehend</button>
                <button class="filter-tab" data-status="processing">In Bearbeitung</button>
                <button class="filter-tab" data-status="shipped">Versandt</button>
                <button class="filter-tab" data-status="delivered">Zugestellt</button>
                <button class="filter-tab" data-status="cancelled">Storniert</button>
            </div>

            <div id="orders-list" class="data-table-container">
                <p>Lade Bestellungen...</p>
            </div>

        <?php else: ?>
            <div class="coming-soon">
                <h2>Diese Seite wird vorbereitet</h2>
            </div>
        <?php endif; ?>
    </main>
</div>

<style>
.admin-dashboard-mega {
    display: grid;
    grid-template-columns: 260px 1fr;
    min-height: 100vh;
    background: #f5f6fa;
}

.admin-sidebar-mega {
    background: var(--primary-color);
    color: white;
    padding: 1.5rem;
    position: sticky;
    top: 0;
    height: 100vh;
    overflow-y: auto;
}

.sidebar-header h2 {
    margin: 0 0 0.5rem 0;
    color: white;
    border: none;
    font-size: 1.2rem;
}

.sidebar-header p {
    margin: 0;
    font-size: 0.85rem;
    opacity: 0.8;
}

.sidebar-nav-mega {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 2rem;
}

.sidebar-nav-mega a {
    padding: 0.8rem 1rem;
    background: rgba(255,255,255,0.1);
    color: white;
    border-radius: 5px;
    transition: all 0.3s;
    text-decoration: none;
    font-weight: 500;
}

.sidebar-nav-mega a:hover {
    background: rgba(255,255,255,0.2);
}

.sidebar-nav-mega a.active {
    background: white;
    color: var(--primary-color);
}

.nav-divider {
    color: rgba(255,255,255,0.6);
    font-size: 0.75rem;
    font-weight: 600;
    margin: 1rem 0 0.5rem;
    text-transform: uppercase;
}

.nav-home {
    margin-top: 2rem;
    border-top: 1px solid rgba(255,255,255,0.2);
    padding-top: 1rem !important;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-logout {
    margin-top: 0.5rem;
    background: rgba(220, 53, 69, 0.2) !important;
    border: 1px solid rgba(220, 53, 69, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.nav-logout:hover {
    background: rgba(220, 53, 69, 0.3) !important;
    border-color: rgba(220, 53, 69, 0.5);
}

.admin-content-mega {
    padding: 2rem;
}

.admin-top-bar h1 {
    margin: 0;
    border: none;
}

.admin-info-box {
    background: #e3f2fd;
    border-left: 4px solid #2196F3;
    padding: 1rem;
    border-radius: 5px;
    margin-bottom: 2rem;
    color: #1565c0;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 2rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

.alert-warning {
    background: #fff3cd;
    color: #856404;
}

.stats-grid-admin {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card-admin {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-top: 0.5rem;
}

.welcome-box {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.welcome-box h2 {
    margin-top: 0;
    border: none;
}

.admin-form-mega {
    background: white;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 2rem;
}

.form-section-mega {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid #f0f0f0;
}

.form-section-mega:last-of-type {
    border: none;
}

.form-section-mega h3 {
    margin-top: 0;
}

.form-section-mega h4 {
    color: var(--primary-color);
    margin-top: 0;
}

.form-group-mega {
    margin-bottom: 1.5rem;
}

.form-group-mega label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.form-group-mega input[type="text"],
.form-group-mega input[type="email"],
.form-group-mega input[type="url"],
.form-group-mega input[type="number"],
.form-group-mega select,
.form-group-mega textarea {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s;
    font-family: inherit;
}

.form-group-mega input:focus,
.form-group-mega select:focus,
.form-group-mega textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.form-grid-2col {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.about-cards-edit {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.card-edit {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border: 2px solid #e0e0e0;
}

.card-edit h4 {
    margin-top: 0;
    color: var(--primary-color);
}

.card-edit input,
.card-edit textarea {
    width: 100%;
    padding: 0.6rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 0.8rem;
    font-family: inherit;
}

.colors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 2rem;
    margin-top: 1.5rem;
}

.color-picker {
    text-align: center;
}

.color-picker label {
    display: block;
    margin-bottom: 1rem;
    font-weight: 600;
}

.color-picker input[type="color"] {
    width: 100%;
    height: 80px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    margin-bottom: 0.5rem;
}

.color-picker input[type="text"] {
    width: 100%;
    text-align: center;
    padding: 0.5rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
}

.btn {
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s;
}

.btn-primary {
    background: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
}

.btn-secondary {
    background: #e0e0e0;
    color: #333;
}

.btn-success {
    background: #4CAF50;
    color: white;
}

.btn-lg {
    padding: 1rem 2rem;
}

.admin-table-mega {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-top: 1.5rem;
}

.admin-table-mega th {
    background: var(--primary-color);
    color: white;
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.admin-table-mega td {
    padding: 1rem;
    border-bottom: 1px solid #f0f0f0;
}

.stock-badge {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #4CAF50;
    color: white;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.stock-badge.low {
    background: #ff9800;
}

.btn-small-admin {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-right: 0.5rem;
    font-size: 0.9rem;
    text-decoration: none;
}

.btn-small-admin.danger,
.btn-small-admin.btn-danger {
    background: #dc3545;
}

.btn-small-admin:hover {
    opacity: 0.8;
}

.admin-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.image-preview {
    background: #f8f9fa;
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
    border: 1px solid #e0e0e0;
}

.coming-soon {
    background: white;
    padding: 4rem 2rem;
    border-radius: 10px;
    text-align: center;
}

.coming-soon h2 {
    color: var(--primary-color);
    border: none;
}

.wines-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.wines-header h3 {
    margin: 0;
}

.form-actions-mega {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
}

/* Mobile Navigation Toggle */
@media (max-width: 1024px) {
    .admin-dashboard-mega {
        grid-template-columns: 1fr;
    }

    .admin-sidebar-mega {
        position: fixed;
        left: 0;
        top: 0;
        height: 100vh;
        width: 260px;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .admin-sidebar-mega.active {
        transform: translateX(0);
    }

    .admin-content-mega {
        margin-left: 0;
    }

    .form-grid-2col {
        grid-template-columns: 1fr;
    }

    .about-cards-edit {
        grid-template-columns: 1fr;
    }

    .colors-grid {
        grid-template-columns: 1fr;
    }

    .stats-grid-admin {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    }

    .wines-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .wines-header h3 {
        text-align: center;
    }

    .admin-table-mega {
        font-size: 0.85rem;
        overflow-x: auto;
        display: block;
    }

    .admin-table-mega th,
    .admin-table-mega td {
        padding: 0.6rem 0.4rem;
        font-size: 0.8rem;
    }
}

/* News Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.7);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content {
    background: white;
    border-radius: 12px;
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-lg {
    max-width: 900px;
}

.modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: var(--primary-color);
}

.modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    cursor: pointer;
    color: #999;
    line-height: 1;
    padding: 0;
    width: 30px;
    height: 30px;
}

.modal-close:hover {
    color: #333;
}

.modal-body {
    padding: 2rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.9rem;
}

.form-control {
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(114, 44, 44, 0.1);
}

textarea.form-control {
    resize: vertical;
    font-family: inherit;
}

.form-hint {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 0.3rem;
}

.form-section-divider {
    margin: 2rem 0 1.5rem 0;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
}

.form-section-divider h4 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
}

.color-picker-group {
    display: flex;
    gap: 0.75rem;
    align-items: center;
}

.news-color-input {
    width: 60px;
    height: 45px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
}

.color-preview-box {
    padding: 1.5rem;
    border-radius: 8px;
    text-align: center;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #722c2c;
    color: #ffffff;
}

.modal-footer {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding-top: 1.5rem;
    border-top: 2px solid #e5e7eb;
    margin-top: 1.5rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }

    .modal-content {
        width: 95%;
        max-height: 95vh;
    }

    .modal-body {
        padding: 1.5rem;
    }
}

@media (max-width: 768px) {
    .admin-top-bar h1 {
        font-size: 1.5rem;
    }

    .stats-grid-admin {
        grid-template-columns: 1fr;
    }

    .form-actions-mega {
        flex-direction: column;
    }

    .form-actions-mega .btn {
        width: 100%;
    }

    .admin-form-mega {
        padding: 1rem;
    }
}

@media (max-width: 480px) {
    .admin-content-mega {
        padding: 1rem;
    }

    .admin-top-bar h1 {
        font-size: 1.2rem;
    }

    .stat-number {
        font-size: 1.8rem;
    }

    .btn {
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
    }
}

/* Mobile Menu Toggle Button */
@media (max-width: 1024px) {
    .admin-mobile-toggle {
        position: fixed;
        bottom: 20px;
        right: 20px;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        cursor: pointer;
        z-index: 999;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .admin-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
    }

    .admin-overlay.active {
        display: block;
    }
}

@media (min-width: 1025px) {
    .admin-mobile-toggle {
        display: none !important;
    }

    .admin-overlay {
        display: none !important;
    }
}
</style>

<script>
// Remove Featured Item
function removeFeatured(type, id) {
    console.log('removeFeatured called with type:', type, 'id:', id);

    if (!confirm('Möchtest du diesen Eintrag wirklich von den Neuheiten entfernen?')) {
        console.log('User cancelled removal');
        return;
    }

    const body = `type=${encodeURIComponent(type)}&id=${encodeURIComponent(id)}`;
    console.log('Sending to API:', body);

    fetch('api/remove-featured.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            showNotification('Erfolgreich entfernt', 'success');
            // Force reload ohne Cache
            setTimeout(() => {
                window.location.href = window.location.href.split('?')[0] + '?page=admin-dashboard&tab=featured-wines&nocache=' + Date.now();
            }, 500);
        } else {
            showNotification('Fehler: ' + (data.error || 'Unbekannter Fehler'), 'error');
            console.error('API error:', data.error, 'DB error:', data.db_error);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        showNotification('Fehler beim Entfernen', 'error');
    });
}

// Toggle Event Featured Status
function toggleEventFeatured(eventId, isFeatured) {
    fetch('api/toggle-event-featured.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `event_id=${eventId}&is_featured=${isFeatured ? 1 : 0}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(isFeatured ? 'Als Neuheit markiert' : 'Von Neuheiten entfernt', 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showNotification('Fehler: ' + (data.error || 'Unbekannter Fehler'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Fehler beim Aktualisieren', 'error');
    });
}

// Admin Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('admin-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('admin-overlay');

    if (menuToggle && sidebar && overlay) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        });

        overlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
        });

        // Close menu on nav click
        const navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            });
        });
    }

    // Load News Items if on news-items tab
    if (document.getElementById('news-items-list')) {
        loadNewsItems();
    }
});

// NEWS ITEMS MANAGEMENT
function loadNewsItems() {
    fetch('api/news-items.php?action=get_all&active_only=false')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                renderNewsItems(data.data);
            } else {
                document.getElementById('news-items-list').innerHTML = '<p>Fehler beim Laden</p>';
            }
        })
        .catch(e => {
            console.error('Error:', e);
            document.getElementById('news-items-list').innerHTML = '<p>Fehler beim Laden</p>';
        });
}

function renderNewsItems(items) {
    const container = document.getElementById('news-items-list');

    if (items.length === 0) {
        container.innerHTML = '<p>Keine News-Items vorhanden.</p>';
        return;
    }

    const html = `
        <table class="admin-table-mega">
            <thead>
                <tr>
                    <th>Titel</th>
                    <th>Typ</th>
                    <th>Reihenfolge</th>
                    <th>Status</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
                ${items.map(item => `
                    <tr>
                        <td><strong>${escapeHtml(item.title)}</strong></td>
                        <td><span class="badge-type-${item.type}">${getTypeLabel(item.type)}</span></td>
                        <td>${item.display_order}</td>
                        <td>
                            <span class="stock-badge ${item.is_active ? '' : 'low'}">
                                ${item.is_active ? 'Aktiv' : 'Inaktiv'}
                            </span>
                        </td>
                        <td>
                            <button onclick="editNewsItem(${item.id})" class="btn-small-admin">Bearbeiten</button>
                            <button onclick="toggleNewsActive(${item.id})" class="btn-small-admin">
                                ${item.is_active ? 'Deaktivieren' : 'Aktivieren'}
                            </button>
                            <button onclick="deleteNewsItem(${item.id})" class="btn-small-admin danger">Löschen</button>
                        </td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    container.innerHTML = html;
}

function getTypeLabel(type) {
    const labels = {
        'wine': 'Wein',
        'event': 'Event',
        'general': 'Allgemein'
    };
    return labels[type] || type;
}

function showAddNewsModal() {
    const title = prompt('News-Titel:');
    if (!title) return;

    const type = prompt('Typ (wine/event/general):');
    if (!type) return;

    const content = prompt('Beschreibung (optional):');
    const imageUrl = prompt('Bild-URL (optional):');
    const linkUrl = prompt('Link-URL (optional):');

    const data = { title, type, content, image_url: imageUrl, link_url: linkUrl };

    fetch('api/news-items.php?action=create', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('News-Item erstellt!');
            loadNewsItems();
        } else {
            alert('Fehler: ' + data.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Erstellen');
    });
}

function toggleNewsActive(id) {
    if (!confirm('Status wirklich ändern?')) return;

    fetch('api/news-items.php?action=toggle_active', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            loadNewsItems();
        } else {
            alert('Fehler: ' + data.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Aktualisieren');
    });
}

function deleteNewsItem(id) {
    if (!confirm('News-Item wirklich löschen?')) return;

    fetch('api/news-items.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Gelöscht!');
            loadNewsItems();
        } else {
            alert('Fehler: ' + data.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Löschen');
    });
}

// Edit News Item - Open Modal with Data
function editNewsItem(id) {
    fetch(`api/news-items.php?action=get_one&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                openNewsModal(data.data);
            } else {
                alert('Fehler: ' + data.error);
            }
        })
        .catch(e => {
            console.error('Error:', e);
            alert('Fehler beim Laden');
        });
}

// Open News Modal (for both create and edit)
function openNewsModal(item = null) {
    const modal = document.getElementById('news-edit-modal');
    const title = document.getElementById('news-modal-title');
    const form = document.getElementById('news-edit-form');

    if (item) {
        // Edit mode
        title.textContent = 'News-Item bearbeiten';
        document.getElementById('news-id').value = item.id || '';
        document.getElementById('news-title').value = item.title || '';
        document.getElementById('news-type').value = item.type || 'general';
        document.getElementById('news-content').value = item.content || '';
        document.getElementById('news-image-url').value = item.image_url || '';
        document.getElementById('news-link-url').value = item.link_url || '';
        document.getElementById('news-display-order').value = item.display_order || 0;

        // Farben setzen
        const bgColor = item.bg_color || '#722c2c';
        const textColor = item.text_color || '#ffffff';

        document.getElementById('news-bg-color').value = bgColor;
        document.getElementById('news-bg-color-picker').value = bgColor;
        document.getElementById('news-text-color').value = textColor;
        document.getElementById('news-text-color-picker').value = textColor;

        updateColorPreview();
    } else {
        // Create mode
        title.textContent = 'Neues News-Item erstellen';
        form.reset();
        document.getElementById('news-bg-color').value = '#722c2c';
        document.getElementById('news-bg-color-picker').value = '#722c2c';
        document.getElementById('news-text-color').value = '#ffffff';
        document.getElementById('news-text-color-picker').value = '#ffffff';
        updateColorPreview();
    }

    modal.style.display = 'flex';

    // Color picker sync
    setupColorPickers();
}

// Setup Color Picker Sync
function setupColorPickers() {
    const bgPicker = document.getElementById('news-bg-color-picker');
    const bgInput = document.getElementById('news-bg-color');
    const textPicker = document.getElementById('news-text-color-picker');
    const textInput = document.getElementById('news-text-color');

    bgPicker.addEventListener('input', function() {
        bgInput.value = this.value;
        updateColorPreview();
    });

    bgInput.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            bgPicker.value = this.value;
            updateColorPreview();
        }
    });

    textPicker.addEventListener('input', function() {
        textInput.value = this.value;
        updateColorPreview();
    });

    textInput.addEventListener('input', function() {
        if (/^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            textPicker.value = this.value;
            updateColorPreview();
        }
    });
}

// Update Color Preview
function updateColorPreview() {
    const preview = document.getElementById('news-color-preview');
    const bgColor = document.getElementById('news-bg-color').value;
    const textColor = document.getElementById('news-text-color').value;

    preview.style.backgroundColor = bgColor;
    preview.style.color = textColor;
}

// Close News Modal
function closeNewsModal() {
    const modal = document.getElementById('news-edit-modal');
    modal.style.display = 'none';
}

// Save News Item (Create or Update)
function saveNewsItem(event) {
    event.preventDefault();

    const form = document.getElementById('news-edit-form');
    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
        data[key] = value;
    });

    const newsId = data.id;
    const action = newsId ? 'update' : 'create';
    const url = `api/news-items.php?action=${action}`;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            alert(newsId ? 'News-Item aktualisiert!' : 'News-Item erstellt!');
            closeNewsModal();
            loadNewsItems();
        } else {
            alert('Fehler: ' + result.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Speichern');
    });
}

// Update showAddNewsModal to use new modal
function showAddNewsModal() {
    openNewsModal(null);
}

// EVENT MANAGEMENT
function deleteEvent(id) {
    if (!confirm('Event wirklich löschen?')) return;

    fetch('api/events.php?action=delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Event gelöscht!');
            location.reload();
        } else {
            alert('Fehler: ' + data.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Löschen');
    });
}

// HELPER
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ========== COUPON MANAGEMENT ==========
function loadCoupons() {
    fetch('api/coupons.php?action=get_all')
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('coupons-list').innerHTML = '<p style="color: #c62828;">Fehler beim Laden</p>';
                return;
            }

            if (d.coupons.length === 0) {
                document.getElementById('coupons-list').innerHTML = '<p>Keine Gutscheine vorhanden</p>';
                return;
            }

            let html = '<table class="data-table"><thead><tr>';
            html += '<th>Code</th><th>Typ</th><th>Wert</th><th>Verwendet</th><th>Status</th><th>Gültig bis</th><th>Aktionen</th>';
            html += '</tr></thead><tbody>';

            d.coupons.forEach(c => {
                const discountDisplay = c.discount_type === 'percentage'
                    ? c.discount_value + '%'
                    : 'CHF ' + parseFloat(c.discount_value).toFixed(2);

                const usageDisplay = c.usage_limit
                    ? `${c.used_count}/${c.usage_limit}`
                    : c.used_count;

                const validUntil = c.valid_until
                    ? new Date(c.valid_until).toLocaleDateString('de-CH')
                    : '-';

                html += `<tr>
                    <td><strong>${escapeHtml(c.code)}</strong><br><small>${escapeHtml(c.description || '')}</small></td>
                    <td>${c.discount_type === 'percentage' ? 'Prozentual' : 'Fix'}</td>
                    <td>${discountDisplay}</td>
                    <td>${usageDisplay}</td>
                    <td><span class="badge ${c.is_active ? 'badge-success' : 'badge-secondary'}">${c.is_active ? 'Aktiv' : 'Inaktiv'}</span></td>
                    <td>${validUntil}</td>
                    <td class="table-actions">
                        <button onclick="editCoupon(${c.id})" class="btn-icon" title="Bearbeiten">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                        </button>
                        <button onclick="toggleCouponActive(${c.id})" class="btn-icon" title="Aktivieren/Deaktivieren">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="6" y="4" width="4" height="16"></rect>
                                <rect x="14" y="4" width="4" height="16"></rect>
                            </svg>
                        </button>
                        <button onclick="deleteCoupon(${c.id}, '${escapeHtml(c.code)}')" class="btn-icon" title="Löschen">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            document.getElementById('coupons-list').innerHTML = html;
        })
        .catch(e => {
            console.error(e);
            document.getElementById('coupons-list').innerHTML = '<p style="color: #c62828;">Fehler beim Laden</p>';
        });
}

function showAddCouponForm() {
    document.getElementById('coupon-form-title').textContent = 'Neuer Gutschein';
    document.getElementById('coupon-form').reset();
    document.getElementById('coupon-id').value = '';
    document.getElementById('coupon-form-container').style.display = 'block';
    document.getElementById('coupon-code').disabled = false;
}

function hideCouponForm() {
    document.getElementById('coupon-form-container').style.display = 'none';
}

function editCoupon(id) {
    fetch(`api/coupons.php?action=get_all`)
        .then(r => r.json())
        .then(d => {
            const coupon = d.coupons.find(c => c.id == id);
            if (!coupon) return;

            document.getElementById('coupon-form-title').textContent = 'Gutschein bearbeiten';
            document.getElementById('coupon-id').value = coupon.id;
            document.getElementById('coupon-code').value = coupon.code;
            document.getElementById('coupon-code').disabled = true;
            document.getElementById('coupon-discount-type').value = coupon.discount_type;
            document.getElementById('coupon-discount-value').value = coupon.discount_value;
            document.getElementById('coupon-min-order').value = coupon.min_order_amount;
            document.getElementById('coupon-description').value = coupon.description || '';
            document.getElementById('coupon-max-discount').value = coupon.max_discount_amount || '';
            document.getElementById('coupon-usage-limit').value = coupon.usage_limit || '';
            document.getElementById('coupon-active').checked = coupon.is_active == 1;
            document.getElementById('coupon-valid-from').value = coupon.valid_from ? coupon.valid_from.slice(0,16) : '';
            document.getElementById('coupon-valid-until').value = coupon.valid_until ? coupon.valid_until.slice(0,16) : '';
            document.getElementById('coupon-form-container').style.display = 'block';
        });
}

document.getElementById('coupon-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const id = document.getElementById('coupon-id').value;
    formData.append('action', id ? 'update' : 'create');

    fetch('api/coupons.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert(d.message);
            hideCouponForm();
            loadCoupons();
        } else {
            alert('Fehler: ' + d.error);
        }
    })
    .catch(e => {
        console.error(e);
        alert('Fehler beim Speichern');
    });
});

function deleteCoupon(id, code) {
    if (!confirm(`Gutschein "${code}" wirklich löschen?`)) return;

    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', id);

    fetch('api/coupons.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert(d.message);
            loadCoupons();
        } else {
            alert('Fehler: ' + d.error);
        }
    });
}

function toggleCouponActive(id) {
    const formData = new FormData();
    formData.append('action', 'toggle_active');
    formData.append('id', id);

    fetch('api/coupons.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            loadCoupons();
        } else {
            alert('Fehler: ' + d.error);
        }
    });
}

// ========== ORDER MANAGEMENT ==========
let currentOrderFilter = 'all';

function loadOrders(status = 'all') {
    currentOrderFilter = status;
    const url = status === 'all'
        ? 'api/orders.php?action=get_all_orders'
        : `api/orders.php?action=get_all_orders&status=${status}`;

    fetch(url)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                document.getElementById('orders-list').innerHTML = '<p style="color: #c62828;">Fehler beim Laden</p>';
                return;
            }

            if (d.orders.length === 0) {
                document.getElementById('orders-list').innerHTML = '<p>Keine Bestellungen gefunden</p>';
                return;
            }

            let html = '<table class="data-table"><thead><tr>';
            html += '<th>Bestellnr.</th><th>Datum</th><th>Kunde</th><th>Artikel</th><th>Betrag</th><th>Status</th><th>Zahlung</th><th>Aktionen</th>';
            html += '</tr></thead><tbody>';

            d.orders.forEach(o => {
                const statusLabels = {
                    'pending': 'Ausstehend',
                    'processing': 'In Bearbeitung',
                    'shipped': 'Versandt',
                    'delivered': 'Zugestellt',
                    'cancelled': 'Storniert'
                };

                const statusColors = {
                    'pending': 'badge-warning',
                    'processing': 'badge-info',
                    'shipped': 'badge-primary',
                    'delivered': 'badge-success',
                    'cancelled': 'badge-danger'
                };

                const paymentLabels = {
                    'card': 'Karte',
                    'twint': 'TWINT',
                    'cash': 'Bar'
                };

                html += `<tr>
                    <td><strong>${o.order_number}</strong></td>
                    <td>${new Date(o.created_at).toLocaleDateString('de-CH')}</td>
                    <td>${escapeHtml(o.delivery_first_name + ' ' + o.delivery_last_name)}<br><small>${escapeHtml(o.delivery_email)}</small></td>
                    <td>${o.item_count} Artikel</td>
                    <td><strong>CHF ${parseFloat(o.total_amount).toFixed(2)}</strong></td>
                    <td><span class="badge ${statusColors[o.order_status]}">${statusLabels[o.order_status]}</span></td>
                    <td>${paymentLabels[o.payment_method]}</td>
                    <td class="table-actions">
                        <button onclick="viewAdminOrderDetails(${o.id})" class="btn-icon" title="Details">👁️</button>
                        <button onclick="changeOrderStatus(${o.id}, '${o.order_status}')" class="btn-icon" title="Status ändern">📝</button>
                        <button onclick="deleteOrder(${o.id}, '${o.order_number}')" class="btn-icon btn-danger" title="Löschen">🗑️</button>
                    </td>
                </tr>`;
            });

            html += '</tbody></table>';
            document.getElementById('orders-list').innerHTML = html;
        })
        .catch(e => {
            console.error(e);
            document.getElementById('orders-list').innerHTML = '<p style="color: #c62828;">Fehler beim Laden</p>';
        });
}

// Show notification toast
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `admin-notification admin-notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ'}</span>
            <span class="notification-message">${message}</span>
        </div>
    `;
    document.body.appendChild(notification);

    setTimeout(() => notification.classList.add('show'), 10);

    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function viewAdminOrderDetails(orderId) {
    fetch(`api/orders.php?action=get_order_details_admin&order_id=${orderId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.success) {
                alert('Fehler: ' + d.error);
                return;
            }

            const o = d.order;
            const items = d.items;

            const deliveryMethodLabel = o.delivery_method === 'pickup' ? 'Abholung' : 'Lieferung';
            const addressLabel = o.delivery_method === 'pickup' ? 'Kontaktdaten' : 'Lieferadresse';
            const paymentMethodLabel = o.payment_method === 'card' ? 'Karte' : o.payment_method === 'twint' ? 'TWINT' : 'Barzahlung';
            const statusLabels = {
                'pending': 'Ausstehend',
                'processing': 'In Bearbeitung',
                'shipped': 'Versandt',
                'delivered': 'Zugestellt',
                'cancelled': 'Storniert'
            };
            const paymentStatusLabels = {
                'pending': 'Ausstehend',
                'completed': 'Abgeschlossen',
                'failed': 'Fehlgeschlagen',
                'refunded': 'Rückerstattet'
            };

            // Build items HTML
            let itemsHtml = '';
            items.forEach(item => {
                const itemIcon = item.item_type === 'event' ? '🎫' : '🍷';
                const productDetails = item.item_type === 'wine' ?
                    `<small>${escapeHtml(item.producer || '')}</small><br><small>${escapeHtml(item.vintage || '')} • ${escapeHtml(item.region || '')}</small>` :
                    '';

                itemsHtml += `
                    <div class="order-item" style="display: grid; grid-template-columns: 40px 1fr auto; gap: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 5px; margin-bottom: 0.5rem;">
                        <div style="font-size: 2rem;">${itemIcon}</div>
                        <div>
                            <strong>${escapeHtml(item.name)}</strong><br>
                            ${productDetails}
                            <br><small>Menge: ${item.quantity}x • CHF ${parseFloat(item.unit_price).toFixed(2)}</small>
                        </div>
                        <div style="text-align: right;">
                            <strong>CHF ${parseFloat(item.total_price).toFixed(2)}</strong>
                        </div>
                    </div>
                `;
            });

            const modalHtml = `
                <div class="admin-order-modal" style="max-width: 900px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                        <div class="detail-section">
                            <h3 style="margin-top: 0; color: var(--primary-color); border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem;">Bestellinformationen</h3>
                            <p><strong>Bestellnummer:</strong> ${escapeHtml(o.order_number)}</p>
                            <p><strong>Datum:</strong> ${new Date(o.created_at).toLocaleDateString('de-CH')} ${new Date(o.created_at).toLocaleTimeString('de-CH')}</p>
                            <p><strong>Status:</strong> <span class="badge badge-${o.order_status === 'delivered' ? 'success' : o.order_status === 'cancelled' ? 'danger' : o.order_status === 'shipped' ? 'primary' : 'warning'}">${statusLabels[o.order_status]}</span></p>
                            ${o.user_id ? `<p><strong>Kunde-ID:</strong> ${o.user_id}</p>` : '<p><strong>Gastbestellung</strong></p>'}
                            ${o.notes ? `<p><strong>Notizen:</strong> ${escapeHtml(o.notes)}</p>` : ''}
                        </div>

                        <div class="detail-section">
                            <h3 style="margin-top: 0; color: var(--primary-color); border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem;">Zahlungsinformationen</h3>
                            <p><strong>Methode:</strong> ${paymentMethodLabel}</p>
                            <p><strong>Zahlungsstatus:</strong> <span class="badge badge-${o.payment_status === 'completed' ? 'success' : o.payment_status === 'failed' ? 'danger' : 'warning'}">${paymentStatusLabels[o.payment_status]}</span></p>
                            ${o.payment_transaction_id ? `<p><strong>Transaktions-ID:</strong> ${escapeHtml(o.payment_transaction_id)}</p>` : ''}
                            <div style="margin-top: 1rem; padding: 1rem; background: #e8f5e9; border-radius: 5px;">
                                <p style="margin: 0.3rem 0;"><strong>Zwischensumme:</strong> CHF ${parseFloat(o.subtotal).toFixed(2)}</p>
                                <p style="margin: 0.3rem 0;"><strong>Versand:</strong> CHF ${parseFloat(o.shipping_cost).toFixed(2)}</p>
                                ${o.discount_amount > 0 ? `<p style="margin: 0.3rem 0; color: #27ae60;"><strong>Rabatt:</strong> -CHF ${parseFloat(o.discount_amount).toFixed(2)}</p>` : ''}
                                <p style="margin: 0.3rem 0; font-size: 1.2rem; border-top: 2px solid #27ae60; padding-top: 0.5rem; margin-top: 0.5rem;"><strong>Total:</strong> CHF ${parseFloat(o.total_amount).toFixed(2)}</p>
                            </div>
                        </div>
                    </div>

                    <div class="detail-section" style="margin-bottom: 2rem;">
                        <h3 style="margin-top: 0; color: var(--primary-color); border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem;">${addressLabel}</h3>
                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                            <p><strong>Name:</strong> ${escapeHtml(o.delivery_first_name)} ${escapeHtml(o.delivery_last_name)}</p>
                            ${o.delivery_method === 'delivery' ? `
                                <p><strong>Adresse:</strong> ${escapeHtml(o.delivery_street)}</p>
                                <p><strong>Ort:</strong> ${escapeHtml(o.delivery_postal_code)} ${escapeHtml(o.delivery_city)}</p>
                            ` : '<p><strong>Abholung in der Filiale</strong></p>'}
                            <p><strong>Telefon:</strong> ${escapeHtml(o.delivery_phone)}</p>
                            <p><strong>E-Mail:</strong> ${escapeHtml(o.delivery_email)}</p>
                            <p><strong>Methode:</strong> ${deliveryMethodLabel}</p>
                        </div>
                    </div>

                    <div class="detail-section">
                        <h3 style="margin-top: 0; color: var(--primary-color); border-bottom: 2px solid #e0e0e0; padding-bottom: 0.5rem;">Bestellte Artikel</h3>
                        ${itemsHtml}
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <button onclick="changeOrderStatus(${o.id}, '${o.order_status}')" class="btn btn-primary">Status ändern</button>
                        <button onclick="deleteOrder(${o.id}, '${o.order_number}')" class="btn btn-danger">Bestellung löschen</button>
                    </div>
                </div>
            `;

            showAdminModal('Bestelldetails #' + o.order_number, modalHtml);
        })
        .catch(e => {
            console.error(e);
            alert('Fehler beim Laden der Bestelldetails');
        });
}

function changeOrderStatus(orderId, currentStatus) {
    const modalHtml = `
        <div style="padding: 1rem;">
            <p style="margin-bottom: 1rem;">Wähle den neuen Bestellstatus:</p>
            <select id="new-order-status" class="form-control" style="width: 100%; padding: 0.5rem; font-size: 1rem; margin-bottom: 1.5rem;">
                <option value="pending" ${currentStatus === 'pending' ? 'selected' : ''}>Ausstehend</option>
                <option value="processing" ${currentStatus === 'processing' ? 'selected' : ''}>In Bearbeitung</option>
                <option value="shipped" ${currentStatus === 'shipped' ? 'selected' : ''}>Versandt</option>
                <option value="delivered" ${currentStatus === 'delivered' ? 'selected' : ''}>Zugestellt</option>
                <option value="cancelled" ${currentStatus === 'cancelled' ? 'selected' : ''}>Storniert</option>
            </select>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button onclick="closeAdminModal()" class="btn btn-secondary">Abbrechen</button>
                <button onclick="submitStatusChange(${orderId})" class="btn btn-primary">Status aktualisieren</button>
            </div>
        </div>
    `;

    showAdminModal('Bestellstatus ändern', modalHtml);
}

function submitStatusChange(orderId) {
    const newStatus = document.getElementById('new-order-status').value;

    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('id', orderId);
    formData.append('status', newStatus);

    fetch('api/orders.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showNotification('Status aktualisiert', 'success');
            closeAdminModal();
            loadOrders(currentOrderFilter);
        } else {
            showNotification('Fehler: ' + d.error, 'error');
        }
    })
    .catch(e => {
        console.error(e);
        showNotification('Fehler beim Aktualisieren', 'error');
    });
}

function deleteOrder(orderId, orderNumber) {
    if (!confirm(`Bestellung ${orderNumber} wirklich löschen?\n\nDiese Aktion kann nicht rückgängig gemacht werden!`)) {
        return;
    }

    const formData = new FormData();
    formData.append('action', 'delete_order');
    formData.append('order_id', orderId);

    fetch('api/orders.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            showNotification('Bestellung gelöscht', 'success');
            closeAdminModal();
            loadOrders(currentOrderFilter);
        } else {
            showNotification('Fehler: ' + d.error, 'error');
        }
    })
    .catch(e => {
        console.error(e);
        showNotification('Fehler beim Löschen', 'error');
    });
}

// Admin modal helper functions
function showAdminModal(title, content) {
    // Remove existing modal if any
    const existing = document.getElementById('admin-modal-backdrop');
    if (existing) existing.remove();

    const backdrop = document.createElement('div');
    backdrop.id = 'admin-modal-backdrop';
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML = `
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <h3 style="margin: 0;">${title}</h3>
                <button onclick="closeAdminModal()" class="modal-close" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #999;">&times;</button>
            </div>
            <div class="modal-body">
                ${content}
            </div>
        </div>
    `;

    backdrop.addEventListener('click', function(e) {
        if (e.target === backdrop) closeAdminModal();
    });

    document.body.appendChild(backdrop);
}

function closeAdminModal() {
    const modal = document.getElementById('admin-modal-backdrop');
    if (modal) modal.remove();
}

// Filter tabs
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        loadOrders(this.dataset.status);
    });
});

// Load data on tab change
if (window.location.search.includes('tab=coupons')) {
    loadCoupons();
} else if (window.location.search.includes('tab=orders')) {
    loadOrders();
}
</script>

<style>
.filter-tabs {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 0.5rem 1rem;
    background: #f0f0f0;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s;
}

.filter-tab.active {
    background: var(--primary-color);
    color: white;
}

.filter-tab:hover:not(.active) {
    background: #e0e0e0;
}

.badge {
    padding: 0.3rem 0.6rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-success { background: #d4edda; color: #155724; }
.badge-warning { background: #fff3cd; color: #856404; }
.badge-info { background: #d1ecf1; color: #0c5460; }
.badge-primary { background: #cfe2ff; color: #084298; }
.badge-danger { background: #f8d7da; color: #721c24; }
.badge-secondary { background: #e0e0e0; color: #666; }

<style>
.badge-type-wine {
    background: #722c2c;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    display: inline-block;
}

.badge-type-event {
    background: #2196F3;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    display: inline-block;
}

.badge-type-general {
    background: #4CAF50;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    display: inline-block;
}

/* Admin Order Modal Styles */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.75);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 100000;
    padding: 2rem;
    overflow-y: auto;
}

.modal-content {
    background: white;
    border-radius: 15px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 1000px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    padding: 2rem;
    border-radius: 15px 15px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: white;
    font-size: 1.5rem;
}

.modal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 1.8rem;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
}

.modal-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modal-body {
    padding: 2rem;
}

.btn-icon {
    background: transparent;
    border: 1px solid #ddd;
    padding: 0.5rem 0.8rem;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s;
}

.btn-icon:hover {
    background: #f5f5f5;
    border-color: var(--primary-color);
    transform: scale(1.1);
}

.btn-icon.btn-danger {
    border-color: #c62828;
    color: #c62828;
}

.btn-icon.btn-danger:hover {
    background: #ffebee;
}

.badge {
    padding: 0.4rem 0.8rem;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.badge-warning {
    background: #fff3cd;
    color: #856404;
}

.badge-info {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-primary {
    background: #cce5ff;
    color: #004085;
}

.badge-success {
    background: #d4edda;
    color: #155724;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.table-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
}

.form-control {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
}

@media (max-width: 768px) {
    .modal-backdrop {
        padding: 1rem;
    }

    .modal-content {
        max-height: 95vh;
    }

    .modal-header {
        padding: 1.5rem;
    }

    .modal-body {
        padding: 1.5rem;
    }
}

/* Notification Toast */
.admin-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 100001;
    min-width: 300px;
    max-width: 500px;
    padding: 1.2rem 1.5rem;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    transform: translateX(150%);
    transition: transform 0.3s ease;
}

.admin-notification.show {
    transform: translateX(0);
}

.admin-notification-success {
    background: linear-gradient(135deg, #27ae60, #229954);
    color: white;
}

.admin-notification-error {
    background: linear-gradient(135deg, #c62828, #b71c1c);
    color: white;
}

.admin-notification-info {
    background: linear-gradient(135deg, #2196F3, #1976D2);
    color: white;
}

.notification-content {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.notification-icon {
    font-size: 1.5rem;
    font-weight: bold;
}

.notification-message {
    flex: 1;
    font-size: 1rem;
}
</style>