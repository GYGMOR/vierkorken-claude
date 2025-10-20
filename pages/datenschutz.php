<?php
// pages/datenschutz.php - Datenschutzerklärung Seite (editierbar)

$datenschutz_title = get_setting('datenschutz_title', 'Datenschutzerklärung');
$datenschutz_content = get_setting('datenschutz_content', 'Hier kommt die Datenschutzerklärung hin...');
?>

<div class="datenschutz-page">
    <div class="container">
        <h1><?php echo safe_output($datenschutz_title); ?></h1>
        
        <div class="datenschutz-content">
            <div class="datenschutz-main-text">
                <?php echo $datenschutz_content; ?>
            </div>
        </div>
        
        <div class="datenschutz-footer">
            <p style="text-align: center; color: var(--text-light); margin-top: 2rem;">
                <a href="?page=impressum">Impressum</a> | 
                <a href="?page=agb">Allgemeine Geschäftsbedingungen</a>
            </p>
        </div>
    </div>
</div>

<style>
.datenschutz-page {
    background: white;
    padding: 3rem 0;
}

.datenschutz-page h1 {
    margin-bottom: 2rem;
}

.datenschutz-content {
    max-width: 900px;
    margin: 0 auto;
}

.datenschutz-main-text {
    line-height: 1.8;
    color: var(--text-light);
}

.datenschutz-main-text p {
    margin-bottom: 1rem;
}

.datenschutz-main-text h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: var(--primary-color);
    font-size: 1.5rem;
    border: none;
}

.datenschutz-main-text h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.8rem;
    color: var(--primary-color);
    font-size: 1.2rem;
    border: none;
}

.datenschutz-main-text ol,
.datenschutz-main-text ul {
    margin-left: 2rem;
    margin-bottom: 1rem;
}

.datenschutz-main-text li {
    margin-bottom: 0.5rem;
}

.datenschutz-main-text strong {
    color: var(--primary-color);
}

@media (max-width: 768px) {
    .datenschutz-page {
        padding: 2rem 0;
    }
    
    .datenschutz-page h1 {
        font-size: 1.8rem;
    }
}
</style>