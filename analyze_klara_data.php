<?php
/**
 * Klara POS Data Analysis Script
 * Analyzes Excel files from Klara POS system
 *
 * Usage: Run this file in browser or via CLI
 */

require_once 'vendor/autoload.php'; // Composer autoload for PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

echo "<h1>Klara POS Data Analysis</h1>";
echo "<hr>";

// File paths
$artikelFile = __DIR__ . '/database/Artikel_Export.xlsx';
$deliveryFile = __DIR__ . '/database/Delivery_Export_from_1_to_43.xlsx';

/**
 * Analyze Excel file
 */
function analyzeExcel($filePath, $fileName) {
    echo "<h2>Analyzing: $fileName</h2>";

    if (!file_exists($filePath)) {
        echo "<p style='color: red;'>File not found: $filePath</p>";
        return null;
    }

    try {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Get all data
        $data = $worksheet->toArray();
        $headers = $data[0] ?? [];
        $rows = array_slice($data, 1);

        echo "<p><strong>Total Rows:</strong> " . count($rows) . "</p>";
        echo "<p><strong>Total Columns:</strong> " . count($headers) . "</p>";

        // Display headers
        echo "<h3>Columns:</h3>";
        echo "<ol>";
        foreach ($headers as $header) {
            echo "<li>" . htmlspecialchars($header) . "</li>";
        }
        echo "</ol>";

        // Display first 5 rows
        echo "<h3>Sample Data (First 5 rows):</h3>";
        echo "<div style='overflow-x: auto;'>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse; font-size: 12px;'>";
        echo "<thead><tr>";
        foreach ($headers as $header) {
            echo "<th>" . htmlspecialchars($header) . "</th>";
        }
        echo "</tr></thead>";
        echo "<tbody>";

        $sampleRows = array_slice($rows, 0, 5);
        foreach ($sampleRows as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
            }
            echo "</tr>";
        }

        echo "</tbody></table>";
        echo "</div>";

        // Save analysis as JSON
        $analysis = [
            'file' => $fileName,
            'total_rows' => count($rows),
            'total_columns' => count($headers),
            'columns' => $headers,
            'sample_data' => $sampleRows
        ];

        $jsonFile = str_replace('.xlsx', '_analysis.json', $filePath);
        file_put_contents($jsonFile, json_encode($analysis, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo "<p style='color: green;'>✓ Analysis saved to: " . basename($jsonFile) . "</p>";

        return $analysis;

    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        return null;
    }
}

// Analyze both files
echo "<div style='background: #f5f5f5; padding: 20px; margin: 20px 0;'>";
$artikelAnalysis = analyzeExcel($artikelFile, 'Artikel_Export.xlsx');
echo "</div>";

echo "<hr>";

echo "<div style='background: #f5f5f5; padding: 20px; margin: 20px 0;'>";
$deliveryAnalysis = analyzeExcel($deliveryFile, 'Delivery_Export_from_1_to_43.xlsx');
echo "</div>";

echo "<hr>";
echo "<h2>Next Steps:</h2>";
echo "<ol>";
echo "<li>Review the column structures above</li>";
echo "<li>Identify which columns map to: Product Name, Category, Price, Stock, SKU/Article Number</li>";
echo "<li>Create import script to populate wines table</li>";
echo "<li>Set up Klara API integration for real-time sync</li>";
echo "</ol>";

echo "<p><a href='?page=admin-dashboard'>← Back to Admin Dashboard</a></p>";
?>
