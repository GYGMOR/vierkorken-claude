
<?php
$contact_title = get_setting('contact_title', 'Kontakt');
$contact_intro = get_setting('contact_intro', 'Haben Sie Fragen zu unseren Weinen oder möchten Sie eine Bestellung aufgeben? Wir freuen uns auf Ihre Nachricht!');
$contact_company = get_setting('impressum_company', 'Vier Korken GmbH');
$contact_address = get_setting('impressum_address', 'Weinstrasse 123, 8000 Zürich');
$contact_phone = get_setting('impressum_phone', '+41 43 123 45 67');
$contact_email = get_setting('impressum_email', 'info@vierkorken.ch');

// Handle form submission
$form_success = false;
$form_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Simple validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $form_error = 'Bitte füllen Sie alle Felder aus.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_error = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    } else {
        // Send email (configure mail settings as needed)
        $to = $contact_email;
        $email_subject = "Kontaktanfrage: " . $subject;
        $email_body = "Name: $name\n";
        $email_body .= "E-Mail: $email\n\n";
        $email_body .= "Nachricht:\n$message";
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";

        if (mail($to, $email_subject, $email_body, $headers)) {
            $form_success = true;
        } else {
            $form_error = 'Entschuldigung, es gab ein Problem beim Senden Ihrer Nachricht. Bitte versuchen Sie es später erneut.';
        }
    }
}
?>

<div class="contact-page">
    <div class="container">
        <h1><?php editable('contact_title', $contact_title, 'span'); ?></h1>

        <div class="contact-intro">
            <p><?php echo nl2br(safe_output($contact_intro)); ?></p>
        </div>

        <div class="contact-content">
            <!-- Contact Form -->
            <div class="contact-form-wrapper">
                <h2>Kontaktformular</h2>

                <?php if ($form_success): ?>
                    <div class="alert alert-success">
                        Vielen Dank für Ihre Nachricht! Wir werden uns so schnell wie möglich bei Ihnen melden.
                    </div>
                <?php endif; ?>

                <?php if ($form_error): ?>
                    <div class="alert alert-error">
                        <?php echo safe_output($form_error); ?>
                    </div>
                <?php endif; ?>

                <?php if (!$form_success): ?>
                <form method="POST" class="contact-form">
                    <div class="form-group">
                        <label for="name">Name *</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo isset($_POST['name']) ? safe_output($_POST['name']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">E-Mail *</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? safe_output($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="subject">Betreff *</label>
                        <input type="text" id="subject" name="subject" required
                               value="<?php echo isset($_POST['subject']) ? safe_output($_POST['subject']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="message">Nachricht *</label>
                        <textarea id="message" name="message" rows="6" required><?php echo isset($_POST['message']) ? safe_output($_POST['message']) : ''; ?></textarea>
                    </div>

                    <button type="submit" name="contact_submit" class="btn btn-primary">Nachricht senden</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Kontaktinformationen</h2>

                <div class="info-block">
                    <div class="info-icon">
                        <?php echo get_icon('location', 24); ?>
                    </div>
                    <div class="info-content">
                        <h4>Adresse</h4>
                        <p><?php echo nl2br(safe_output($contact_address)); ?></p>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-icon">
                        <?php echo get_icon('phone', 24); ?>
                    </div>
                    <div class="info-content">
                        <h4>Telefon</h4>
                        <p><a href="tel:<?php echo urlencode($contact_phone); ?>"><?php echo safe_output($contact_phone); ?></a></p>
                    </div>
                </div>

                <div class="info-block">
                    <div class="info-icon">
                        <?php echo get_icon('email', 24); ?>
                    </div>
                    <div class="info-content">
                        <h4>E-Mail</h4>
                        <p><a href="mailto:<?php echo safe_output($contact_email); ?>"><?php echo safe_output($contact_email); ?></a></p>
                    </div>
                </div>

                <div class="opening-hours">
                    <h4>Öffnungszeiten</h4>
                    <p>Montag - Freitag: 9:00 - 18:00 Uhr<br>
                       Samstag: 10:00 - 16:00 Uhr<br>
                       Sonntag: Geschlossen</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.contact-page {
    background: white;
    padding: 3rem 0;
    min-height: 60vh;
}

.contact-page h1 {
    margin-bottom: 1rem;
    color: var(--primary-color);
}

.contact-intro {
    margin-bottom: 3rem;
    color: var(--text-light);
    font-size: 1.1rem;
}

.contact-content {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 4rem;
}

.contact-form-wrapper h2,
.contact-info h2 {
    color: var(--primary-color);
    margin-bottom: 1.5rem;
    font-size: 1.5rem;
}

/* Alert messages */
.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

/* Contact Form */
.contact-form {
    background: var(--bg-light);
    padding: 2rem;
    border-radius: 10px;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text-dark);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 1rem;
    font-family: inherit;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.btn-primary {
    background: var(--primary-color);
    color: white;
    padding: 1rem 2rem;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-primary:hover {
    background: var(--primary-dark);
}

/* Contact Info */
.contact-info {
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    padding: 2rem;
    border-radius: 10px;
    height: fit-content;
}

.info-block {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border-color);
}

.info-block:last-of-type {
    border-bottom: none;
}

.info-icon {
    flex-shrink: 0;
    color: var(--primary-color);
}

.info-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
    font-size: 1rem;
}

.info-content p {
    margin: 0;
    color: var(--text-light);
}

.info-content a {
    color: var(--text-light);
    text-decoration: none;
}

.info-content a:hover {
    color: var(--primary-color);
}

.opening-hours {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid var(--border-color);
}

.opening-hours h4 {
    margin: 0 0 1rem 0;
    color: var(--primary-color);
}

.opening-hours p {
    margin: 0;
    color: var(--text-light);
    line-height: 1.8;
}

@media (max-width: 968px) {
    .contact-content {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
}

@media (max-width: 480px) {
    .contact-page {
        padding: 2rem 0;
    }

    .contact-form {
        padding: 1.5rem;
    }

    .contact-info {
        padding: 1.5rem;
    }
}
</style>
