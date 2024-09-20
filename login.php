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
        // Benutzer-ID, Benutzername und Benutzerrolle in der Session speichern
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username']; // Benutzername in die Session setzen
        $_SESSION['role'] = $user['role']; // Benutzerrolle in die Session setzen

        // Weiterleiten zur Startseite (Dashboard)
        header("Location: index.php");
        exit;
    } else {
        // Fehlermeldung bei falschem Benutzernamen oder Passwort
        $error = "Falscher Benutzername oder Passwort!";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="container">
    <h2>Login</h2>
    <form action="login.php" method="POST">
        <label for="username">Benutzername:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">Passwort:</label>
        <input type="password" id="password" name="password" required>
        
        <button type="submit">Login</button>
    </form>

    <p>Noch kein Konto? <a href="register.php">Hier registrieren</a></p>

    <!-- Anzeige der Fehlermeldung bei falschen Login-Daten -->
    <?php if (isset($error)): ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>
</div>

</body>
</html>