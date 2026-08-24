<?php
session_start();
require_once '../database/db.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$sender_id = (int)($data['sender_id'] ?? 0);
$receiver_id = (int)($data['receiver_id'] ?? 0);
$message = trim($data['message'] ?? '');

if ($sender_id > 0 && $receiver_id > 0 && $message !== '') {
    $stmt = $pdo->prepare("INSERT INTO Inquiry (sender_id, receiver_id, content, sent_at)
                           VALUES (?, ?, ?, datetime('now'))");
    $stmt->execute([$sender_id, $receiver_id, $message]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Dados inválidos']);
}
exit();