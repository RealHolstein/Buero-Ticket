<?php
require 'sessions/session.php';
require 'config/db.php';

// Überprüfen, ob der Benutzer ein Admin ist
if ($_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

// Überprüfen, ob der Status in der URL angegeben ist
if (!isset($_GET['status'])) {
    die("Status nicht angegeben.");
}

$status = $_GET['status'];

// Abfrage: Tickets nach Status
$stmt = $pdo->prepare("SELECT * FROM tickets WHERE status = ?");
$stmt->execute([$status]);
$tickets = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container">
    <h2>Tickets mit Status: <?php echo htmlspecialchars($status); ?></h2>

    <?php if (count($tickets) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Ticket ID</th>
                <th>Titel</th>
                <th>Status</th>
                <th>Erstellt von</th>
                <th>Zugewiesen an</th>
                <th>Aktion</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $ticket): ?>
            <tr>
                <td><?php echo htmlspecialchars($ticket['id']); ?></td>
                <td><?php echo htmlspecialchars($ticket['title']); ?></td>
                <td><?php echo htmlspecialchars($ticket['status']); ?></td>
                <td><?php echo htmlspecialchars($ticket['user_id']); ?></td>
                <td><?php echo htmlspecialchars($ticket['assigned_to']); ?></td>
                <td>
                    <a href="ticket.php?id=<?php echo $ticket['id']; ?>">Ansehen</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Keine Tickets mit diesem Status.</p>
    <?php endif; ?>
</div>

</body>
</html>