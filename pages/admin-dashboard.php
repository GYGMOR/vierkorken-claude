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
        update_setting('color_primary', $_POST['color_primary']);
        update_setting('color_primary_dark', $_POST['color_primary_dark']);
        update_setting('color_accent_gold', $_POST['color_accent_gold']);
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
            <a href="?page=admin-dashboard&tab=wines" class="<?php echo $tab === 'wines' ? 'active' : ''; ?>">Weine verwalten</a>
            <a href="?page=admin-dashboard&tab=add-wine" class="<?php echo $tab === 'add-wine' ? 'active' : ''; ?>">Wein hinzufügen</a>
            
            <div class="nav-divider"></div>
            <a href="?page=home" class="nav-home">Zur Webseite</a>
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
                    <div class="stat-number"><?php echo $db->query("SELECT COUNT(*) as c FROM wines")->fetch_assoc()['c']; ?></div>
                    <div class="stat-label">Weine</div>
                </div>
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo $db->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc()['c']; ?></div>
                    <div class="stat-label">Bestellungen</div>
                </div>
                <div class="stat-card-admin">
                    <div class="stat-number"><?php echo $db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c']; ?></div>
                    <div class="stat-label">Benutzer</div>
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
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_colors">
                <div class="form-section-mega">
                    <h3>Website Farben</h3>
                    <p>Nach dem Speichern: Seite neu laden um Änderungen zu sehen.</p>
                    
                    <div class="colors-grid">
                        <div class="color-picker">
                            <label>Hauptfarbe</label>
                            <input type="color" name="color_primary" value="<?php echo $settings['color_primary'] ?? '#722c2c'; ?>">
                            <input type="text" value="<?php echo $settings['color_primary'] ?? '#722c2c'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Dunkle Hauptfarbe</label>
                            <input type="color" name="color_primary_dark" value="<?php echo $settings['color_primary_dark'] ?? '#561111'; ?>">
                            <input type="text" value="<?php echo $settings['color_primary_dark'] ?? '#561111'; ?>" readonly>
                        </div>
                        <div class="color-picker">
                            <label>Akzentfarbe (Gold)</label>
                            <input type="color" name="color_accent_gold" value="<?php echo $settings['color_accent_gold'] ?? '#d4a574'; ?>">
                            <input type="text" value="<?php echo $settings['color_accent_gold'] ?? '#d4a574'; ?>" readonly>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg">Speichern</button>
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
                Diese Weine werden auf der Startseite unter "News / Neuheiten" angezeigt.
            </div>

            <div class="admin-form-mega">
                <h3>Wein hinzufügen</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h4>Option 1: Existierenden Wein</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="toggle_featured_wine">
                            <div class="form-group-mega">
                                <label>Wein auswählen:</label>
                                <select name="wine_id" required>
                                    <option value="">-- Wählen --</option>
                                    <?php 
                                    $all_wines = $db->query("SELECT id, name, producer, is_featured FROM wines WHERE is_featured = 0 ORDER BY name ASC");
                                    while ($w = $all_wines->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $w['id']; ?>"><?php echo safe_output($w['name']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <input type="hidden" name="featured" value="1">
                            <button type="submit" class="btn btn-primary">Hinzufügen</button>
                        </form>
                    </div>

                    <div>
                        <h4>Option 2: Neuen Wein erstellen</h4>
                        <form method="POST">
                            <input type="hidden" name="action" value="create_featured_wine">
                            <div class="form-group-mega">
                                <label>Weinname</label>
                                <input type="text" name="wine_name" required>
                            </div>
                            <div class="form-group-mega">
                                <label>Produzent</label>
                                <input type="text" name="wine_producer">
                            </div>
                            <div class="form-group-mega">
                                <label>Kategorie</label>
                                <select name="category_id">
                                    <?php foreach (get_all_categories() as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo safe_output($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group-mega">
                                <label>Preis (CHF)</label>
                                <input type="number" name="wine_price" step="0.01" value="0.00">
                            </div>
                            <button type="submit" class="btn btn-success">Erstellen</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="admin-form-mega">
                <h3>Aktuelle Neuheiten</h3>
                <?php 
                $featured_wines = $db->query("SELECT w.id, w.name, w.producer, w.price, c.name as cat_name FROM wines w LEFT JOIN categories c ON w.category_id = c.id WHERE w.is_featured = 1 ORDER BY w.name");
                $count = $featured_wines->num_rows;
                ?>
                
                <?php if ($count > 0): ?>
                    <p>Anzahl: <?php echo $count; ?> Wein<?php echo $count !== 1 ? 'e' : ''; ?></p>
                    <table class="admin-table-mega">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Produzent</th>
                                <th>Kategorie</th>
                                <th>Preis</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($wine = $featured_wines->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo safe_output($wine['name']); ?></strong></td>
                                    <td><?php echo safe_output($wine['producer'] ?? '-'); ?></td>
                                    <td><?php echo safe_output($wine['cat_name']); ?></td>
                                    <td>CHF <?php echo number_format($wine['price'], 2); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_featured_wine">
                                            <input type="hidden" name="wine_id" value="<?php echo $wine['id']; ?>">
                                            <button type="submit" class="btn-small-admin danger">Entfernen</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-warning">Keine Neuheiten hinzugefügt.</div>
                <?php endif; ?>
            </div>

        <?php elseif ($tab === 'images'): ?>
            <form method="POST" class="admin-form-mega">
                <input type="hidden" name="action" value="update_images">

                <div class="form-section-mega">
                    <h3>Header Banner</h3>
                    <div class="form-group-mega">
                        <label>Banner Bild URL</label>
                        <input type="text" name="header_banner_image" value="<?php echo safe_output($settings['header_banner_image'] ?? ''); ?>" placeholder="z.B. assets/images/banner.jpg">
                        <small>Empfohlene Größe: 1920x600px</small>
                    </div>
                    <?php if (!empty($settings['header_banner_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo safe_output($settings['header_banner_image']); ?>" alt="Banner" style="max-width: 100%; height: auto; border-radius: 8px;">
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

.btn-small-admin.danger {
    background: #ff6b6b;
}

.btn-small-admin:hover {
    opacity: 0.8;
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
});
</script>