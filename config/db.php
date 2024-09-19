<?php
$host = '23.88.58.130';
$db = 'ticketsystem_db';
$user = 'ticketsystem_user';
$pass = 'hj#jAbCoTPzD7*e&lz!7N7u';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
