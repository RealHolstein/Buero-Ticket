<?php
require 'sessions/session.php';
require 'config/db.php';

// Benutzer-ID aus der Session holen
$user_id = $_SESSION['user_id'];

// Abfrage: Benutzerinformationen
$stmt = $pdo->prepare("SELECT username, email, phone, profile_picture FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$error = '';
$success = '';

// Wenn das Formular abgesendet wurde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $new_password_confirm = $_POST['new_password_confirm'];
    
    // Bild-Upload verarbeiten
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        if (in_array($file_type, $allowed_types)) {
            $file_name = time() . '_' . $_FILES['profile_picture']['name'];
            $file_path = 'uploads/' . $file_name;
            
            // Datei verschieben
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
                // Bildpfad in der Datenbank aktualisieren
                $stmt = $pdo->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $stmt->execute([$file_name, $user_id]);
                $success = 'Profilbild erfolgreich hochgeladen.';
            } else {
                $error = 'Fehler beim Hochladen des Bildes.';
            }
        } else {
            $error = 'Nur JPEG, PNG und GIF Dateien sind erlaubt.';
        }
    }

    // Überprüfen, ob das aktuelle Passwort eingegeben wurde, aber nur wenn das Passwort geändert werden soll
    if (!empty($new_password) || !empty($new_password_confirm)) {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user_password = $stmt->fetchColumn();

        if (!password_verify($current_password, $user_password)) {
            $error = 'Das aktuelle Passwort ist falsch.';
        } elseif ($new_password !== $new_password_confirm) {
            $error = 'Das neue Passwort und die Bestätigung stimmen nicht überein.';
        } else {
            // Passwort aktualisieren
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed_password, $user_id]);
            $success = $success ? $success . ' Passwort erfolgreich geändert.' : 'Passwort erfolgreich geändert.';
        }
    }

    // Benutzerinformationen (außer Passwort) aktualisieren
    if (empty($error)) {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->execute([$username, $email, $phone, $user_id]);
        $success = $success ? $success . ' Profil wurde erfolgreich aktualisiert.' : 'Profil wurde erfolgreich aktualisiert.';
    }
}

include 'includes/header.php';
?>

<div class="container">
    <h2>Profil bearbeiten</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <!-- Profilbild anzeigen -->
    <div class="profile-picture">
        <?php if (!empty($user['profile_picture'])): ?>
            <img src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
        <?php else: ?>
            <img src="uploads/default.png" alt="Standard-Profilbild" style="max-width: 150px; max-height: 150px; border-radius: 50%;">
        <?php endif; ?>
    </div>

    <form action="profile_edit.php" method="POST" enctype="multipart/form-data">
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label for="email">E-Mail:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

        <label for="phone">Telefonnummer:</label>
        <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">

        <!-- Bild-Upload -->
        <label for="profile_picture">Profilbild ändern:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">

        <label for="current_password">Aktuelles Passwort (nur für Passwortänderung erforderlich):</label>
        <input type="password" id="current_password" name="current_password">

        <label for="new_password">Neues Passwort (optional):</label>
        <input type="password" id="new_password" name="new_password">

        <label for="new_password_confirm">Neues Passwort bestätigen (optional):</label>
        <input type="password" id="new_password_confirm" name="new_password_confirm">

        <button type="submit">Speichern</button>
    </form>

    <a href="profile.php">Zurück zum Profil</a>
</div>

</body>
</html>
