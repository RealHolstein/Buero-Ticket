<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

// Tickets nach Status gruppieren (Neu, In Arbeit, Zurückgestellt, Abgeschlossen)
$stmt_new = $pdo->prepare("SELECT id, title FROM tickets WHERE user_id = ? AND status = 'Neu'");
$stmt_new->execute([$user_id]);
$tickets_new = $stmt_new->fetchAll();

$stmt_in_progress = $pdo->prepare("SELECT id, title FROM tickets WHERE user_id = ? AND status = 'In Arbeit'");
$stmt_in_progress->execute([$user_id]);
$tickets_in_progress = $stmt_in_progress->fetchAll();

$stmt_on_hold = $pdo->prepare("SELECT id, title FROM tickets WHERE user_id = ? AND status = 'Zurückgestellt'");
$stmt_on_hold->execute([$user_id]);
$tickets_on_hold = $stmt_on_hold->fetchAll();

$stmt_closed = $pdo->prepare("SELECT id, title FROM tickets WHERE user_id = ? AND status = 'Abgeschlossen'");
$stmt_closed->execute([$user_id]);
$tickets_closed = $stmt_closed->fetchAll();

include 'includes/header.php';
?>

<div class="container">
    <h2>Meine Tickets</h2>

    <!-- Kategorie: Neue Tickets -->
    <h3>Neu</h3>
    <ul class="ticket-list">
        <?php foreach ($tickets_new as $ticket): ?>
            <li><a href="ticket.php?id=<?php echo $ticket['id']; ?>"><?php echo $ticket['title']; ?></a></li>
        <?php endforeach; ?>
        <?php if (empty($tickets_new)): ?>
            <p>Keine neuen Tickets vorhanden.</p>
        <?php endif; ?>
    </ul>

    <!-- Kategorie: In Arbeit -->
    <h3>In Arbeit</h3>
    <ul class="ticket-list">
        <?php foreach ($tickets_in_progress as $ticket): ?>
            <li><a href="ticket.php?id=<?php echo $ticket['id']; ?>"><?php echo $ticket['title']; ?></a></li>
        <?php endforeach; ?>
        <?php if (empty($tickets_in_progress)): ?>
            <p>Keine Tickets in Arbeit.</p>
        <?php endif; ?>
    </ul>

    <!-- Kategorie: Zurückgestellt -->
    <h3>Zurückgestellt</h3>
    <ul class="ticket-list">
        <?php foreach ($tickets_on_hold as $ticket): ?>
            <li><a href="ticket.php?id=<?php echo $ticket['id']; ?>"><?php echo $ticket['title']; ?></a></li>
        <?php endforeach; ?>
        <?php if (empty($tickets_on_hold)): ?>
            <p>Keine zurückgestellten Tickets vorhanden.</p>
        <?php endif; ?>
    </ul>

    <!-- Kategorie: Abgeschlossen -->
    <h3>Abgeschlossen</h3>
    <ul class="ticket-list">
        <?php foreach ($tickets_closed as $ticket): ?>
            <li><a href="ticket.php?id=<?php echo $ticket['id']; ?>"><?php echo $ticket['title']; ?></a></li>
        <?php endforeach; ?>
        <?php if (empty($tickets_closed)): ?>
            <p>Keine abgeschlossenen Tickets vorhanden.</p>
        <?php endif; ?>
    </ul>
</div>

</body>
</html>