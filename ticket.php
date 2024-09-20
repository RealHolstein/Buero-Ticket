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

include 'includes/header.php';

// Ticket-ID aus der URL holen
$ticket_id = (int)$_GET['id']; // Sicherheitshalber casten wir die ID zu einem Integer

// Kommentar hinzufügen (für Hauptseite und Popup)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['comment_submit']) || isset($_POST['comment_submit_popup'])) {
        $comment = isset($_POST['comment']) ? $_POST['comment'] : $_POST['comment_popup'];
        $user_id = $_SESSION['user_id']; // Angemeldeter Benutzer

        // Speichern des Kommentars in der Datenbank
        $comment_stmt = $pdo->prepare("INSERT INTO ticket_comments (ticket_id, user_id, comment) VALUES (?, ?, ?)");
        $comment_stmt->execute([$ticket_id, $user_id, htmlspecialchars($comment)]);

        // Weiterleitung, um doppeltes Senden zu verhindern
        header("Location: ticket.php?id=$ticket_id");
        exit;
    }

    // Ticket zuweisen oder Ersteller ändern
    if (isset($_POST['assign_ticket'])) {
        $assigned_user_id = $_POST['assigned_user_id'];
        $update_stmt = $pdo->prepare("UPDATE tickets SET assigned_to = ? WHERE id = ?");
        $update_stmt->execute([$assigned_user_id, $ticket_id]);

        // Historie aktualisieren
        $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
        $history_stmt->execute([$ticket_id, $_SESSION['user_id'], 'Ticket zugewiesen an Benutzer-ID: ' . $assigned_user_id]);

        header("Location: ticket.php?id=$ticket_id");
        exit;
    }

    if (isset($_POST['change_creator'])) {
        $new_creator_id = $_POST['new_creator_id'];
        $update_stmt = $pdo->prepare("UPDATE tickets SET user_id = ? WHERE id = ?");
        $update_stmt->execute([$new_creator_id, $ticket_id]);

        // Historie aktualisieren
        $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, action) VALUES (?, ?, ?)");
        $history_stmt->execute([$ticket_id, $_SESSION['user_id'], 'Ticket-Ersteller geändert zu Benutzer-ID: ' . $new_creator_id]);

        header("Location: ticket.php?id=$ticket_id");
        exit;
    }
}

// Ticket-Daten abrufen, inkl. Benutzernamen für Ersteller, Beschreibung und Zuweisung
$stmt = $pdo->prepare("
    SELECT tickets.*, 
           creator.username AS creator_name, 
           assignee.username AS assigned_to_name 
    FROM tickets 
    JOIN users AS creator ON tickets.user_id = creator.id 
    LEFT JOIN users AS assignee ON tickets.assigned_to = assignee.id 
    WHERE tickets.id = ?
");
$stmt->execute([$ticket_id]);
$ticket = $stmt->fetch();

// Überprüfen, ob das Ticket gefunden wurde
if (!$ticket) {
    die("Das Ticket existiert nicht oder Sie haben keine Berechtigung darauf zuzugreifen.");
}

// Historie abrufen und Benutzernamen einbinden
$history_stmt = $pdo->prepare("
    SELECT th.*, u.username, u.role 
    FROM ticket_history th 
    JOIN users u ON th.changed_by = u.id 
    WHERE th.ticket_id = ? 
    ORDER BY th.created_at DESC
");
$history_stmt->execute([$ticket_id]);
$history = $history_stmt->fetchAll();

// Kommentare abrufen
$comment_stmt = $pdo->prepare("
    SELECT c.comment, u.username, u.role, c.created_at 
    FROM ticket_comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.ticket_id = ? 
    ORDER BY c.created_at DESC
");
$comment_stmt->execute([$ticket_id]);
$comments = $comment_stmt->fetchAll();

// Benutzer für die Zuweisung und Ersteller-Änderung abrufen
$users_stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE role = 'admin'");
$users_stmt->execute();
$users = $users_stmt->fetchAll();
?>

<div class="container">
    <h2>Ticket Details</h2>
    <p><strong>Titel:</strong> <?php echo htmlspecialchars($ticket['title']); ?></p>
    <p><strong>Beschreibung:</strong> <?php echo nl2br(htmlspecialchars($ticket['description'])); ?></p> <!-- Anzeige der Beschreibung -->
    <p><strong>Status:</strong> <?php echo htmlspecialchars($ticket['status']); ?></p>
    <p><strong>Erstellt von:</strong> <?php echo htmlspecialchars($ticket['creator_name']); ?></p>
    <p><strong>Zugewiesen an:</strong> <?php echo $ticket['assigned_to_name'] ? htmlspecialchars($ticket['assigned_to_name']) : 'Nicht zugewiesen'; ?></p>

    <!-- Ticket zuweisen und Ersteller ändern (eingeklappt) -->
    <?php if ($_SESSION['role'] === 'admin'): ?>
    <div>
        <button onclick="toggleSection('assign-section')">Ticket zuweisen &#9662;</button>
        <div id="assign-section" style="display:none; margin-top: 10px;">
            <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                <label for="assigned_user_id">Zuweisen an:</label>
                <select name="assigned_user_id" required>
                    <option value="" disabled selected>Bitte wählen Sie einen Benutzer</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="assign_ticket">Zuweisen</button>
            </form>
        </div>

        <button onclick="toggleSection('creator-section')">Ersteller ändern &#9662;</button>
        <div id="creator-section" style="display:none; margin-top: 10px;">
            <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
                <label for="new_creator_id">Neuer Ersteller:</label>
                <select name="new_creator_id" required>
                    <option value="" disabled selected>Bitte wählen Sie einen Benutzer</option>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="change_creator">Ersteller ändern</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <h3>Historie</h3>
    <ul class="history scrollable-container">
        <?php 
        foreach ($history as $entry): 
            $class = $entry['role'] === 'admin' ? 'admin-entry' : 'user-entry';
        ?>
            <li class="<?php echo $class; ?>" style="margin-bottom: 5px;">
                <strong><?php echo date('d.m.Y', strtotime($entry['created_at'])); ?>, <?php echo date('H:i', strtotime($entry['created_at'])); ?> Uhr:</strong> 
                <?php echo htmlspecialchars($entry['action']); ?> (von <?php echo htmlspecialchars($entry['username']); ?>)
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Kommentare</h3>
    <ul class="comments scrollable-container">
        <?php 
        foreach ($comments as $comment): 
            $class = $comment['role'] === 'admin' ? 'admin-entry' : 'user-entry';
        ?>
            <li class="<?php echo $class; ?>" style="margin-bottom: 5px; max-height: 60px; overflow: hidden;">
                <strong><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?> Uhr</strong>
                <?php echo htmlspecialchars($comment['username']); ?>:
                <span class="comment-text">
                    <?php echo strlen($comment['comment']) > 100 ? substr(htmlspecialchars($comment['comment']), 0, 100) . '...' : htmlspecialchars($comment['comment']); ?>
                </span>
                <?php if (strlen($comment['comment']) > 100): ?>
                    <a href="#" class="expand-comment" data-comment="<?php echo htmlspecialchars($comment['comment']); ?>">Mehr anzeigen</a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Kommentarformular (für Benutzer und Admins) -->
    <h3>Kommentar hinzufügen</h3>
    <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
        <textarea name="comment" class="comment-textarea-main" required></textarea>
        <button type="submit" name="comment_submit">Kommentar hinzufügen</button>
    </form>
</div>

<!-- Modal Popup für Kommentare -->
<div id="commentModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Kommentar</h3>
        <p id="fullComment"></p>
        <h3>Antwort hinzufügen</h3>
        <form action="ticket.php?id=<?php echo $ticket_id; ?>" method="POST">
            <textarea name="comment_popup" class="comment-textarea-popup" required></textarea>
            <button type="submit" name="comment_submit_popup">Kommentar hinzufügen</button>
        </form>
    </div>
</div>

<!-- Einbinden von TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '.comment-textarea-main, .comment-textarea-popup',  // Wendet TinyMCE auf beide Textareas an
        height: 150,  // Höhe des Texteditors
        menubar: true,  // Kein Menü
        plugins: 'lists link',  // Aktiviert grundlegende Plugins
        toolbar: 'undo redo | bold italic underline | bullist numlist | link',  // Toolbar mit Formatierungsoptionen
        branding: false  // Entfernt das Branding von TinyMCE
    });

    // Modal Popup öffnen
    var modal = document.getElementById("commentModal");
    var span = document.getElementsByClassName("close")[0];

    // Wenn auf "Mehr anzeigen" geklickt wird, öffnet das Modal und zeigt den vollständigen Kommentar
    document.querySelectorAll('.expand-comment').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var fullComment = this.getAttribute('data-comment');
            document.getElementById("fullComment").innerText = fullComment;
            modal.style.display = "block";
        });
    });

    // Schließt das Modal, wenn auf das "X" geklickt wird
    span.onclick = function() {
        modal.style.display = "none";
    }

    // Schließt das Modal, wenn außerhalb des Modals geklickt wird
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Ein-/Ausblenden der Ticketzuweisung und Erstelleränderung
    function toggleSection(sectionId) {
        var section = document.getElementById(sectionId);
        section.style.display = (section.style.display === 'none' || section.style.display === '') ? 'block' : 'none';
    }
</script>   

</body>
</html>