<?php
// Lokal alle Kantone-SVGs erstellen

$kantone = [
    'AG' => '#000000', 'AI' => '#000000', 'AR' => '#000000', 'BE' => '#DC1E29',
    'BL' => '#000000', 'BS' => '#000000', 'FR' => '#000000', 'GE' => '#F4C022',
    'GL' => '#DC1E29', 'GR' => '#000000', 'JU' => '#DC1E29', 'LU' => '#0D4C92',
    'NE' => '#009246', 'NW' => '#DC1E29', 'OW' => '#DC1E29', 'SG' => '#009246',
    'SH' => '#F4C022', 'SO' => '#DC1E29', 'SZ' => '#DC1E29', 'TG' => '#009246',
    'TI' => '#DC1E29', 'UR' => '#F4C022', 'VD' => '#009246', 'VS' => '#DC1E29',
    'ZG' => '#0D4C92', 'ZH' => '#0D4C92'
];

$target_dir = __DIR__ . '/assets/images/kantone/';
if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);

foreach ($kantone as $code => $color) {
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <rect width="100" height="100" fill="#DC1E29"/>
  <rect x="35" y="15" width="30" height="70" fill="white"/>
  <rect x="15" y="35" width="70" height="30" fill="white"/>
  <text x="50" y="60" font-family="Arial, sans-serif" font-size="28" font-weight="bold" fill="{$color}" text-anchor="middle" stroke="white" stroke-width="0.5">{$code}</text>
</svg>
SVG;
    file_put_contents($target_dir . $code . '.svg', $svg);
}

echo "✓ Alle 26 Kantone erstellt!";
?>
