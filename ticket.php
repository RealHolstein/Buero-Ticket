<?php
require 'sessions/session.php';
require 'config/db.php';

// Überprüfen, ob die Ticket-ID in der URL vorhanden ist
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Ticket-ID fehlt. Bitte wählen Sie ein gültiges Ticket.");
}

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

// Historie abrufen
$history_stmt = $pdo->prepare("SELECT * FROM ticket_history WHERE ticket_id = ? ORDER BY created_at ASC");
$history_stmt->execute([$ticket_id]);
$history = $history_stmt->fetchAll();

// Kommentare abrufen
$comment_stmt = $pdo->prepare("SELECT c.comment, u.username, c.created_at FROM ticket_comments c JOIN users u ON c.user_id = u.id WHERE c.ticket_id = ? ORDER BY c.created_at ASC");
$comment_stmt->execute([$ticket_id]);
$comments = $comment_stmt->fetchAll();

// Benutzer für die Zuweisung abrufen (Admin sieht alle Benutzer)
$users_stmt = $pdo->prepare("SELECT id, username FROM users WHERE role = 'user'");
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
            <select name="assigned_user_id">
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="assign">Ticket zuweisen</button>
        </form>

        <h3>Ersteller des Tickets ändern</h3>
        <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
            <select name="new_creator_id">
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" name="change_creator">Ersteller ändern</button>
        </form>
    <?php endif; ?>

    <h3>Historie</h3>
    <ul class="history">
        <?php foreach ($history as $entry): ?>
            <li><strong><?php echo $entry['created_at']; ?>:</strong> <?php echo htmlspecialchars($entry['action']); ?> (von Benutzer-ID <?php echo $entry['changed_by']; ?>)</li>
        <?php endforeach; ?>
    </ul>

    <h3>Kommentare</h3>
    <ul class="comments">
        <?php foreach ($comments as $comment): ?>
            <li><strong><?php echo htmlspecialchars($comment['username']); ?>:</strong> <?php echo htmlspecialchars($comment['comment']); ?> (<?php echo $comment['created_at']; ?>)</li>
        <?php endforeach; ?>
    </ul>

    <!-- Kommentarformular (nur für Admins sichtbar) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
            <textarea name="comment" required></textarea>
            <button type="submit" name="comment">Kommentar hinzufügen</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>