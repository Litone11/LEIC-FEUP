<?php // includes/footer.php ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <footer>
    <p>&copy; 2025 SkillSwap</p>
  </footer>

