<?php
// generate-hash.php - Generiere einen korrekten bcrypt Hash

$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Passwort: " . $password . "<br>";
echo "Hash: " . $hash . "<br>";
echo "<br>";
echo "SQL zum kopieren:<br>";
echo "UPDATE users SET password = '" . $hash . "' WHERE id = 7;";
?>