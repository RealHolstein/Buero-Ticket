<?php
require 'sessions/session.php';
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $new_status = $_POST['status'];
    $user_id = $_SESSION['user_id'];

    // Alten Status abrufen
    $stmt = $pdo->prepare("SELECT status FROM tickets WHERE id = ?");
    $stmt->execute([$ticket_id]);
    $old_status = $stmt->fetchColumn();

    // Status aktualisieren
    $update_stmt = $pdo->prepare("UPDATE tickets SET status = ? WHERE id = ?");
    $update_stmt->execute([$new_status, $ticket_id]);

    // Historie aktualisieren
    $history_stmt = $pdo->prepare("INSERT INTO ticket_history (ticket_id, changed_by, old_status, new_status, action) VALUES (?, ?, ?, ?, ?)");
    $history_stmt->execute([$ticket_id, $user_id, $old_status, $new_status, 'Status geändert']);
    
    header("Location: ticket.php?id=$ticket_id");  // Zurück zur Ticket-Detailseite
    exit;
}