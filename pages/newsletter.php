<?php
// pages/newsletter.php - Newsletter Seite (editierbar)

$newsletter_title = get_setting('newsletter_title', 'Newsletter');
$newsletter_content = get_setting('newsletter_content', 'Abonniere unseren Newsletter und bleibe auf dem Laufenden.');

$message = '';
$message_type = '';

// Newsletter Anmeldung
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'subscribe') {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Ungültige E-Mail Adresse';
        $message_type = 'error';
    } else {
        $email_safe = $db->real_escape_string($email);
        
        // Überprüfe ob bereits angemeldet
        $check = $db->query("SELECT id FROM newsletter_subscribers WHERE email = '$email_safe'");
        
        if ($check->num_rows > 0) {
            // Wenn abgemeldet, wieder aktivieren
            $db->query("UPDATE newsletter_subscribers SET subscribed = 1, subscribed_at = NOW() WHERE email = '$email_safe'");
            $message = 'Du bist bereits angemeldet!';
            $message_type = 'warning';
        } else {
            // Neu anmelden
            if ($db->query("INSERT INTO newsletter_subscribers (email, subscribed) VALUES ('$email_safe', 1)")) {
                $message = 'Vielen Dank! Du hast dich erfolgreich angemeldet.';
                $message_type = 'success';
            } else {
                $message = 'Fehler beim Anmelden. Bitte versuche es später erneut.';
                $message_type = 'error';
            }
        }
    }
}
?>

<div class="newsletter-page">
    <div class="container">
        <h1><?php editable('newsletter_title', $newsletter_title, 'span'); ?></h1>
        
        <div class="newsletter-content">
            <div class="newsletter-text">
                <p><?php editable_textarea('newsletter_content', $newsletter_content, 'span', ['newsletter-main-text']); ?></p>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php echo safe_output($message); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="newsletter-form-page">
                    <input type="hidden" name="action" value="subscribe">
                    <div class="form-group">
                        <input type="email" name="email" placeholder="deine@email.ch" required>
                        <button type="submit" class="btn btn-primary">Anmelden</button>
                    </div>
                </form>
                
                <p class="newsletter-note">
                    Datenschutz: Wir teilen deine E-Mail nicht mit Dritten. 
                    <a href="?page=datenschutz">Datenschutzerklärung</a>
                </p>
            </div>
            
            <div class="newsletter-benefits">
                <h3>Was du erhältst:</h3>
                <ul>
                    <li>🍷 Wein-Empfehlungen & Neuheiten</li>
                    <li>🎁 Exklusive Angebote & Rabatte</li>
                    <li>📚 Tipps & Tricks rund um Wein</li>
                    <li>📅 Event-Ankündigungen</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
.newsletter-page {
    background: white;
    padding: 3rem 0;
}

.newsletter-page h1 {
    text-align: center;
    margin-bottom: 2rem;
}

.newsletter-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: center;
}

.newsletter-text {
    max-width: 500px;
}

.newsletter-main-text {
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.newsletter-form-page {
    display: flex;
    gap: 0.5rem;
    margin: 1.5rem 0;
}

.newsletter-form-page input {
    flex: 1;
    padding: 0.8rem;
    border: 2px solid var(--border-color);
    border-radius: 5px;
    font-size: 1rem;
}

.newsletter-form-page button {
    padding: 0.8rem 1.5rem;
}

.newsletter-note {
    font-size: 0.9rem;
    color: var(--text-light);
}

.newsletter-benefits {
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    padding: 2rem;
    border-radius: 10px;
}

.newsletter-benefits h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.newsletter-benefits ul {
    list-style: none;
    padding: 0;
}

.newsletter-benefits li {
    padding: 0.6rem 0;
    font-size: 1.05rem;
}

@media (max-width: 768px) {
    .newsletter-content {
        grid-template-columns: 1fr;
    }
    
    .newsletter-form-page {
        flex-direction: column;
    }
    
    .newsletter-form-page input,
    .newsletter-form-page button {
        width: 100%;
    }
}
</style>