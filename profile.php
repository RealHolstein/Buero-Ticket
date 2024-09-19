<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

// Abfrage: Benutzerinformationen
$stmt = $pdo->prepare("SELECT username, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

include 'includes/header.php';
?>

<div class="container">
    <h2>Profil</h2>
    <p><strong>Benutzername:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>E-Mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Telefonnummer:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

    <a href="profile_edit.php" class="button">Profil bearbeiten</a>
</div>

</body>
</html>