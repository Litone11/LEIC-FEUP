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

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['category'])
    && isset($_POST['csrf_token'])
    && $_POST['csrf_token'] === $_SESSION['csrf_token']
) {
    $category = trim($_POST['category']);

    $stmt = $pdo->prepare("INSERT INTO Category (name) VALUES (?)");
    $stmt->execute([$category]);

    header("Location: ../home/admin_dashboard.php");
    exit;
}
?>