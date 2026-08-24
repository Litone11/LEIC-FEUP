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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: ../home/admin_dashboard.php");
        exit;
    }
    $username = trim($_POST['username']);

    $stmt = $pdo->prepare("UPDATE User SET is_admin = 1 WHERE username = ?");
    $stmt->execute([$username]);

    header("Location: ../home/admin_dashboard.php");
    exit;
}
?>