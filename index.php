<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

// Abfrage: Gesamtanzahl der Tickets des angemeldeten Benutzers
$stmt_total = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE user_id = ?");
$stmt_total->execute([$user_id]);
$total_tickets = $stmt_total->fetchColumn();

// Abfrage: Anzahl der offenen Tickets
$stmt_open = $pdo->prepare("SELECT COUNT(*) AS open FROM tickets WHERE user_id = ? AND status IN ('Neu', 'In Arbeit', 'Zurückgestellt')");
$stmt_open->execute([$user_id]);
$open_tickets = $stmt_open->fetchColumn();

// Abfrage: Anzahl der geschlossenen Tickets
$stmt_closed = $pdo->prepare("SELECT COUNT(*) AS closed FROM tickets WHERE user_id = ? AND status = 'Abgeschlossen'");
$stmt_closed->execute([$user_id]);
$closed_tickets = $stmt_closed->fetchColumn();

include 'includes/header.php';
?>

<div class="container">
    <h2>Dashboard</h2>
    <p>Willkommen, Sie sind eingeloggt!</p>

    <!-- Übersicht über die Tickets -->
    <div class="ticket-summary">
        <div>
            <h3>Gesamtanzahl Tickets</h3>
            <p><?php echo $total_tickets; ?></p>
        </div>
        <div>
            <h3>Offene Tickets</h3>
            <p><?php echo $open_tickets; ?></p>
        </div>
        <div>
            <h3>Geschlossene Tickets</h3>
            <p><?php echo $closed_tickets; ?></p>
        </div>
    </div>
</div>

</body>
</html>