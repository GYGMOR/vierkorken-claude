
<?php
$agb_title = get_setting('agb_title', 'Allgemeine Geschäftsbedingungen');
$agb_content = get_setting('agb_content', 'Hier kommen die AGB hin...');
?>

<div class="agb-page">
    <div class="container">
        <h1><?php editable('agb_title', $agb_title, 'span'); ?></h1>
        
        <div class="agb-content">
            <div class="agb-main-text">
                <?php echo $agb_content; ?>
            </div>
        </div>
        
        <div class="agb-footer">
            <p style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                <a href="?page=datenschutz">Datenschutzerklärung</a> | 
                <a href="?page=impressum">Impressum</a>
            </p>
        </div>
    </div>
</div>

<style>
.agb-page {
    background: white;
    padding: 3rem 0;
}

.agb-page h1 {
    margin-bottom: 2rem;
}

.agb-content {
    max-width: 900px;
    margin: 0 auto;
}

.agb-main-text {
    line-height: 1.8;
    color: var(--text-light);
}

.agb-main-text p {
    margin-bottom: 1rem;
}

.agb-main-text h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
    font-size: 1.5rem;
    border: none;
}

.agb-main-text h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.8rem;
    color: var(--primary-color);
    font-size: 1.2rem;
    border: none;
}

.agb-main-text ol,
.agb-main-text ul {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.agb-main-text li {
    margin-bottom: 0.5rem;
}
</style>
