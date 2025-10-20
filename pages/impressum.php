
<?php
$impressum_title = get_setting('impressum_title', 'Impressum');
$impressum_content = get_setting('impressum_content', 'Hier kommt das Impressum hin...');
$impressum_company = get_setting('impressum_company', 'Vier Korken GmbH');
$impressum_address = get_setting('impressum_address', 'Weinstrasse 123, 8000 Zürich');
$impressum_phone = get_setting('impressum_phone', '+41 43 123 45 67');
$impressum_email = get_setting('impressum_email', 'info@vierkorken.ch');
?>

<div class="impressum-page">
    <div class="container">
        <h1><?php editable('impressum_title', $impressum_title, 'span'); ?></h1>
        
        <div class="impressum-content">
            <div class="impressum-text">
                <div class="impressum-main-text">
                    <?php echo nl2br(safe_output(get_setting('impressum_content', ''))); ?>
                </div>
            </div>
            
            <div class="impressum-info">
                <h3>Kontaktinformationen</h3>
                
                <div class="info-block">
                    <h4>Unternehmen</h4>
                    <p><?php echo safe_output($impressum_company); ?></p>
                </div>
                
                <div class="info-block">
                    <h4>Adresse</h4>
                    <p><?php echo safe_output($impressum_address); ?></p>
                </div>
                
                <div class="info-block">
                    <h4>Telefon</h4>
                    <p><a href="tel:<?php echo urlencode($impressum_phone); ?>"><?php echo safe_output($impressum_phone); ?></a></p>
                </div>
                
                <div class="info-block">
                    <h4>E-Mail</h4>
                    <p><a href="mailto:<?php echo safe_output($impressum_email); ?>"><?php echo safe_output($impressum_email); ?></a></p>
                </div>
            </div>
        </div>
        
        <div class="impressum-footer">
            <p style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                <a href="?page=datenschutz">Datenschutzerklärung</a> | 
                <a href="?page=agb">Allgemeine Geschäftsbedingungen</a>
            </p>
        </div>
    </div>
</div>

<style>
.impressum-page {
    background: white;
    padding: 3rem 0;
}

.impressum-page h1 {
    margin-bottom: 2rem;
}

.impressum-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    margin-bottom: 3rem;
}

.impressum-main-text {
    line-height: 1.8;
    color: var(--text-light);
}

.impressum-main-text p {
    margin-bottom: 1rem;
}

.impressum-info {
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    padding: 2rem;
    border-radius: 10px;
    height: fit-content;
}

.impressum-info h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.info-block {
    margin-bottom: 1.5rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
}

.info-block:last-child {
    border: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.info-block h4 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
    font-size: 1rem;
}

.info-block p {
    margin: 0;
    color: var(--text-light);
}

.info-block a {
    word-break: break-word;
}

@media (max-width: 768px) {
    .impressum-content {
        grid-template-columns: 1fr;
    }
}
</style>