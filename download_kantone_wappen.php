<?php
// Download Script für Schweizer Kantonswappen von Wikimedia Commons
// Führe dieses Script einmalig aus: php download_kantone_wappen.php

echo "Lade Kantonswappen herunter...\n\n";

// Wikimedia Commons URLs für alle 26 Kantone (offizielle Wappen)
$wappen_urls = [
    'AG' => 'https://upload.wikimedia.org/wikipedia/commons/4/49/Wappen_Aargau_matt.svg',
    'AI' => 'https://upload.wikimedia.org/wikipedia/commons/f/f2/Wappen_Appenzell_Innerrhoden_matt.svg',
    'AR' => 'https://upload.wikimedia.org/wikipedia/commons/8/8d/Wappen_Appenzell_Ausserrhoden_matt.svg',
    'BE' => 'https://upload.wikimedia.org/wikipedia/commons/f/f1/Wappen_Bern_matt.svg',
    'BL' => 'https://upload.wikimedia.org/wikipedia/commons/3/36/Wappen_Basel-Landschaft_matt.svg',
    'BS' => 'https://upload.wikimedia.org/wikipedia/commons/d/df/Wappen_Basel-Stadt_matt.svg',
    'FR' => 'https://upload.wikimedia.org/wikipedia/commons/d/de/Wappen_Freiburg_matt.svg',
    'GE' => 'https://upload.wikimedia.org/wikipedia/commons/b/b2/Wappen_Genf_matt.svg',
    'GL' => 'https://upload.wikimedia.org/wikipedia/commons/8/89/Wappen_Glarus_matt.svg',
    'GR' => 'https://upload.wikimedia.org/wikipedia/commons/2/20/Wappen_Graub%C3%BCnden_matt.svg',
    'JU' => 'https://upload.wikimedia.org/wikipedia/commons/e/ee/Wappen_Jura_matt.svg',
    'LU' => 'https://upload.wikimedia.org/wikipedia/commons/4/4f/Wappen_Luzern_matt.svg',
    'NE' => 'https://upload.wikimedia.org/wikipedia/commons/b/b8/Wappen_Neuenburg_matt.svg',
    'NW' => 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Wappen_Nidwalden_matt.svg',
    'OW' => 'https://upload.wikimedia.org/wikipedia/commons/6/6d/Wappen_Obwalden_matt.svg',
    'SG' => 'https://upload.wikimedia.org/wikipedia/commons/f/f5/Wappen_St._Gallen_matt.svg',
    'SH' => 'https://upload.wikimedia.org/wikipedia/commons/6/64/Wappen_Schaffhausen_matt.svg',
    'SO' => 'https://upload.wikimedia.org/wikipedia/commons/c/c9/Wappen_Solothurn_matt.svg',
    'SZ' => 'https://upload.wikimedia.org/wikipedia/commons/e/e5/Wappen_Schwyz_matt.svg',
    'TG' => 'https://upload.wikimedia.org/wikipedia/commons/c/c2/Wappen_Thurgau_matt.svg',
    'TI' => 'https://upload.wikimedia.org/wikipedia/commons/9/9d/Wappen_Tessin_matt.svg',
    'UR' => 'https://upload.wikimedia.org/wikipedia/commons/a/a6/Wappen_Uri_matt.svg',
    'VD' => 'https://upload.wikimedia.org/wikipedia/commons/c/c4/Wappen_Waadt_matt.svg',
    'VS' => 'https://upload.wikimedia.org/wikipedia/commons/8/8c/Wappen_Wallis_matt.svg',
    'ZG' => 'https://upload.wikimedia.org/wikipedia/commons/b/bb/Wappen_Zug_matt.svg',
    'ZH' => 'https://upload.wikimedia.org/wikipedia/commons/5/5a/Wappen_Z%C3%BCrich_matt.svg'
];

$target_dir = __DIR__ . '/assets/images/kantone/';

// Stelle sicher dass Ordner existiert
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0755, true);
    echo "Ordner erstellt: $target_dir\n";
}

$success = 0;
$failed = 0;

foreach ($wappen_urls as $kanton => $url) {
    $target_file = $target_dir . $kanton . '.svg';

    echo "Lade $kanton... ";

    $content = @file_get_contents($url);

    if ($content !== false) {
        if (file_put_contents($target_file, $content)) {
            echo "✓ OK\n";
            $success++;
        } else {
            echo "✗ FEHLER beim Schreiben\n";
            $failed++;
        }
    } else {
        echo "✗ FEHLER beim Download\n";
        $failed++;
    }

    // Kurze Pause um Server nicht zu überlasten
    usleep(200000); // 0.2 Sekunden
}

echo "\n";
echo "========================================\n";
echo "Fertig!\n";
echo "Erfolgreich: $success Wappen\n";
echo "Fehlgeschlagen: $failed Wappen\n";
echo "========================================\n";
echo "\nWappen gespeichert in: $target_dir\n";
?>
