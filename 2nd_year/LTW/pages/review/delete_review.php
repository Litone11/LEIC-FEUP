<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: ../../index.php");
        exit;
    }

    $reviewId = (int) $_POST['review_id'];

    $stmt = $pdo->prepare("DELETE FROM Review WHERE id = ?");
    $stmt->execute([$reviewId]);

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
} else {
    header("Location: ../../index.php");
    exit;
}
?>