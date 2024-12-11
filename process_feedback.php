<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $feedback = htmlspecialchars($_POST['feedback']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Feedback Received</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="hero-section">
        <h1 class="hero-title">Thank You!</h1>
        <p class="hero-subtitle">We appreciate your feedback</p>
    </div>

    <div class="central-container" style="text-align: center;">
        <h2>Feedback Summary</h2>
        <p><strong>Name:</strong> <?php echo $name; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
        <p><strong>Message:</strong></p>
        <div>
            <?php echo nl2br($feedback); ?>
        </div>
        <br>
        <p>Thank you for helping us improve the website!</p>
        <br>
        <a href="index.php">
            <button class="btn-about">Home Page</button>
        </a>
    </div>
</body>
</html>
