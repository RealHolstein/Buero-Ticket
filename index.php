<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID und Rolle aus der Session holen
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

// Abfrage: Benutzerinformationen inkl. Profilbild
$stmt = $pdo->prepare("SELECT username, email, phone, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

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

    // Letzte Aktivitäten abrufen
    $stmt_recent = $pdo->prepare("SELECT id, title, status, updated_at FROM tickets ORDER BY updated_at DESC LIMIT 5");
    $stmt_recent->execute();
    $recent_tickets = $stmt_recent->fetchAll();

    // Tickets nach Priorität
    $stmt_priority_high = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE priority = 'Hoch'");
    $stmt_priority_high->execute();
    $high_priority_tickets = $stmt_priority_high->fetchColumn();

    $stmt_priority_medium = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE priority = 'Mittel'");
    $stmt_priority_medium->execute();
    $medium_priority_tickets = $stmt_priority_medium->fetchColumn();

    $stmt_priority_low = $pdo->prepare("SELECT COUNT(*) AS total FROM tickets WHERE priority = 'Niedrig'");
    $stmt_priority_low->execute();
    $low_priority_tickets = $stmt_priority_low->fetchColumn();
}

include 'includes/header.php';
?>

<div class="container">
    <div class="sidebar">
        <div class="profile-section">
            <!-- Profilbild anzeigen, falls vorhanden -->
            <div class="profile-picture">
                <?php if (!empty($user['profile_picture'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                <?php else: ?>
            <img src="uploads/default.png" alt="Standard-Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
                    <?php endif; ?>
        </div>
            <div class="username">
                <!-- Überprüfung, ob der Benutzername gesetzt ist -->
                <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Unbekannter Benutzer'; ?>
            </div>
        </div>

        <!-- Seitenleiste mit Menüpunkten -->
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="ticket_create.php">Ticket erstellen</a></li>
                <li><a href="ticket_list.php?status=Neu">Tickets ansehen</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </div>

    <div class="content">
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

        <!-- Übersicht nach Priorität -->
        <div class="ticket-summary">
            <h3>Tickets nach Priorität</h3>
            <ul>
                <li>Hoch: <?php echo $high_priority_tickets; ?> Tickets</li>
                <li>Mittel: <?php echo $medium_priority_tickets; ?> Tickets</li>
                <li>Niedrig: <?php echo $low_priority_tickets; ?> Tickets</li>
            </ul>
        </div>

        <!-- Letzte Aktivitäten -->
        <div class="ticket-summary">
            <h3>Letzte Aktivitäten</h3>
            <ul>
                <?php foreach ($recent_tickets as $ticket): ?>
                <li>
                    <a href="ticket_view.php?id=<?php echo $ticket['id']; ?>">
                        #<?php echo $ticket['id']; ?> - <?php echo htmlspecialchars($ticket['title']); ?>
                    </a> - Status: <?php echo htmlspecialchars($ticket['status']); ?> 
                    (Zuletzt aktualisiert am <?php echo date('d.m.Y H:i', strtotime($ticket['updated_at'])); ?>)
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>