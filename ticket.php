<?php
require 'sessions/session.php';
require 'config/db.php';

// Ticket-ID aus der URL holen
$ticket_id = $_GET['id'];

// Ticket-Daten abrufen
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE id = ? AND user_id = ?");
$stmt->execute([$ticket_id, $_SESSION['user_id']]);
$ticket = $stmt->fetch();

// Historie abrufen
$history_stmt = $pdo->prepare("SELECT * FROM ticket_history WHERE ticket_id = ? ORDER BY created_at ASC");
$history_stmt->execute([$ticket_id]);
$history = $history_stmt->fetchAll();
?>

<h2>Ticket Details</h2>
<p><strong>Title:</strong> <?php echo $ticket['title']; ?></p>
<p><strong>Status:</strong> <?php echo $ticket['status']; ?></p>

<!-- Formular zum Ändern des Status (nur für Administratoren oder berechtigte Benutzer sichtbar) -->
<?php if ($_SESSION['role'] === 'admin'): ?>
    <h3>Status ändern</h3>
    <form action="change_status.php" method="POST">
        <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
        <select name="status">
            <option value="Neu" <?php if ($ticket['status'] === 'Neu') echo 'selected'; ?>>Neu</option>
            <option value="In Arbeit" <?php if ($ticket['status'] === 'In Arbeit') echo 'selected'; ?>>In Arbeit</option>
            <option value="Zurückgestellt" <?php if ($ticket['status'] === 'Zurückgestellt') echo 'selected'; ?>>Zurückgestellt</option>
            <option value="Abgeschlossen" <?php if ($ticket['status'] === 'Abgeschlossen') echo 'selected'; ?>>Abgeschlossen</option>
        </select>
        <button type="submit">Status ändern</button>
    </form>
<?php endif; ?>

<h3>Historie</h3>
<ul>
    <?php foreach ($history as $entry): ?>
        <li><?php echo $entry['created_at']; ?> - <?php echo $entry['action']; ?> (von Benutzer-ID: <?php echo $entry['changed_by']; ?>)</li>
    <?php endforeach; ?>
</ul>

</body>
</html>