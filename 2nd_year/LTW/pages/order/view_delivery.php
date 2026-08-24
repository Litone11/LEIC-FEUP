<?php
session_start();
require_once '../../database/db.php';
require_once '../../includes/header.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    header('Location: ../auth/login.php');
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT d.message, d.files, d.submitted_at, s.title, u.username, u.name
                       FROM Delivery d
                       JOIN ServiceTransaction t ON d.transaction_id = t.id
                       JOIN Service s ON t.service_id = s.id
                       JOIN User u ON s.user_id = u.id
                       WHERE d.transaction_id = ? AND t.client_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$delivery = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Entrega | Freelance Platform</title>
    <style>
        :root {
            --primary-color: #1E88E5;
            --secondary-color: #2575fc;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --highlight-color: #f0e6ff;
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
        
        main.delivery-view {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin: 40px auto;
        }
        
        .delivery-view h2 {
            color: var(--primary-color);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .delivery-info {
            margin-bottom: 30px;
        }
        
        .delivery-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        
        .delivery-info strong {
            color: var(--primary-color);
        }
        
        .message-box {
            background-color: var(--highlight-color);
            padding: 20px;
            border-radius: 10px;
            margin: 25px 0;
            border-left: 4px solid var(--primary-color);
        }
        
        .message-box h4 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .message-content {
            white-space: pre-wrap;
            line-height: 1.7;
        }
        
        .files-section {
            margin-top: 30px;
        }
        
        .files-section h4 {
            margin-bottom: 15px;
            color: var(--primary-color);
            font-size: 18px;
        }
        
        .files-list {
            list-style-type: none;
            padding: 0;
            margin: 0;
        }
        
        .files-list li {
            padding: 12px 15px;
            margin-bottom: 8px;
            background-color: white;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }
        
        .files-list li:hover {
            transform: translateX(5px);
            border-color: var(--primary-color);
        }
        
        .file-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-color);
            font-weight: 500;
        }
        
        .file-link:hover {
            color: var(--primary-color);
        }
        
        .file-icon {
            color: var(--primary-color);
            font-size: 20px;
        }
        
        .not-found {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 30px;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 10px 15px;
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .back-link:hover {
            background-color: var(--highlight-color);
            text-decoration: none;
        }
        
        @media (max-width: 768px) {
            main.delivery-view {
                padding: 25px;
            }
            
            .delivery-view h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="delivery-view">
            <?php if (!$delivery): ?>
                <div class="not-found">
                    <p>Entrega não encontrada ou você não tem permissão para visualizá-la.</p>
                    <a href="client_orders.php" class="back-link">← Voltar aos pedidos</a>
                </div>
            <?php else: ?>
                <h2>Entrega Final</h2>
                
                <div class="delivery-info">
                    <p><strong>Serviço:</strong> <?= htmlspecialchars($delivery['title']) ?></p>
                    <p><strong>Freelancer:</strong> <?= htmlspecialchars($delivery['name'] ?: $delivery['username']) ?></p>
                    <p><strong>Data de Entrega:</strong> <?= date('d/m/Y \à\s H:i', strtotime($delivery['submitted_at'])) ?></p>
                </div>
                
                <div class="message-box">
                    <h4>Mensagem do Freelancer</h4>
                    <div class="message-content"><?= nl2br(htmlspecialchars($delivery['message'])) ?></div>
                </div>
                
                <?php if (!empty($delivery['files'])): ?>
                    <div class="files-section">
                        <h4>Ficheiros Entregues</h4>
                        <ul class="files-list">
                            <?php foreach (explode(',', $delivery['files']) as $file): 
                                $file = trim($file);
                                if (!empty($file)): ?>
                                    <li>
                                        <a href="../uploads/<?= urlencode($file) ?>" download class="file-link">
                                            <span class="file-icon">📎</span>
                                            <span><?= htmlspecialchars(basename($file)) ?></span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <a href="client_orders.php" class="back-link">← Voltar aos pedidos</a>
            <?php endif; ?>
        </main>
    </div>

    <?php require_once '../../includes/footer.php'; ?>
</body>
</html>