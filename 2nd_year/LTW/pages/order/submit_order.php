<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Token CSRF inválido.";
        exit();
    }

    $serviceId = isset($_POST['service_id']) ? (int)$_POST['service_id'] : null;
    $message = trim($_POST['message'] ?? '');
    $clientId = $_SESSION['user_id'];
    $freelancerId = isset($_POST['freelancer_id']) ? (int)$_POST['freelancer_id'] : null;

    
    $stmt = $pdo->prepare("SELECT * FROM Service WHERE id = ?");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        echo "Serviço não encontrado.";
        exit();
    }

    
    $stmt = $pdo->prepare("INSERT INTO ServiceTransaction (client_id, service_id, status, created_at) VALUES (?, ?, 'pending', datetime('now'))");
    $stmt->execute([$clientId, $serviceId]);

    
    if (!empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO Inquiry (sender_id, receiver_id, service_id, content, is_custom_offer, sent_at) VALUES (?, ?, ?, ?, 0, datetime('now'))");
        $stmt->execute([$clientId, $freelancerId, $serviceId, $message]);
    }

    header("Location: ../home/client_homepage.php");
    exit();
}

header("Location: ../order/order_service.php");
exit();
