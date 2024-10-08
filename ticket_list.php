<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzerinformationen inkl. Profilbild und Username erneut abrufen
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Profilbild in der Session speichern, falls noch nicht vorhanden
if (!isset($_SESSION['profile_picture'])) {
    $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? 'uploads/profile_images/' . $user['profile_picture'] : 'uploads/profile_images/default.jpg';
}
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = $user['username'];
}

include 'includes/header.php';

// Überprüfen, ob der Benutzer ein Admin ist
if ($_SESSION['role'] !== 'admin') {
    die("Zugriff verweigert.");
}

// Überprüfen, ob der Status in der URL angegeben ist
if (!isset($_GET['status'])) {
    die("Status nicht angegeben.");
}

$status = $_GET['status'];

// Abfrage: Tickets nach Status, inklusive Namen der Ersteller und zugewiesenen Benutzer
$stmt = $pdo->prepare("
    SELECT tickets.id, tickets.title, tickets.status, 
           creator.username AS creator_name, 
           assignee.username AS assigned_to_name 
    FROM tickets 
    JOIN users AS creator ON tickets.user_id = creator.id 
    LEFT JOIN users AS assignee ON tickets.assigned_to = assignee.id 
    WHERE tickets.status = ?
");
$stmt->execute([$status]);
$tickets = $stmt->fetchAll();

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
                <td><?php echo htmlspecialchars($ticket['creator_name']); ?></td>
                <td><?php echo htmlspecialchars($ticket['assigned_to_name']) ?: 'Nicht zugewiesen'; ?></td> <!-- Überprüfen, ob zugewiesen -->
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