<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';
include('../../includes/header.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client']) || !isset($_GET['id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$transactionId = $_GET['id'];
$userId = $_SESSION['user_id'];


$stmt = $pdo->prepare("SELECT t.id, t.service_id, s.title, u.username, u.name FROM ServiceTransaction t
                       JOIN Service s ON t.service_id = s.id
                       JOIN User u ON s.user_id = u.id
                       WHERE t.id = ? AND t.client_id = ? AND t.status = 'received'");
$stmt->execute([$transactionId, $userId]);
$transaction = $stmt->fetch();

if (!$transaction) {
    echo '<div class="error-message">Transação inválida ou já avaliada.</div>';
    include('../includes/footer.php');
    exit;
}


$stmt = $pdo->prepare("SELECT id FROM Review WHERE transaction_id = ?");
$stmt->execute([$transactionId]);
if ($stmt->fetch()) {
    echo '<div class="error-message">Esta transação já foi avaliada.</div>';
    include('../../includes/footer.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Freelancer | Freelance Platform</title>
    <style>
        :root {
            --primary-color: #1E88E5;
            --secondary-color: #2575fc;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --border-color: #e0e0e0;
            --error-color: #f44336;
            --success-color: #4caf50;
            --star-color: #ffc107;
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
        
        main.review-page {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px;
            margin: 40px auto;
        }
        
        .review-page h2 {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }
        
        .service-info {
            background-color: var(--light-gray);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
        }
        
        .service-info p {
            margin: 8px 0;
            font-size: 16px;
        }
        
        .service-info strong {
            color: var(--primary-color);
        }
        
        .review-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        label {
            font-weight: 600;
            color: #555;
            font-size: 15px;
        }
        
        select {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 16px;
            background-color: white;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }
        
        select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
            outline: none;
        }
        
        textarea {
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        
        textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.1);
            outline: none;
        }
        
        button[type="submit"] {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 14px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        button[type="submit"]:hover {
            background: linear-gradient(135deg, #5a0db3 0%, #1a65e0 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .error-message {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--error-color);
        }
        
        .rating-options {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .rating-options select {
            flex: 0 0 80px;
        }
        
        .stars-preview {
            display: flex;
            gap: 5px;
        }
        
        .star {
            color: var(--star-color);
            font-size: 24px;
        }
        
        @media (max-width: 768px) {
            main.review-page {
                padding: 25px;
                margin: 20px auto;
            }
            
            .review-page h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="review-page">
            <h2>Avaliar Freelancer</h2>
            
            <div class="service-info">
                <p><strong>Serviço:</strong> <?= htmlspecialchars($transaction['title']) ?></p>
                <p><strong>Freelancer:</strong> <?= htmlspecialchars($transaction['name'] ?: $transaction['username']) ?></p>
            </div>
            
            <form action="../review/submit_review.php" method="POST" class="review-form">
                <input type="hidden" name="transaction_id" value="<?= $transactionId ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                
                <div class="form-group">
                    <label for="rating">Avaliação:</label>
                    <div class="rating-options">
                        <select name="rating" id="rating" required>
                            <option value="">Selecione...</option>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> estrela<?= $i > 1 ? 's' : '' ?></option>
                            <?php endfor; ?>
                        </select>
                        <div class="stars-preview" id="starsPreview">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="comment">Comentário (opcional):</label>
                    <textarea name="comment" id="comment" rows="5" placeholder="Conte-nos sobre sua experiência com este serviço..."></textarea>
                </div>
                
                <button type="submit">Enviar Avaliação</button>
            </form>
        </main>
    </div>

    <script>
        const ratingSelect = document.getElementById('rating');
        const starsPreview = document.getElementById('starsPreview');
        
        ratingSelect.addEventListener('change', function() {
            const rating = parseInt(this.value);
            starsPreview.innerHTML = '';
            
            if (rating > 0) {
                for (let i = 0; i < 5; i++) {
                    const star = document.createElement('span');
                    star.className = 'star';
                    star.innerHTML = i < rating ? '★' : '☆';
                    starsPreview.appendChild(star);
                }
            }
        });
    </script>
    
    <?php include('../../includes/footer.php'); ?>
</body>
</html>