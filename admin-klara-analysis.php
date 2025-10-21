<?php
/**
 * Klara Excel Analysis - Admin Tool
 * Access: Upload to server and visit directly
 */

session_start();
require_once 'config/database.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("<h1>Access Denied</h1><p>Admin access required.</p>");
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Klara Excel Analysis</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background: #4c254c; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background: #f9f9f9; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .error { background: #ffebee; padding: 15px; border-radius: 5px; margin: 15px 0; color: #c62828; }
        .success { background: #e8f5e9; padding: 15px; border-radius: 5px; margin: 15px 0; color: #2e7d32; }
        .scroll-table { overflow-x: auto; max-height: 600px; overflow-y: auto; }
        h2 { color: #4c254c; border-bottom: 2px solid #4c254c; padding-bottom: 10px; }
    </style>
</head>
<body>
<div class='container'>

<h1>Klara Excel Data Analysis</h1>

<?php

/**
 * Read XLSX file using PHP's ZIP extension
 */
function readXLSX($filepath) {
    if (!file_exists($filepath)) {
        return ['error' => 'File not found: ' . $filepath];
    }

    $zip = new ZipArchive();
    if ($zip->open($filepath) !== TRUE) {
        return ['error' => 'Could not open XLSX file'];
    }

    // Read the sheet data
    $sharedStringsXML = $zip->getFromName('xl/sharedStrings.xml');
    $worksheetXML = $zip->getFromName('xl/worksheets/sheet1.xml');

    $zip->close();

    if (!$worksheetXML) {
        return ['error' => 'Could not read worksheet data'];
    }

    // Parse shared strings
    $sharedStrings = [];
    if ($sharedStringsXML) {
        $stringsData = simplexml_load_string($sharedStringsXML);
        foreach ($stringsData->si as $val) {
            $sharedStrings[] = (string)$val->t;
        }
    }

    // Parse worksheet
    $worksheet = simplexml_load_string($worksheetXML);
    $rows = [];

    foreach ($worksheet->sheetData->row as $row) {
        $rowData = [];
        foreach ($row->c as $cell) {
            $cellValue = '';

            if (isset($cell['t']) && $cell['t'] == 's') {
                $index = (int)$cell->v;
                $cellValue = isset($sharedStrings[$index]) ? $sharedStrings[$index] : '';
            } else {
                $cellValue = (string)$cell->v;
            }

            $rowData[] = $cellValue;
        }
        $rows[] = $rowData;
    }

    return ['rows' => $rows];
}

// Analyze Artikel Export
$artikelFile = __DIR__ . '/database/Artikel_Export.xlsx';
echo "<h2>📊 Artikel Export (Klara POS)</h2>";

if (file_exists($artikelFile)) {
    echo "<div class='info'>Analyzing: " . basename($artikelFile) . "</div>";

    $data = readXLSX($artikelFile);

    if (isset($data['error'])) {
        echo "<div class='error'>" . $data['error'] . "</div>";
    } else {
        $rows = $data['rows'];
        $headers = $rows[0] ?? [];
        $dataRows = array_slice($rows, 1);

        echo "<div class='success'>";
        echo "<strong>✓ Total Rows:</strong> " . count($dataRows) . "<br>";
        echo "<strong>✓ Total Columns:</strong> " . count($headers) . "<br>";
        echo "</div>";

        echo "<h3>Column Headers:</h3>";
        echo "<ol style='column-count: 2;'>";
        foreach ($headers as $i => $header) {
            echo "<li><strong>Column " . ($i+1) . ":</strong> " . htmlspecialchars($header) . "</li>";
        }
        echo "</ol>";

        echo "<h3>Sample Data (First 20 rows):</h3>";
        echo "<div class='scroll-table'>";
        echo "<table>";
        echo "<thead><tr>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";

        $sampleRows = array_slice($dataRows, 0, 20);
        foreach ($sampleRows as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";

        // Save to JSON
        $analysis = [
            'file' => 'Artikel_Export.xlsx',
            'total_rows' => count($dataRows),
            'columns' => $headers,
            'sample_data' => $sampleRows
        ];
        file_put_contents(__DIR__ . '/database/artikel_analysis.json', json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "<div class='success'>✓ Analysis saved to: database/artikel_analysis.json</div>";
    }
} else {
    echo "<div class='error'>File not found: $artikelFile</div>";
}

echo "<hr>";

// Analyze Delivery Export
$deliveryFile = __DIR__ . '/database/Delivery_Export_from_1_to_43.xlsx';
echo "<h2>📦 Delivery Export</h2>";

if (file_exists($deliveryFile)) {
    echo "<div class='info'>Analyzing: " . basename($deliveryFile) . "</div>";

    $data = readXLSX($deliveryFile);

    if (isset($data['error'])) {
        echo "<div class='error'>" . $data['error'] . "</div>";
    } else {
        $rows = $data['rows'];
        $headers = $rows[0] ?? [];
        $dataRows = array_slice($rows, 1);

        echo "<div class='success'>";
        echo "<strong>✓ Total Rows:</strong> " . count($dataRows) . "<br>";
        echo "<strong>✓ Total Columns:</strong> " . count($headers) . "<br>";
        echo "</div>";

        echo "<h3>Column Headers:</h3>";
        echo "<ol style='column-count: 2;'>";
        foreach ($headers as $i => $header) {
            echo "<li><strong>Column " . ($i+1) . ":</strong> " . htmlspecialchars($header) . "</li>";
        }
        echo "</ol>";

        echo "<h3>Sample Data (First 20 rows):</h3>";
        echo "<div class='scroll-table'>";
        echo "<table>";
        echo "<thead><tr>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";

        $sampleRows = array_slice($dataRows, 0, 20);
        foreach ($sampleRows as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";

        // Save to JSON
        $analysis = [
            'file' => 'Delivery_Export_from_1_to_43.xlsx',
            'total_rows' => count($dataRows),
            'columns' => $headers,
            'sample_data' => $sampleRows
        ];
        file_put_contents(__DIR__ . '/database/delivery_analysis.json', json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "<div class='success'>✓ Analysis saved to: database/delivery_analysis.json</div>";
    }
} else {
    echo "<div class='error'>File not found: $deliveryFile</div>";
}

echo "<hr>";
echo "<h2>🎯 Next Steps</h2>";
echo "<ol>";
echo "<li>Review the column structures above</li>";
echo "<li>Check the JSON files in database/ folder for detailed analysis</li>";
echo "<li>Identify column mapping for import script</li>";
echo "</ol>";

echo "<p><a href='?page=admin-dashboard'>← Back to Admin Dashboard</a></p>";

echo "</div>";
?>

</body>
</html>
