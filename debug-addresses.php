<?php
// debug-addresses.php - Debug script to check addresses
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("Please login first. <a href='?page=home&modal=login'>Login</a>");
}

$user_id = $_SESSION['user_id'];
$result = $db->query("SELECT * FROM user_addresses WHERE user_id = $user_id");

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Debug - Addresses</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f8f8; font-weight: 600; }
        .missing { color: red; font-weight: bold; }
        .present { color: green; }
        h1 { color: #333; }
        .field-check { font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Debug: User Addresses</h1>
        <p><strong>User ID:</strong> <?php echo $user_id; ?></p>
        <p><strong>User Email:</strong> <?php echo $_SESSION['email'] ?? 'Not set'; ?></p>

        <h2>Saved Addresses in Database:</h2>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Street</th>
                        <th>Postal</th>
                        <th>City</th>
                        <th>Phone</th>
                        <th>Label</th>
                        <th>Default</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($addr = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $addr['id']; ?></td>
                        <td><?php echo $addr['first_name'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['last_name'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['street'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['postal_code'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['city'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['phone'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['label'] ?? '<span class="missing">NULL</span>'; ?></td>
                        <td><?php echo $addr['is_default'] ? 'Ja' : 'Nein'; ?></td>
                        <td class="field-check">
                            <?php
                            $required = ['first_name', 'last_name', 'street', 'postal_code', 'city', 'phone'];
                            $missing = [];
                            foreach ($required as $field) {
                                if (empty($addr[$field])) {
                                    $missing[] = $field;
                                }
                            }
                            if (empty($missing)) {
                                echo '<span class="present">Complete</span>';
                            } else {
                                echo '<span class="missing">Missing: ' . implode(', ', $missing) . '</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="color: orange;">No addresses found in database.</p>
        <?php endif; ?>

        <h2>Test Data Attributes:</h2>
        <p>This shows how the address data would appear in checkout:</p>
        <?php
        $result = $db->query("SELECT * FROM user_addresses WHERE user_id = $user_id");
        if ($result && $result->num_rows > 0):
            $addresses = $result->fetch_all(MYSQLI_ASSOC);
            foreach ($addresses as $addr):
        ?>
        <div style="background: #f9f9f9; padding: 15px; margin: 10px 0; border-radius: 5px;">
            <h4>Address ID: <?php echo $addr['id']; ?></h4>
            <p><strong>HTML Attributes:</strong></p>
            <pre style="background: #fff; padding: 10px; overflow-x: auto;">data-first-name="<?php echo htmlspecialchars($addr['first_name'] ?? ''); ?>"
data-last-name="<?php echo htmlspecialchars($addr['last_name'] ?? ''); ?>"
data-street="<?php echo htmlspecialchars($addr['street'] ?? ''); ?>"
data-postal-code="<?php echo htmlspecialchars($addr['postal_code'] ?? ''); ?>"
data-city="<?php echo htmlspecialchars($addr['city'] ?? ''); ?>"
data-phone="<?php echo htmlspecialchars($addr['phone'] ?? ''); ?>"</pre>

            <p><strong>JavaScript Access (dataset):</strong></p>
            <pre style="background: #fff; padding: 10px; overflow-x: auto;">dataset.firstName = "<?php echo htmlspecialchars($addr['first_name'] ?? ''); ?>"
dataset.lastName = "<?php echo htmlspecialchars($addr['last_name'] ?? ''); ?>"
dataset.street = "<?php echo htmlspecialchars($addr['street'] ?? ''); ?>"
dataset.postalCode = "<?php echo htmlspecialchars($addr['postal_code'] ?? ''); ?>"
dataset.city = "<?php echo htmlspecialchars($addr['city'] ?? ''); ?>"
dataset.phone = "<?php echo htmlspecialchars($addr['phone'] ?? ''); ?>"</pre>
        </div>
        <?php endforeach; endif; ?>

        <hr>
        <p><a href="?page=user-portal&tab=addresses">← Back to Address Book</a> | <a href="?page=checkout">Go to Checkout</a></p>
    </div>
</body>
</html>
