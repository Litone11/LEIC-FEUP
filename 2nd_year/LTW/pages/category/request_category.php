<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';
include '../../includes/header.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header("Location: ../auth/login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Token CSRF inválido.';
    } else {
        $categoryName = trim($_POST['category_name']);

        if (!empty($categoryName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO CategoryRequest (user_id, name) VALUES (?, ?)");
                $stmt->execute([$_SESSION['user_id'], $categoryName]);
                
                $success = 'Pedido de categoria enviado com sucesso!';
            } catch (PDOException $e) {
                $error = 'Erro ao enviar pedido. Por favor, tente novamente.';
            }
        } else {
            $error = 'Por favor, insira um nome para a categoria.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedir Nova Categoria | Freelance Platform</title>
    <style>
        :root {
            --primary-color: #1E88E5;
            --secondary-color: #2575fc;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --success-color: #4caf50;
            --error-color: #f44336;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        main.category-request {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin-top: 30px;
        }
        
        h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
            outline: none;
        }
        
        button[type="submit"] {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 14px 20px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        button[type="submit"]:hover {
            background: linear-gradient(135deg, #5a0db3 0%, #1a65e0 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        .success-message {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            main.category-request {
                padding: 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="category-request">
            <h2>Pedir Nova Categoria</h2>
            
            <?php if ($success): ?>
                <div class="message success-message"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form action="request_category.php" method="POST">
                <div class="form-group">
                    <label for="category_name">Nome da Categoria:</label>
                    <input type="text" name="category_name" id="category_name" required>
                </div>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <button type="submit">Enviar Pedido</button>
            </form>
            
            <a href="../home/freelancer_homepage.php" class="back-link">← Voltar à página inicial</a>
        </main>
    </div>
    
    <?php include '../../includes/footer.php'; ?>
</body>
</html>