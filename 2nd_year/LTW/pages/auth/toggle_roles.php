<?php
session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Location: login.php');
    exit();
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: login.php');
    exit();
}

$role = $_POST['role'] ?? '';

$isClient = $role === 'client' ? 1 : 0;
$isFreelancer = $role === 'freelancer' ? 1 : 0;

$stmt = $pdo->prepare("UPDATE User SET is_client = ?, is_freelancer = ? WHERE id = ?");
$stmt->execute([$isClient, $isFreelancer, $_SESSION['user_id']]);

$_SESSION['is_client'] = $isClient;
$_SESSION['is_freelancer'] = $isFreelancer;

header('Location: ../home/admin_dashboard.php');
exit();
