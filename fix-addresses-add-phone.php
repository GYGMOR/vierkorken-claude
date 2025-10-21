<?php
// fix-addresses-add-phone.php - Add phone field to existing addresses
session_start();
require_once 'config/database.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    die("Access denied. Admin only.");
}

echo "<h1>Fix User Addresses - Add Missing Phone Field</h1>";

// Check if phone column exists
$check = $db->query("SHOW COLUMNS FROM user_addresses LIKE 'phone'");
if ($check && $check->num_rows > 0) {
    echo "<p style='color: green;'>Phone column already exists in user_addresses table.</p>";
} else {
    echo "<p style='color: orange;'>Phone column does not exist. Adding it now...</p>";
    $alter = $db->query("ALTER TABLE user_addresses ADD COLUMN phone VARCHAR(50) DEFAULT '' AFTER city");
    if ($alter) {
        echo "<p style='color: green;'>Phone column added successfully!</p>";
    } else {
        echo "<p style='color: red;'>Error adding phone column: " . $db->error . "</p>";
    }
}

// Get all addresses without phone
$result = $db->query("SELECT id, user_id, first_name, last_name, phone FROM user_addresses");

echo "<h2>Current Addresses:</h2>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr><th>ID</th><th>User ID</th><th>Name</th><th>Phone</th><th>Status</th></tr>";

while ($addr = $result->fetch_assoc()) {
    $status = empty($addr['phone']) ? "<span style='color: red;'>Missing</span>" : "<span style='color: green;'>OK</span>";
    echo "<tr>";
    echo "<td>{$addr['id']}</td>";
    echo "<td>{$addr['user_id']}</td>";
    echo "<td>{$addr['first_name']} {$addr['last_name']}</td>";
    echo "<td>" . ($addr['phone'] ?: '<em>empty</em>') . "</td>";
    echo "<td>$status</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Go to <a href='?page=user-portal&tab=addresses'>User Portal → Adressbuch</a></li>";
echo "<li>Edit each address and add a phone number</li>";
echo "<li>Or delete old addresses and create new ones with all required fields</li>";
echo "</ol>";

echo "<p><a href='debug-addresses.php'>→ Check Addresses in Debug View</a></p>";
?>
