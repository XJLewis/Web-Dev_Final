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

    // Fetch list of characters for dropdowns
    $characters_sql = "SELECT id, name FROM chars";
    $characters_stmt = $pdo->query($characters_sql);
    $characters = $characters_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize variables
    $characterId = '';
    $inventory = [];

    // Check if a search was submitted
    if (isset($_GET['character_id']) && !empty($_GET['character_id'])) {
        $characterId = (int)$_GET['character_id'];

        // SQL query to fetch character inventory
        $sql = "SELECT i.item_name, i.item_type, i.damage, i.armor_class, i.camp_supply, i.quantity, i.description, i.equipped
                FROM inventory i
                JOIN chars c ON i.character_id = c.id
                WHERE c.id = :characterId";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':characterId', $characterId);
        $stmt->execute();

        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Handle adding a new item
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
        // Sanitize inputs
        $character_id = (int) $_POST['character_id'];
        $item_name = htmlspecialchars($_POST['item_name']);
        $item_type = htmlspecialchars($_POST['item_type']);
        $damage = !empty($_POST['damage']) ? htmlspecialchars($_POST['damage']) : null;
        $armor_class = !empty($_POST['armor_class']) ? (int) $_POST['armor_class'] : null;
        $camp_supply = !empty($_POST['camp_supply']) ? (int) $_POST['camp_supply'] : null;
        $equipped = isset($_POST['equipped']) ? 1 : 0; // Set equipped flag if checkbox is checked

        // Insert into the database
        $insert_sql = "INSERT INTO inventory (character_id, item_name, item_type, damage, armor_class, camp_supply, equipped, description) 
                    VALUES (:character_id, :item_name, :item_type, :damage, :armor_class, :camp_supply, :equipped, :description)";
        $stmt = $pdo->prepare($insert_sql);
        $stmt->execute([
            'character_id' => $character_id,
            'item_name' => $item_name,
            'item_type' => $item_type,
            'damage' => $damage,
            'armor_class' => $armor_class,
            'camp_supply' => $camp_supply,
            'equipped' => $equipped,
            'description' => htmlspecialchars($_POST['description'])  // Sanitize description
        ]);
    }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    // Handle deleting an item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_item'])) {
    // Sanitize inputs
    $character_id = (int) $_POST['character_id'];
    $item_name = htmlspecialchars($_POST['item_name']);

    // SQL query to delete the item
    $delete_sql = "DELETE FROM inventory WHERE character_id = :character_id AND item_name = :item_name LIMIT 1";
    $stmt = $pdo->prepare($delete_sql);

    try {
        $stmt->execute([
            'character_id' => $character_id,
            'item_name' => $item_name
        ]);
        echo "<p>Item successfully deleted!</p>";
    } catch (PDOException $e) {
        echo "Error deleting item: " . $e->getMessage();
    }
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
        <h1 class="hero-title">Inventory Management</h1>
        <h2 class="hero-subtitle">Edit and browse your party's inventory</h2>
        <a href="index.php">
            <button class="btn-about">Home Page</button>
        </a>
        <a href="about.html">
            <button class="btn-about">About Us</button>
        </a>
    </div>

    <div class="central-container">
        <!-- Search Form -->
        <h2>Search Inventory by Character</h2>
        <form method="get" action="inventory.php" class="search-form">
            <label for="character_id">Select Character:</label>
            <select name="character_id" id="character_id" required>
                <option value="">-- Select a Character --</option>
                <?php foreach ($characters as $character): ?>
                    <option value="<?= $character['id']; ?>" <?= $character['id'] == $characterId ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($character['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>

        <!-- Inventory Table -->
        <?php if (!empty($characterId)): ?>
            <h2>Inventory</h2>
            <?php if ($inventory): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Details</th>
                            <th>Description</th>
                            <th>Equipped</th>
                            <th>Actions</th>
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
                                <td><?= $item['equipped'] ? 'Yes' : 'No' ?></td>
                                <td>
                                    <!-- Delete Button Form -->
                                    <form action="inventory.php" method="post" style="display:inline;">
                                        <input type="hidden" name="delete_item" value="1">
                                        <input type="hidden" name="item_name" value="<?= htmlspecialchars($item['item_name']) ?>">
                                        <input type="hidden" name="character_id" value="<?= $characterId ?>">
                                        <input type="submit" value="Delete">
                                    </form>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            <?php else: ?>
                <p>No inventory found for the selected character.</p>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Add Item Form -->
        <div class="form-container">
        <h2>Add New Item</h2>
        <form action="inventory.php" method="POST">
            <label for="character_id">Select Character:</label>
            <select name="character_id" id="character_id" required>
                <option value="">-- Select a Character --</option>
                <?php foreach ($characters as $character): ?>
                    <option value="<?= $character['id']; ?>">
                        <?= htmlspecialchars($character['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="item_name">Item Name:</label>
            <input type="text" name="item_name" id="item_name" required>

            <label for="item_type">Item Type:</label>
            <select name="item_type" id="item_type" required onchange="toggleItemFields()">
                <option value="Weapon">Weapon</option>
                <option value="Armor">Armor</option>
                <option value="Food">Camp Supply</option>
                <option value="Other">Miscellaneous</option>
            </select>

            <div id="damage-field" class="item-field" style="display:none;">
                <label for="damage">Damage:</label>
                <input type="text" name="damage" id="damage">
            </div>

            <div id="armor_class-field" class="item-field" style="display:none;">
                <label for="armor_class">Armor Class:</label>
                <input type="number" name="armor_class" id="armor_class">
            </div>

            <div id="camp_supply-field" class="item-field" style="display:none;">
                <label for="camp_supply">Camp Supply Value:</label>
                <input type="number" name="camp_supply" id="camp_supply">
            </div>

            <div id="equipped-field" class="item-field" style="display:none;">
                <label for="equipped">Equipped:</label>
                <input type="checkbox" name="equipped" id="equipped">
            </div>

            <label for="description">Description:</label>
            <textarea name="description" id="description"></textarea>

            <input type="submit" name="add_item" value="Add Item">
        </form>
    </div>
    </div>

    <script>
        function toggleItemFields() {
            // Get the selected item type
            var itemType = document.getElementById("item_type").value;

            // Reset all fields to hidden
            document.getElementById("damage-field").style.display = "none";
            document.getElementById("armor_class-field").style.display = "none";
            document.getElementById("camp_supply-field").style.display = "none";
            document.getElementById("equipped-field").style.display = "none";

            // Show relevant fields based on the selected item type
            if (itemType === "Weapon") {
                document.getElementById("damage-field").style.display = "block";
                document.getElementById("equipped-field").style.display = "block";
            } else if (itemType === "Armor") {
                document.getElementById("armor_class-field").style.display = "block";
                document.getElementById("equipped-field").style.display = "block";
            } else if (itemType === "Food") {
                document.getElementById("camp_supply-field").style.display = "block";
            }
        }

        // Call toggleItemFields when the page loads to adjust visibility of fields
        window.onload = toggleItemFields;
    </script>
</body>
</html>
