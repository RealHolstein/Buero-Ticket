<?php
require 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Benutzer mit dem angegebenen Benutzernamen abrufen
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Überprüfen, ob Benutzer gefunden wurde und Passwort korrekt ist
    if ($user && password_verify($password, $user['password'])) {
        // Benutzer-ID und Benutzerrolle in der Session speichern
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role']; // Die Rolle des Benutzers in die Session setzen

        // Weiterleiten zur Startseite
        header("Location: index.php");
        exit;
    } else {
        // Fehlermeldung bei falschem Benutzernamen oder Passwort
        $error = "Falscher Benutzername oder Passwort!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<h2>Login</h2>
<form action="login.php" method="POST">
    <label for="username">Benutzername:</label>
    <input type="text" id="username" name="username" required>
    
    <label for="password">Passwort:</label>
    <input type="password" id="password" name="password" required>
    
    <button type="submit">Login</button>
</form>

<p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p>

<?php if (isset($error)): ?>
    <p><?php echo $error; ?></p>
<?php endif; ?>
</body>
</html>