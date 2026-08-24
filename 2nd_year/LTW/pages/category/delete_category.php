<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_id'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error_message'] = "Token CSRF inválido.";
    } else {
        $categoryId = (int) $_POST['category_id'];

        $stmt = $pdo->prepare("DELETE FROM Category WHERE id = ?");
        $stmt->execute([$categoryId]);

        $_SESSION['success_message'] = "Categoria eliminada com sucesso.";
    }
}

header("Location: ../home/admin_dashboard.php");
exit;