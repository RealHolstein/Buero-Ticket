<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Ticketsystem</title>
</head>
<body>
    <header>
        <h1>Büro-Ticketsystem by Mark Bothing</h1>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php">Dashboard</a>
            <a href="ticket_create.php">Ticket erstellen</a>
            <a href="profile.php">Profil</a>
            <a href="logout.php">Logout</a>
        <?php endif; ?>
    </header>