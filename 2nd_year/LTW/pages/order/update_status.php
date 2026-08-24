<?php
session_start();
require_once '../../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Token CSRF inválido.";
        exit();
    }
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $transactionId = $_POST['transaction_id'] ?? null;
    $action = $_POST['action'] ?? '';

    if ($transactionId && in_array($action, ['accept', 'reject'])) {
        if ($action === 'accept') {
            $stmt = $pdo->prepare("UPDATE ServiceTransaction SET status = 'accepted' WHERE id = ?");
            $stmt->execute([$transactionId]);
            header("Location: in_progress.php");
            exit();
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare("DELETE FROM ServiceTransaction WHERE id = ?");
            $stmt->execute([$transactionId]);
            header("Location: deliveries.php");
            exit();
        }
    }
}

header("Location: deliveries.php");
exit();
