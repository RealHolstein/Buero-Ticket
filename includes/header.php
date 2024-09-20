<?php
// Session-Start sicherstellen, falls es nicht bereits in der jeweiligen Seite vorhanden ist
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'config/db.php';

// Benutzerinformationen inkl. Profilbild abrufen
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Wenn das Profilbild vorhanden ist, verwenden, sonst Standardbild
    if ($user) {
        $_SESSION['username'] = $user['username']; // Benutzername in Session speichern
        $_SESSION['profile_picture'] = !empty($user['profile_picture']) ? 'uploads/profile_images/' . $user['profile_picture'] : 'uploads/profile_images/default.jpg';
    } else {
        // Falls der Benutzer nicht gefunden wird, Profilbild auf Standard setzen
        $_SESSION['profile_picture'] = 'uploads/profile_images/default.jpg';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ticketsystem</title>
</head>
<body>
    <div class="sidebar">
        <div class="profile-section">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="profile-picture">
                    <!-- Profilbild anzeigen -->
                    <img src="<?php echo htmlspecialchars($_SESSION['profile_picture']); ?>" alt="Profilbild" class="user-image">
                </div>
                <p class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></p>
            <?php endif; ?>
        </div>
        <nav class="sidebar-menu">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="ticket_create.php">Ticket erstellen</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>