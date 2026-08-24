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
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token.';
    } else {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirm_password']);
        $name = trim($_POST['name']);
        $role = $_POST['role'];

        if (!empty($username) && !empty($email) && !empty($password) && !empty($confirm_password) && !empty($name)) {
            if ($password === $confirm_password) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                try {
                    $stmt = $pdo->prepare('INSERT INTO User (username, email, password_hash, name, is_client, is_freelancer, is_admin) VALUES (:username, :email, :password_hash, :name, :is_client, :is_freelancer, 0)');
                    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                    $stmt->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $is_client = 0;
                    $is_freelancer = 0;
                    if ($role === 'client') {
                        $is_client = 1;
                    } elseif ($role === 'freelancer') {
                        $is_freelancer = 1;
                    }
                    $stmt->bindParam(':is_client', $is_client, PDO::PARAM_INT);
                    $stmt->bindParam(':is_freelancer', $is_freelancer, PDO::PARAM_INT);
                    $stmt->execute();

                    $success = 'Registration successful! You can now <a href="login.php">log in</a>.';
                    $success_is_safe_html = true;
                } catch (PDOException $e) {
                    if ($e->getCode() === '23000') { 
                        $error = 'Username or email already exists.';
                    } else {
                        $error = 'Database error: ' . $e->getMessage();
                    }
                }
            } else {
                $error = 'Passwords do not match.';
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
    <title>Register - Freelance Platform</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
      :root {
        --primary-color: #1E88E5;
        --primary-gradient: linear-gradient(135deg, #1E88E5 0%, rgb(0, 50, 155) 100%);
        --primary-gradient-hover: linear-gradient(135deg, rgb(0, 70, 180) 0%, rgb(0, 110, 255) 100%);
      }

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
        justify-content: center;
        align-items: center;
        padding: 40px 20px;
        width: 100%;
      }

      .logo {
        margin: 30px 0;
        max-width: 180px;
        height: auto;
      }

      main.register-page {
        width: 100%;
        max-width: 480px;
        padding: 40px;
        background-color: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.2);
      }

      .register-page h2 {
        text-align: center;
        color: var(--primary-color);
        margin-bottom: 25px;
        font-size: 28px;
        font-weight: 600;
      }

      .register-page form {
        display: flex;
        flex-direction: column;
        gap: 16px;
      }

      .register-page .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
      }

      .register-page label {
        font-weight: 600;
        color: #555;
        font-size: 14px;
      }

      .register-page input[type="text"],
      .register-page input[type="email"],
      .register-page input[type="password"],
      .register-page select {
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 15px;
        transition: all 0.3s ease;
      }

      .register-page input:focus,
      .register-page select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
        outline: none;
      }

      .register-page button[type="submit"] {
        background: var(--primary-gradient);
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

      .register-page button[type="submit"]:hover {
        background: var(--primary-gradient-hover);
        transform: translateY(-1px);
        box-shadow: 0 6px 12px rgba(36, 0, 99, 0.4);
      }

      .register-page button[type="submit"]:active {
        transform: translateY(0);
      }

      .register-page p {
        text-align: center;
        margin-top: 20px;
        color: #666;
      }

      .register-page a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
      }

      .register-page a:hover {
        text-decoration: underline;
      }

      .register-page .error {
        background-color: #ffebee;
        color: #c62828;
        padding: 12px 15px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #c62828;
      }

      .register-page .success {
        background-color: #e8f5e9;
        color: #2e7d32;
        padding: 12px 15px;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 15px;
        font-size: 14px;
        border-left: 4px solid #2e7d32;
      }

      @media (max-width: 576px) {
        main.register-page {
          padding: 30px 20px;
        }
      }
    </style>
</head>
<body>
    <div class="container">
        <img src="../../assets/img/image3.png" alt="Company Logo" class="logo">
        
        <main class="register-page">
            <h2>Create Your Account</h2>
            <?php if ($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success">
                    <?php echo isset($success_is_safe_html) && $success_is_safe_html ? $success : htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="role">Account Type:</label>
                    <select name="role" id="role" required>
                        <option value="client">Client</option>
                        <option value="freelancer">Freelancer</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Log in here</a>.</p>
        </main>
    </div>
</body>
</html>