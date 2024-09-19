<?php
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $passwordConfirm = $_POST['password_confirm'];

    // Überprüfen, ob beide Passwörter übereinstimmen
    if ($password !== $passwordConfirm) {
        $error = "Die Passwörter stimmen nicht überein!";
    } else {
        // Hashen des Passworts
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Überprüfen, ob der Benutzername bereits existiert
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            $error = "Der Benutzername ist bereits vergeben!";
        } else {
            // Benutzer in die Datenbank einfügen
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            if ($stmt->execute([$username, $hashedPassword])) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Fehler bei der Registrierung!";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Registrieren</h2>
<form action="register.php" method="POST">
    <label for="username">Benutzername:</label>
    <input type="text" id="username" name="username" required>

    <label for="password">Passwort:</label>
    <input type="password" id="password" name="password" required>

    <label for="password_confirm">Passwort bestätigen:</label>
    <input type="password" id="password_confirm" name="password_confirm" required>

    <button type="submit">Registrieren</button>
</form>

<?php if (isset($error)): ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>

</body>
</html>