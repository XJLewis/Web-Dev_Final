<?php
$host = 'localhost';
$dbname = 'final';
$user = 'xander';
$pass = 'passwd';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Initialize variables
    $characterName = '';
    $inventory = [];

    // Check if a search was submitted
    if (isset($_GET['character_name']) && !empty($_GET['character_name'])) {
        $characterName = $_GET['character_name'];

        // SQL query to fetch character inventory
        $sql = "SELECT i.item_name, i.item_type, i.damage, i.armor_class, i.camp_supply, i.quantity, i.description
                FROM inventory i
                JOIN chars c ON i.character_id = c.id
                WHERE c.name = :characterName";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':characterName', $characterName);
        $stmt->execute();

        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="hero-section">
        <h1>Inventory Management</h1>
        <!-- Button to go to index.php -->
        <a href="index.php">
            <button class="btn-about">Home Page</button>
        <a href="about.html">
            <button class="btn-about">About Us</button>
        </a>
    </div>

    <div class="central-container">
        <h1>Inventory Management</h1>
        <!-- Search Form -->
        <form method="get" action="inventory.php" class="search-form">
            <label for="character_name">Search Character:</label>
            <input type="text" id="character_name" name="character_name" placeholder="Enter character name" value="<?= htmlspecialchars($characterName) ?>">
            <button type="submit">Search</button>
        </form>

        <!-- Inventory Table -->
        <?php if (!empty($characterName)): ?>
            <h2>Inventory for <?= htmlspecialchars($characterName) ?></h2>
            <?php if ($inventory): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Details</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventory as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['item_type']) ?></td>
                                <td><?= htmlspecialchars($item['quantity']) ?></td>
                                <td>
                                    <?php
                                    // Conditional display based on item type
                                    if ($item['item_type'] === 'Weapon' && !empty($item['damage'])) {
                                        echo "Damage: " . htmlspecialchars($item['damage']);
                                    } elseif ($item['item_type'] === 'Armor' && !empty($item['armor_class'])) {
                                        echo "Armor Class: " . htmlspecialchars($item['armor_class']);
                                    } elseif ($item['item_type'] === 'Food' && !empty($item['camp_supply'])) {
                                        echo "Camp Supply: " . htmlspecialchars($item['camp_supply']);
                                    } else {
                                        echo "N/A";
                                    }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No inventory found for <?= htmlspecialchars($characterName) ?>.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>