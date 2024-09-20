<?php
require 'sessions/session.php';
require 'config/db.php';

include 'includes/header.php';

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

// Abfrage: Benutzerinformationen inkl. Profilbild
$stmt = $pdo->prepare("SELECT username, email, phone, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

?>

<div class="container">
    <h2>Profil</h2>

    <!-- Profilbild anzeigen, falls vorhanden -->
    <div class="profile-picture">
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
        <?php else: ?>
            <img src="uploads/default.png" alt="Standard-Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
        <?php endif; ?>
    </div>

    <p><strong>Benutzername:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
    <p><strong>E-Mail:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Telefonnummer:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>

    <a href="profile_edit.php" class="button">Profil bearbeiten</a>
</div>

</body>
</html>