<?php
session_start();
require_once '../database/db.php';

header('Content-Type: application/json');

$sender_id = isset($_GET['sender_id']) ? (int)$_GET['sender_id'] : 0;
$receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;

if ($sender_id > 0 && $receiver_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Inquiry
                           WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
                           ORDER BY sent_at ASC");
    $stmt->execute([$sender_id, $receiver_id, $receiver_id, $sender_id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo json_encode([]);
}
exit();