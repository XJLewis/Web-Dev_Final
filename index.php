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
    }
}

// Get all characters for main table
$sql = 'SELECT id, name, race, class, hp, ac, is_alive FROM chars';
$stmt = $pdo->query($sql);
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
        <h1 class="hero-title">Baldur's Gate 3 Character Management</h1>
        <p class="hero-subtitle">For all your adventuring needs</p>
        
        <!-- Search Section -->
        <div class="hero-search">
            <h2>Search for a Character</h2>
            <form action="" method="GET" class="search-form">
                <label for="search">Search by Name:</label>
                <input type="text" id="search" name="search" required>
                <input type="submit" value="Search">
            </form>
            
            <?php if (isset($_GET['search'])): ?>
                <div class="search-results">
                    <h3>Search Results</h3>
                    <?php if ($search_results && count($search_results) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
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
                                <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
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
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No characters found matching your search.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Table Section -->
    <div class="table-container">
        <h2>All Characters in Database</h2>
        <table class="half-width-left-align">
            <thead>
                <tr>
                    <th>ID</th>
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
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
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
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Form Section -->
    <div class="form-container">
        <h2>Add a New Character</h2>
        <form action="index.php" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
            <br><br>
            <label for="race">Race:</label>
            <input type="text" id="race" name="race" required>
            <br><br>
            <label for="class">Class:</label>
            <input type="text" id="class" name="class" required>
            <br><br>
            <label for="hp">HP:</label>
            <input type="number" id="hp" name="hp" required>
            <br><br>
            <label for="ac">AC:</label>
            <input type="number" id="ac" name="ac" required>
            <br><br>
            <label for="is_alive">Alive:</label>
            <input type="checkbox" id="is_alive" name="is_alive" value="1">
            <br><br>
            <input type="submit" value="Add Character">
        </form>
    </div>
</body>
</html>
