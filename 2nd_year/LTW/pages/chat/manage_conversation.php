<?php
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header("Location: inbox.php");
    exit();
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$loggedInUser = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';
$targetUser = isset($_POST['user_id']) ? (int)$_POST['user_id'] : null;

if (!$targetUser || !in_array($action, ['delete', 'archive', 'unarchive'])) {
    header("Location: inbox.php");
    exit();
}

if ($action === 'archive') {
    $stmt = $pdo->prepare("UPDATE Inquiry SET archived_by = :archiver
                          WHERE (sender_id = :user1 AND receiver_id = :user2)
                          OR (sender_id = :user2 AND receiver_id = :user1)");
    $stmt->execute([
        ':archiver' => $loggedInUser,
        ':user1' => $loggedInUser,
        ':user2' => $targetUser
    ]);
}

if ($action === 'unarchive') {
    $stmt = $pdo->prepare("UPDATE Inquiry SET archived_by = NULL
                          WHERE ((sender_id = :user1 AND receiver_id = :user2)
                          OR (sender_id = :user2 AND receiver_id = :user1))");
    $stmt->execute([
        ':user1' => $loggedInUser,
        ':user2' => $targetUser
    ]);
}

if ($action === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM Inquiry
                          WHERE (sender_id = :user1 AND receiver_id = :user2)
                          OR (sender_id = :user2 AND receiver_id = :user1)");
    $stmt->execute([
        ':user1' => $loggedInUser,
        ':user2' => $targetUser
    ]);
}

header("Location: inbox.php");
exit();