<?php
// assets/css/dynamic-colors.php - Dynamische Farben
header("Content-type: text/css; charset: UTF-8");

require_once '../../config/database.php';
require_once '../../includes/functions.php';

$color_primary = get_setting('color_primary', '#722c2c');
$color_primary_dark = get_setting('color_primary_dark', '#561111');
$color_accent_gold = get_setting('color_accent_gold', '#d4a574');
?>

:root {
    --primary-color: <?php echo $color_primary; ?>;
    --primary-dark: <?php echo $color_primary_dark; ?>;
    --accent-gold: <?php echo $color_accent_gold; ?>;
}