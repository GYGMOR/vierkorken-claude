<?php
// includes/kantone.php - Schweizer Kantone mit Wappen

// Alle 26 Schweizer Kantone
function get_schweizer_kantone() {
    return [
        'AG' => 'Aargau',
        'AI' => 'Appenzell Innerrhoden',
        'AR' => 'Appenzell Ausserrhoden',
        'BE' => 'Bern',
        'BL' => 'Basel-Landschaft',
        'BS' => 'Basel-Stadt',
        'FR' => 'Freiburg',
        'GE' => 'Genf',
        'GL' => 'Glarus',
        'GR' => 'Graubünden',
        'JU' => 'Jura',
        'LU' => 'Luzern',
        'NE' => 'Neuenburg',
        'NW' => 'Nidwalden',
        'OW' => 'Obwalden',
        'SG' => 'St. Gallen',
        'SH' => 'Schaffhausen',
        'SO' => 'Solothurn',
        'SZ' => 'Schwyz',
        'TG' => 'Thurgau',
        'TI' => 'Tessin',
        'UR' => 'Uri',
        'VD' => 'Waadt',
        'VS' => 'Wallis',
        'ZG' => 'Zug',
        'ZH' => 'Zürich'
    ];
}

// Wappen-URL für einen Kanton
function get_kanton_wappen_url($kanton_code) {
    if (empty($kanton_code)) {
        return null;
    }

    // Wappen-Bilder liegen in assets/images/kantone/
    // Dateinamen: ZH.svg, BE.svg, etc.
    $kanton_code = strtoupper($kanton_code);
    return "assets/images/kantone/{$kanton_code}.svg";
}

// Wappen-HTML für Anzeige
function render_kanton_wappen($kanton_code, $size = 40) {
    if (empty($kanton_code)) {
        return '';
    }

    $kantone = get_schweizer_kantone();
    $kanton_name = $kantone[$kanton_code] ?? $kanton_code;
    $wappen_url = get_kanton_wappen_url($kanton_code);

    return sprintf(
        '<img src="%s" alt="Wappen %s" title="Kanton %s" class="kanton-wappen" style="width:%dpx;height:%dpx;object-fit:contain;" onerror="this.style.display=\'none\'">',
        htmlspecialchars($wappen_url),
        htmlspecialchars($kanton_name),
        htmlspecialchars($kanton_name),
        $size,
        $size
    );
}
?>
