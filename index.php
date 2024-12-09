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

// Handle character search
$search_results = null;
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $search_sql = 'SELECT id, name, race, class, hp, ac, is_alive FROM chars WHERE name LIKE :search';
    $search_stmt = $pdo->prepare($search_sql);
    $search_stmt->execute(['search' => $search_term]);
    $search_results = $search_stmt->fetchAll();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['name']) && isset($_POST['race']) && isset($_POST['class']) && isset($_POST['hp']) && isset($_POST['ac'])) {
        // Insert new entry
        $name = htmlspecialchars($_POST['name']);
        $race = htmlspecialchars($_POST['race']);
        $class = htmlspecialchars($_POST['class']);
        $hp = (int) $_POST['hp'];
        $ac = (int) $_POST['ac'];
        $is_alive = isset($_POST['is_alive']) ? 1 : 0;

        $insert_sql = 'INSERT INTO chars (name, race, class, hp, ac, is_alive) VALUES (:name, :race, :class, :hp, :ac, :is_alive)';
        $stmt_insert = $pdo->prepare($insert_sql);
        $stmt_insert->execute(['name' => $name, 'race' => $race, 'class' => $class, 'hp' => $hp, 'ac' => $ac, 'is_alive' => $is_alive]);
    } elseif (isset($_POST['delete_id'])) {
        // Delete an entry
        $delete_id = (int) $_POST['delete_id'];

        $delete_sql = 'DELETE FROM chars WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    } elseif (isset($_POST['edit_id'])) {
        // Update an entry
        $edit_id = (int) $_POST['edit_id'];
        $name = htmlspecialchars($_POST['edit_name']);
        $race = htmlspecialchars($_POST['edit_race']);
        $class = htmlspecialchars($_POST['edit_class']);
        $hp = (int) $_POST['edit_hp'];
        $ac = (int) $_POST['edit_ac'];
        $is_alive = isset($_POST['edit_is_alive']) ? 1 : 0;

        $update_sql = 'UPDATE chars SET name = :name, race = :race, class = :class, hp = :hp, ac = :ac, is_alive = :is_alive WHERE id = :id';
        $stmt_update = $pdo->prepare($update_sql);
        $stmt_update->execute(['name' => $name, 'race' => $race, 'class' => $class, 'hp' => $hp, 'ac' => $ac, 'is_alive' => $is_alive, 'id' => $edit_id]);
    }
}

// Get all characters for main table
$sql = 'SELECT id, name, race, class, hp, ac, is_alive FROM chars';
$stmt = $pdo->query($sql);

$edit_character = null;
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $edit_stmt = $pdo->prepare('SELECT * FROM chars WHERE id = :id');
    $edit_stmt->execute(['id' => $edit_id]);
    $edit_character = $edit_stmt->fetch();
}
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
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['race']); ?></td>
                    <td><?php echo htmlspecialchars($row['class']); ?></td>
                    <td><?php echo htmlspecialchars($row['hp']); ?></td>
                    <td><?php echo htmlspecialchars($row['ac']); ?></td>
                    <td><?php echo $row['is_alive'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <form action="index.php" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                            <input type="submit" value="Delete">
                        </form>
                        <a href="?edit_id=<?php echo $row['id']; ?>">Edit</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Edit Form -->
        <?php if ($edit_character): ?>
            <h2>Edit Character</h2>
            <form action="index.php" method="post">
                <input type="hidden" name="edit_id" value="<?php echo $edit_character['id']; ?>">
                <label>Name: <input type="text" name="edit_name" value="<?php echo htmlspecialchars($edit_character['name']); ?>" required></label><br>
                <label>Race: <input type="text" name="edit_race" value="<?php echo htmlspecialchars($edit_character['race']); ?>" required></label><br>
                <label>Class: <input type="text" name="edit_class" value="<?php echo htmlspecialchars($edit_character['class']); ?>" required></label><br>
                <label>HP: <input type="number" name="edit_hp" value="<?php echo $edit_character['hp']; ?>" required></label><br>
                <label>AC: <input type="number" name="edit_ac" value="<?php echo $edit_character['ac']; ?>" required></label><br>
                <label>Alive: <input type="checkbox" name="edit_is_alive" <?php echo $edit_character['is_alive'] ? 'checked' : ''; ?>></label><br>
                <input type="submit" value="Update Character">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
