<?php
session_start();
require_once 'database/db.php';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare('SELECT is_admin, is_client, is_freelancer FROM User WHERE id = :user_id');
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            switch (true) {
                case $user['is_admin']:
                    header('Location: pages/home/admin_dashboard.php');
                    break;
                case $user['is_freelancer']:
                    header('Location: pages/home/freelancer_homepage.php');
                    break;
                case $user['is_client']:
                    header('Location: pages/home/client_homepage.php');
                    break;
            }
            exit();
        } else {
            header("Location: pages/auth/login.php");
            exit();
        }
    } catch (PDOException $e) {
        die("Error checking user role: " . $e->getMessage());
    }
} else {
    include 'pages/home/welcome.php';
    exit();
}
?>
