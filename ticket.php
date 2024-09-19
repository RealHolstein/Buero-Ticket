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

// Kommentare abrufen
$comment_stmt = $pdo->prepare("SELECT c.comment, u.username, c.created_at FROM ticket_comments c JOIN users u ON c.user_id = u.id WHERE ticket_id = ? ORDER BY c.created_at ASC");
$comment_stmt->execute([$ticket_id]);
$comments = $comment_stmt->fetchAll();
?>

<h2>Ticket Details</h2>
<p><strong>Title:</strong> <?php echo $ticket['title']; ?></p>
<p><strong>Status:</strong> <?php echo $ticket['status']; ?></p>

<?php if ($ticket['assigned_to'] && $_SESSION['role'] === 'admin'): ?>
    <p><strong>Zugewiesen an:</strong> <?php echo $ticket['assigned_to']; ?></p>
<?php endif; ?>

<h3>Historie</h3>
<ul>
    <?php foreach ($history as $entry): ?>
        <li><?php echo $entry['created_at']; ?> - <?php echo $entry['action']; ?></li>
    <?php endforeach; ?>
</ul>

<h3>Kommentare</h3>
<ul>
    <?php foreach ($comments as $comment): ?>
        <li><strong><?php echo $comment['username']; ?>:</strong> <?php echo $comment['comment']; ?> (<?php echo $comment['created_at']; ?>)</li>
    <?php endforeach; ?>
</ul>

<!-- Kommentarformular -->
<form action="add_comment.php" method="POST">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
    <textarea name="comment" required></textarea>
    <button type="submit">Kommentar hinzufügen</button>
</form>