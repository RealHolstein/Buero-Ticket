<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'sessions/session.php';
require 'config/db.php';

// Überprüfen, ob die Ticket-ID in der URL vorhanden ist
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Ticket-ID fehlt. Bitte wählen Sie ein gültiges Ticket.");
}

echo "User Role: " . $_SESSION['role'];

// Ticket-ID aus der URL holen
$ticket_id = (int)$_GET['id']; // Sicherheitshalber casten wir die ID zu einem Integer

// Ticket-Daten abrufen
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ?");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

// Überprüfen, ob das Ticket gefunden wurde
if (!$ticket) {
    die("Das Ticket existiert nicht oder Sie haben keine Berechtigung darauf zuzugreifen.");
}

// Historie abrufen und Benutzernamen einbinden
$history_stmt = $pdo->prepare("
    SELECT th.*, u.username 
    FROM ticket_history th 
    JOIN users u ON th.changed_by = u.id 
    WHERE th.ticket_id = ? 
    ORDER BY th.created_at ASC
");
$history_stmt->execute([$ticket_id]);
$history = $history_stmt->fetchAll();

// Kommentare abrufen
$comment_stmt = $pdo->prepare("
    SELECT c.comment, u.username, c.created_at 
    FROM ticket_comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.ticket_id = ? 
    ORDER BY c.created_at ASC
");
$comment_stmt->execute([$ticket_id]);
$comments = $comment_stmt->fetchAll();

// Benutzer für die Zuweisung und Ersteller-Änderung abrufen (Admin sieht alle Benutzer)
$users_stmt = $pdo->prepare("SELECT id, username, role FROM users");
$users_stmt->execute();
$users = $users_stmt->fetchAll();

// Wenn der Admin das Ticket einer Person zuweist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign'])) {
    $assigned_user_id = $_POST['assigned_user_id'];
    $update_stmt = $pdo->prepare("UPDATE tickets SET assigned_to = ? WHERE id = ?");
    $update_stmt->execute([$assigned_user_id, $ticket_id]);

    // In der Historie protokollieren
    $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
    $history_stmt->execute([$ticket_id, $_SESSION['user_id'], 'Ticket zugewiesen an Benutzer-ID: ' . $assigned_user_id]);

    header("Location: ticket.php?id=$ticket_id");
    exit;
}

// Wenn der Admin den Ersteller des Tickets ändert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_creator'])) {
    $new_creator_id = $_POST['new_creator_id'];
    $update_stmt = $pdo->prepare("UPDATE tickets SET user_id = ? WHERE id = ?");
    $update_stmt->execute([$new_creator_id, $ticket_id]);

    // In der Historie protokollieren
    $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
    $history_stmt->execute([$ticket_id, $_SESSION['user_id'], 'Ticket-Ersteller geändert zu Benutzer-ID: ' . $new_creator_id]);

    header("Location: ticket.php?id=$ticket_id");
    exit;
}

// Status ändern
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $new_status = $_POST['new_status'];
    $update_stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $ticket_id]);

    // In der Historie protokollieren
    $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
    $history_stmt->execute([$ticket_id, $_SESSION['user_id'], 'Ticket-Status geändert zu: ' . $new_status]);

    header("Location: ticket.php?id=$ticket_id");
    exit;
}

// Kommentar hinzufügen (nur Admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = $_POST['comment'];
    $user_id = $_SESSION['user_id']; // Der angemeldete Admin

    $comment_stmt = $pdo->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
    $comment_stmt->execute([$ticket_id, $user_id, $comment]);

    // In der Historie protokollieren
    $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
    $history_stmt->execute([$ticket_id, $user_id, 'Kommentar hinzugefügt']);

    header("Location: ticket.php?id=$ticket_id");
    exit;
}

include 'includes/header.php';
?>

<div class="container">
    <h2>Ticket Details</h2>
    <p><strong>Title:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
    <p><strong>Zugewiesen an:</strong> <?php echo $ticket['assigned_to'] ? htmlspecialchars($ticket['assigned_to']) : 'Nicht zugewiesen'; ?></p>

    <!-- Ticket zuweisen (nur für Administratoren sichtbar) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <h3>Ticket einer Person zuweisen</h3>
    <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
        <select name="assigned_user_id" required>
            <option value="" disabled selected>Bitte wählen Sie einen Benutzer</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']) . ' - Rolle: ' . htmlspecialchars($user['role']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="assign">Ticket zuweisen</button>
    </form>
    <?php endif; ?>

    <h3>Ersteller des Tickets ändern</h3>
    <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
        <select name="new_creator_id" required>
            <option value="" disabled selected>Bitte wählen Sie einen neuen Ersteller</option>
            <?php foreach ($users as $user): ?>
                <option value="<?php echo $user['id']; ?>">
                    <?php echo htmlspecialchars($user['username']) . ' - Rolle: ' . htmlspecialchars($user['role']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="change_creator">Ersteller ändern</button>
    </form>

    <!-- Ticket-Status ändern -->
    <h3>Ticket-Status ändern</h3>
    <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
        <select name="new_status" required>
            <option value="" disabled selected>Bitte wählen Sie einen neuen Status</option>
            <option value="Neu" <?php if ($ticket['status'] === 'Neu') echo 'selected'; ?>>Neu</option>
            <option value="In Arbeit" <?php if ($ticket['status'] === 'In Arbeit') echo 'selected'; ?>>In Arbeit</option>
            <option value="Zurückgestellt" <?php if ($ticket['status'] === 'Zurückgestellt') echo 'selected'; ?>>Zurückgestellt</option>
            <option value="Abgeschlossen" <?php if ($ticket['status'] === 'Abgeschlossen') echo 'selected'; ?>>Abgeschlossen</option>
        </select>
        <button type="submit" name="change_status">Status ändern</button>
    </form>

    <h3>Historie</h3>
    <ul class="history">
        <?php foreach ($history as $entry): ?>
            <li>
                <strong><?php echo date('d.m.Y', strtotime($entry['created_at'])); ?>, <?php echo date('H:i', strtotime($entry['created_at'])); ?> Uhr:</strong> 
                <?php echo htmlspecialchars($entry['action']); ?> (von <?php echo htmlspecialchars($entry['username']); ?>)
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Kommentare</h3>
    <ul class="comments">
        <?php foreach ($comments as $comment): ?>
            <li>
                <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>Uhr
                <?php echo htmlspecialchars($comment['username']); ?>:
                <strong><?php echo htmlspecialchars($comment['comment']); ?> </strong> <!-- Kommentar-Inhalt -->
                
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Kommentarformular (nur für Admins sichtbar) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <h3>Kommentar hinzufügen</h3>
        <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
            <textarea name="comment" required></textarea>
            <button type="submit" name="comment_submit">Kommentar hinzufügen</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>