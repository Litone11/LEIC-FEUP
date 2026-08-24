<?php
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'error' => 'Token CSRF inválido']);
    exit();
}
require_once '../database/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    echo json_encode(['success' => false, 'error' => 'Não autorizado']);
    exit();
}

$client_id = $_SESSION['user_id'];
$order_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($order_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID inválido']);
    exit();
}

$stmt = $pdo->prepare("SELECT id FROM ServiceTransaction WHERE id = ? AND client_id = ?");
$stmt->execute([$order_id, $client_id]);

if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Encomenda não encontrada ou não pertence ao cliente']);
    exit();
}

$stmt = $pdo->prepare("UPDATE ServiceTransaction SET status = 'received' WHERE id = ?");
$stmt->execute([$order_id]);

echo json_encode(['success' => true]);
exit();