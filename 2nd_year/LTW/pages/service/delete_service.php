<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || (!isset($_SESSION['is_admin']) && !isset($_SESSION['is_freelancer']))) {
    header('Location: ../auth/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['service_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "Token CSRF inválido.";
        exit();
    }
    $redirect = !empty($_SESSION['is_freelancer']) ? '../home/freelancer_homepage.php' : '../home/admin_dashboard.php';
    header("Location: $redirect");
    exit();
}

$id = (int) $_POST['service_id'];

if (!empty($_SESSION['is_admin'])) {
    $stmt = $pdo->prepare('SELECT * FROM Service WHERE id = :id');
    $stmt->execute([':id' => $id]);
} elseif (!empty($_SESSION['is_freelancer'])) {
    $stmt = $pdo->prepare('SELECT * FROM Service WHERE id = :id AND user_id = :user_id');
    $stmt->execute([':id' => $id, ':user_id' => $_SESSION['user_id']]);
}
$service = $stmt->fetch();

if (!$service) {
    echo "Serviço não encontrado ou não autorizado.";
    exit();
}

$delete = $pdo->prepare('DELETE FROM Service WHERE id = :id');
$delete->execute([':id' => $id]);

$redirect = !empty($_SESSION['is_freelancer']) ? '../home/freelancer_homepage.php' : '../home/admin_dashboard.php';
header("Location: $redirect");
exit();
?>
