<?php
require 'sessions/session.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];  // Nur der angemeldete Benutzer darf ein Ticket erstellen

    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description, status) VALUES (?, ?, ?, 'Neu')");
    $stmt->execute([$user_id, $title, $description]);

    header("Location: index.php");
    exit;
}
?>

<!-- Einbindung des CSS für das moderne Design -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket erstellen</title>
    <link rel="stylesheet" href="css/styles.css">  <!-- Stelle sicher, dass der Pfad zu deiner CSS-Datei stimmt -->
</head>
<body>

<header>
    <h1>Ticketsystem</h1>
    <a href="index.php">Home</a>
    <a href="logout.php">Logout</a>
</header>

<div class="container">
    <h2>Ticket erstellen</h2>
    <form action="ticket_create.php" method="POST">
        <label for="title">Titel:</label>
        <input type="text" id="title" name="title" required>

        <label for="description">Beschreibung:</label>
        <textarea id="description" name="description" required></textarea>

        <button type="submit">Ticket erstellen</button>
    </form>
</div>

</body>
</html>