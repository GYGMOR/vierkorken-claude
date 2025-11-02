<?php
// Erstelle einfache Kanton-Badges als SVG mit Kürzel
// Bis wir echte Wappen haben

echo "Erstelle Kanton-Badges...\n\n";

$kantone = [
    'AG' => ['name' => 'Aargau', 'color' => '#000000'],
    'AI' => ['name' => 'Appenzell I.', 'color' => '#000000'],
    'AR' => ['name' => 'Appenzell A.', 'color' => '#000000'],
    'BE' => ['name' => 'Bern', 'color' => '#DC1E29'],
    'BL' => ['name' => 'Basel-Land', 'color' => '#FFFFFF'],
    'BS' => ['name' => 'Basel-Stadt', 'color' => '#000000'],
    'FR' => ['name' => 'Freiburg', 'color' => '#000000'],
    'GE' => ['name' => 'Genf', 'color' => '#F4C022'],
    'GL' => ['name' => 'Glarus', 'color' => '#DC1E29'],
    'GR' => ['name' => 'Graubünden', 'color' => '#000000'],
    'JU' => ['name' => 'Jura', 'color' => '#DC1E29'],
    'LU' => ['name' => 'Luzern', 'color' => '#0D4C92'],
    'NE' => ['name' => 'Neuenburg', 'color' => '#009246'],
    'NW' => ['name' => 'Nidwalden', 'color' => '#DC1E29'],
    'OW' => ['name' => 'Obwalden', 'color' => '#DC1E29'],
    'SG' => ['name' => 'St. Gallen', 'color' => '#009246'],
    'SH' => ['name' => 'Schaffhausen', 'color' => '#F4C022'],
    'SO' => ['name' => 'Solothurn', 'color' => '#DC1E29'],
    'SZ' => ['name' => 'Schwyz', 'color' => '#DC1E29'],
    'TG' => ['name' => 'Thurgau', 'color' => '#009246'],
    'TI' => ['name' => 'Tessin', 'color' => '#DC1E29'],
    'UR' => ['name' => 'Uri', 'color' => '#F4C022'],
    'VD' => ['name' => 'Waadt', 'color' => '#009246'],
    'VS' => ['name' => 'Wallis', 'color' => '#DC1E29'],
    'ZG' => ['name' => 'Zug', 'color' => '#0D4C92'],
    'ZH' => ['name' => 'Zürich', 'color' => '#0D4C92']
];

$target_dir = __DIR__ . '/assets/images/kantone/';

if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
}

$success = 0;

foreach ($kantone as $code => $info) {
    $color = $info['color'];

    // Einfaches SVG Badge mit Kanton-Kürzel
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="100" height="100" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
  <!-- Schweizer Kreuz Hintergrund -->
  <rect width="100" height="100" fill="#DC1E29"/>

  <!-- Weißes Kreuz -->
  <rect x="35" y="15" width="30" height="70" fill="white"/>
  <rect x="15" y="35" width="70" height="30" fill="white"/>

  <!-- Kanton-Kürzel -->
  <text x="50" y="60" font-family="Arial, sans-serif" font-size="28" font-weight="bold" fill="{$color}" text-anchor="middle" stroke="white" stroke-width="0.5">{$code}</text>
</svg>
SVG;

    $filename = $target_dir . $code . '.svg';

    if (file_put_contents($filename, $svg)) {
        echo "✓ $code ({$info['name']})\n";
        $success++;
    } else {
        echo "✗ $code FEHLER\n";
    }
}

echo "\n========================================\n";
echo "Fertig!\n";
echo "Erfolgreich: $success Badges erstellt\n";
echo "========================================\n";
?>
