<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID und Rolle aus der Session holen
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Admin-Übersicht: Tickets nach Status anzeigen
if ($user_role === 'admin') {
    // Abfrage: Anzahl der Tickets nach Status
    $stmt_new = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE status = 'Neu'");
    $stmt_new->execute();
    $new_tickets = $stmt_new->fetchColumn();

    $stmt_in_progress = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE status = 'In Arbeit'");
    $stmt_in_progress->execute();
    $in_progress_tickets = $stmt_in_progress->fetchColumn();

    $stmt_on_hold = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE status = 'Zurückgestellt'");
    $stmt_on_hold->execute();
    $on_hold_tickets = $stmt_on_hold->fetchColumn();

    $stmt_closed = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE status = 'Abgeschlossen'");
    $stmt_closed->execute();
    $closed_tickets = $stmt_closed->fetchColumn();
}

include 'includes/header.php';
?>

<div class="container">
    <h2>Dashboard</h2>
    <p>Willkommen, Sie sind eingeloggt!</p>

    <!-- Übersicht für Admins: Tickets nach Status -->
    <?php if ($user_role === 'admin'): ?>
    <div class="ticket-summary">
        <h3>Ticket Übersicht nach Status</h3>
        <ul>
            <li>
                <a href="ticket_list.php?status=Neu">
                    Neu: <?php echo $new_tickets; ?> Tickets
                </a>
            </li>
            <li>
                <a href="ticket_list.php?status=In Arbeit">
                    In Arbeit: <?php echo $in_progress_tickets; ?> Tickets
                </a>
            </li>
            <li>
                <a href="ticket_list.php?status=Zurückgestellt">
                    Zurückgestellt: <?php echo $on_hold_tickets; ?> Tickets
                </a>
            </li>
            <li>
                <a href="ticket_list.php?status=Abgeschlossen">
                    Abgeschlossen: <?php echo $closed_tickets; ?> Tickets
                </a>
            </li>
        </ul>
    </div>
    <?php endif; ?>
</div>

</body>
</html>