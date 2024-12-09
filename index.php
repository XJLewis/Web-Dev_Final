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

// Handle updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_id'])) {
        $id = (int) $_POST['edit_id'];
        $name = htmlspecialchars($_POST['name']);
        $race = htmlspecialchars($_POST['race']);
        $class = htmlspecialchars($_POST['class']);
        $hp = (int) $_POST['hp'];
        $ac = (int) $_POST['ac'];
        $is_alive = isset($_POST['is_alive']) ? 1 : 0;

        $update_sql = 'UPDATE chars SET name = :name, race = :race, class = :class, hp = :hp, ac = :ac, is_alive = :is_alive WHERE id = :id';
        $stmt_update = $pdo->prepare($update_sql);
        $stmt_update->execute(['name' => $name, 'race' => $race, 'class' => $class, 'hp' => $hp, 'ac' => $ac, 'is_alive' => $is_alive, 'id' => $id]);
    } elseif (isset($_POST['delete_id'])) {
        $delete_id = (int) $_POST['delete_id'];
        $delete_sql = 'DELETE FROM chars WHERE id = :id';
        $stmt_delete = $pdo->prepare($delete_sql);
        $stmt_delete->execute(['id' => $delete_id]);
    } elseif (isset($_POST['name']) && isset($_POST['race'])) {
        // Insert new character
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
$characters = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Character Database</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleEdit(id) {
            const row = document.getElementById('row-' + id);
            const form = document.getElementById('edit-form-' + id);
            row.style.display = row.style.display === 'none' ? '' : 'none';
            form.style.display = form.style.display === 'none' ? '' : 'none';
        }
    </script>
</head>
<body>
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
                <?php foreach ($characters as $char): ?>
                <!-- Display Row -->
                <tr id="row-<?php echo $char['id']; ?>">
                    <td><?php echo htmlspecialchars($char['name']); ?></td>
                    <td><?php echo htmlspecialchars($char['race']); ?></td>
                    <td><?php echo htmlspecialchars($char['class']); ?></td>
                    <td><?php echo htmlspecialchars($char['hp']); ?></td>
                    <td><?php echo htmlspecialchars($char['ac']); ?></td>
                    <td><?php echo $char['is_alive'] ? 'Yes' : 'No'; ?></td>
                    <td>
                        <button type="button" onclick="toggleEdit(<?php echo $char['id']; ?>)">Edit</button>
                        <form action="" method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $char['id']; ?>">
                            <input type="submit" value="Delete">
                        </form>
                    </td>
                </tr>

                <!-- Inline Edit Form -->
                <tr id="edit-form-<?php echo $char['id']; ?>" style="display:none;">
                    <form action="" method="post">
                        <input type="hidden" name="edit_id" value="<?php echo $char['id']; ?>">
                        <td><input type="text" name="name" value="<?php echo htmlspecialchars($char['name']); ?>" required></td>
                        <td><input type="text" name="race" value="<?php echo htmlspecialchars($char['race']); ?>" required></td>
                        <td><input type="text" name="class" value="<?php echo htmlspecialchars($char['class']); ?>" required></td>
                        <td><input type="number" name="hp" value="<?php echo htmlspecialchars($char['hp']); ?>" required></td>
                        <td><input type="number" name="ac" value="<?php echo htmlspecialchars($char['ac']); ?>" required></td>
                        <td>
                            <input type="checkbox" name="is_alive" value="1" <?php echo $char['is_alive'] ? 'checked' : ''; ?>>
                        </td>
                        <td>
                            <input type="submit" value="Save">
                            <button type="button" onclick="toggleEdit(<?php echo $char['id']; ?>)">Cancel</button>
                        </td>
                    </form>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Add Character Form -->
        <div class="form-container">
            <h2>Add a New Character</h2>
            <form action="" method="post">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                <label for="race">Race:</label>
                <input type="text" id="race" name="race" required>
                <label for="class">Class:</label>
                <input type="text" id="class" name="class" required>
                <label for="hp">HP:</label>
                <input type="number" id="hp" name="hp" required>
                <label for="ac">AC:</label>
                <input type="number" id="ac" name="ac" required>
                <label for="is_alive">Alive:</label>
                <input type="checkbox" id="is_alive" name="is_alive" value="1">
                <input type="submit" value="Add Character">
            </form>
        </div>
    </div>
</body>
</html>
