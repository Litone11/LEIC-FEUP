

<?php
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo "Token CSRF inválido.";
    exit;
}
require_once '../../database/db.php';

if (!isset($_POST['transaction_id'], $_POST['rating']) || !isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    header('Location: ../index.php');
    exit;
}

$transactionId = $_POST['transaction_id'];
$rating = intval($_POST['rating']);
$comment = trim($_POST['comment'] ?? '');
$userId = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT t.client_id, t.status FROM ServiceTransaction t WHERE t.id = ?");
$stmt->execute([$transactionId]);
$transaction = $stmt->fetch();

if (!$transaction || $transaction['client_id'] != $userId || $transaction['status'] !== 'received') {
    echo "Acesso negado ou encomenda inválida.";
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM Review WHERE transaction_id = ?");
$stmt->execute([$transactionId]);
if ($stmt->fetch()) {
    echo "Já existe uma avaliação para esta encomenda.";
    exit;
}


$stmt = $pdo->prepare("INSERT INTO Review (transaction_id, rating, comment) VALUES (?, ?, ?)");
$stmt->execute([$transactionId, $rating, $comment]);


header("Location: ../order/client_orders.php");
exit;
?>