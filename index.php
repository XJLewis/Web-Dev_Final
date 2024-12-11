<?php
session_start();
require_once 'auth.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

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
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    throw new PDOException($e->getMessage(), (int)$e->getCode());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_id'])) {
        // Update entry
        $edit_id = (int) $_POST['edit_id'];
        $name = htmlspecialchars($_POST['name']);
        $race = htmlspecialchars($_POST['race']);
        $class = htmlspecialchars($_POST['class']);
        $hp = (int) $_POST['hp'];
        $ac = (int) $_POST['ac'];
        $is_alive = isset($_POST['is_alive']) ? 1 : 0;

        $update_sql = 'UPDATE chars SET name = :name, race = :race, class = :class, hp = :hp, ac = :ac, is_alive = :is_alive WHERE id = :id';
        $stmt_update = $pdo->prepare($update_sql);
        $stmt_update->execute([
            'name' => $name, 'race' => $race, 'class' => $class, 
            'hp' => $hp, 'ac' => $ac, 'is_alive' => $is_alive, 'id' => $edit_id
        ]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];
        $delete_sql = 'DELETE FROM chars WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    } elseif (isset($_POST['name'], $_POST['race'], $_POST['class'], $_POST['hp'], $_POST['ac'])) {
        // Insert a new character
        $name = htmlspecialchars($_POST['name']);
        $race = htmlspecialchars($_POST['race']);
        $class = htmlspecialchars($_POST['class']);
        $hp = (int) $_POST['hp'];
        $ac = (int) $_POST['ac'];
        $is_alive = isset($_POST['is_alive']) ? 1 : 0;

        $insert_sql = 'INSERT INTO chars (name, race, class, hp, ac, is_alive) VALUES (:name, :race, :class, :hp, :ac, :is_alive)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['name' => $name, 'race' => $race, 'class' => $class, 'hp' => $hp, 'ac' => $ac, 'is_alive' => $is_alive]);
    }
}

// Fetch all characters
$sql = 'SELECT id, name, race, class, hp, ac, is_alive FROM chars';
$stmt = $pdo->query($sql);

// To check which row is being edited
$edit_row_id = isset($_POST['row_id']) ? (int)$_POST['row_id'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Character Database</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <h1 class="hero-title">Baldur's Gate 3 Party Management</h1>
        <p class="hero-subtitle">For all your adventuring needs</p>
        <a href="about.html"><button class="btn-about">About Us</button></a>
        <a href="inventory.php"><button class="btn-about">Inventory</button></a>
    </div>

    <!-- Table Section -->
    <div class="central-container">
        <h2>Your Party</h2>
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Race</th>
                    <th>Class</th>
                    <th>HP</th>
                    <th>AC</th>
                    <th>Alive</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $stmt->fetch()): ?>
                    <tr>
                        <?php if ($edit_row_id === $row['id']): ?>
                            <!-- Edit mode, GPT helped heavily -->
                            <form method="post" action="index.php">
                                <input type="hidden" name="edit_id" value="<?php echo $row['id']; ?>">
                                <td><input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required></td>
                                <td><input type="text" name="race" value="<?php echo htmlspecialchars($row['race']); ?>" required></td>
                                <td><input type="text" name="class" value="<?php echo htmlspecialchars($row['class']); ?>" required></td>
                                <td><input type="number" name="hp" value="<?php echo htmlspecialchars($row['hp']); ?>" required></td>
                                <td><input type="number" name="ac" value="<?php echo htmlspecialchars($row['ac']); ?>" required></td>
                                <td>
                                    <input type="checkbox" name="is_alive" value="1" <?php echo $row['is_alive'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <input type="submit" value="Save">
                                    <button type="submit" name="cancel" value="1">Cancel</button>
                                </td>
                            </form>
                        <?php else: ?>
                            <!-- Static mode -->
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['race']); ?></td>
                            <td><?php echo htmlspecialchars($row['class']); ?></td>
                            <td><?php echo htmlspecialchars($row['hp']); ?></td>
                            <td><?php echo htmlspecialchars($row['ac']); ?></td>
                            <td><?php echo $row['is_alive'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <form method="post" action="index.php" style="display:inline;">
                                    <input type="hidden" name="row_id" value="<?php echo $row['id']; ?>">
                                    <input type="submit" value="Edit">
                                </form>
                                <form method="post" action="index.php" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <input type="submit" value="Delete">
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Form Section -->
        <div class="form-container">
            <h2>Add a New Character</h2>
            <form action="index.php" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required><br><br>
                <label for="race">Race:</label>
                <input type="text" id="race" name="race" required><br><br>
                <label for="class">Class:</label>
                <input type="text" id="class" name="class" required><br><br>
                <label for="hp">HP:</label>
                <input type="number" id="hp" name="hp" required><br><br>
                <label for="ac">AC:</label>
                <input type="number" id="ac" name="ac" required><br><br>
                <label for="is_alive">Alive:</label>
                <input type="checkbox" id="is_alive" name="is_alive" value="1"><br><br>
                <input type="submit" value="Add Character">
            </form>
        </div>
    </div>
</body>
</html>
