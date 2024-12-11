<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $feedback = htmlspecialchars($_POST['feedback']);

    // For now, just display the data (or save it to a database)
    echo "Thank you, $name!<br>";
    echo "We received your feedback: <br>";
    echo nl2br($feedback);
}
?>
