<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: ../home/admin_dashboard.php");
        exit;
    }

    $requestId = (int) $_POST['request_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        $stmt = $pdo->prepare("SELECT name FROM CategoryRequest WHERE id = ?");
        $stmt->execute([$requestId]);
        $name = $stmt->fetchColumn();

        if ($name) {
            $pdo->prepare("INSERT INTO Category (name) VALUES (?)")->execute([$name]);
        }

        $pdo->prepare("UPDATE CategoryRequest SET status = 'accepted' WHERE id = ?")->execute([$requestId]);

    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE CategoryRequest SET status = 'rejected' WHERE id = ?")->execute([$requestId]);
    }

    header("Location: ../home/admin_dashboard.php");
    exit;
}

header("Location: ../../index.php");
exit;
?>