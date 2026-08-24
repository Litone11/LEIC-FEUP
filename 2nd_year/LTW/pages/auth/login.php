<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../../database/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $stmt = $pdo->prepare('SELECT * FROM User WHERE username = :username');
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['is_client'] = $user['is_client'];
                $_SESSION['is_freelancer'] = $user['is_freelancer'];
                $_SESSION['is_admin'] = $user['is_admin'];
                header('Location: ../../index.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Please fill in all fields.';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Freelance Platform</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
      body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        background: url('../../assets/img/wallpaper.jpeg') no-repeat center center fixed;
        background-size: cover;
      }

      .container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 20px;
        max-width: 100%;
        width: 100%;
      }

      .logo {
        margin: 30px 0;
        max-width: 180px;
        height: auto;
        
      }

      main.login-page {
        width: 100%;
        max-width: 480px;
        padding: 40px;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
      }

      .login-page h2 {
        text-align: center;
        color: #1E88E5;
        margin-bottom: 25px;
        font-size: 28px;
        font-weight: 600;
      }

      .login-page form {
        display: flex;
        flex-direction: column;
        gap: 16px;
      }

      .login-page .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
      }

      .login-page label {
        font-weight: 600;
        color: #555;
        font-size: 14px;
      }

      .login-page input[type="text"],
      .login-page input[type="email"],
      .login-page input[type="password"],
      .login-page select {
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
      }

      .login-page input:focus,
      .login-page select:focus {
        border-color: #1E88E5;
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        outline: none;
      }

      .login-page button[type="submit"] {
        background: linear-gradient(135deg, #1E88E5 0%,rgb(0, 50, 155) 100%);
        color: white;
        padding: 14px;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-top: 10px;
        transition: all 0.3s ease;
      }

      .login-page button[type="submit"]:hover {
        background: linear-gradient(135deg,rgb(0, 70, 180) 0%,rgb(0, 110, 255) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      }

      .login-page button[type="submit"]:active {
        transform: translateY(0);
      }

      .login-page p {
        text-align: center;
        margin-top: 20px;
        color: #666;
      }

      .login-page a {
        color: #1E88E5;
        text-decoration: none;
        font-weight: 600;
      }

      .login-page a:hover {
        text-decoration: underline;
      }

      .login-page .error {
        background-color: #ffebee;
        color: #c62828;
        padding: 12px 15px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #c62828;
      }

      @media (max-width: 576px) {
        main.login-page {
          padding: 30px 20px;
        }
      }
    </style>
</head>
<body>
    <div class="container">
        <img src="../../assets/img/image3.png" alt="Company Logo" class="logo">
        
        <main class="login-page">
            <h2>Login to Your Account</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <button type="submit">Log In</button>
            </form>
            <p>Don't have an account? <a href="register.php">Register here</a>.</p>
        </main>
    </div>
</body>
</html>