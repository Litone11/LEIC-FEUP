<?php // header.php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/css/style.css">
  <title>SkillSwap</title>
</head>
</html>


<?php
if (!isset($_SESSION['user_id'])) {
  ?>
  <header>
    <div class="logo">
      <a href="/index.php">
        <img src="/img/image1.png" alt="SkillSwap Logo">
      </a>
    </div>
    <div class="search-bar">
      <input type="text" placeholder="Search for services...">
    </div>
    <div class="auth-buttons">
      <a href="/pages/auth/login.php">Login</a>
      <a href="/pages/auth/register.php">Register</a>
    </div>
  </header>
  <?php
} else {
  if (!empty($_SESSION['is_freelancer'])) {
    include 'header_freelancer.php';
  } elseif (!empty($_SESSION['is_client'])) {
    include 'header_client.php';
  }
}
?>
