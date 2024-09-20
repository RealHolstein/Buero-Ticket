<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'sessions/session.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Titel und Beschreibung sicher vor SQL-Injection und XSS schützen
    $title = htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars(trim($_POST['description']), ENT_QUOTES, 'UTF-8');
    $user_id = $_SESSION['user_id'];  // Der angemeldete Benutzer

    // Datenbankeintrag vorbereiten und ausführen
    $stmt = $pdo->prepare("INSERT INTO tickets (user_id, title, description, status) VALUES (?, ?, ?, 'Neu')");
    $stmt->execute([$user_id, $title, $description]);

    // Weiterleitung nach der Erstellung des Tickets
    header("Location: ticket.php?id=" . $pdo->lastInsertId()); // Weiterleitung zum erstellten Ticket
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket erstellen</title>
    <link rel="stylesheet" href="css/styles.css">  <!-- Stelle sicher, dass der Pfad zu deiner CSS-Datei stimmt -->
</head>
<body>

<!-- Einbinden des Headers -->
<?php include 'includes/header.php'; ?>

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