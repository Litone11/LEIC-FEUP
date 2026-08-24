<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';
include('../includes/header.php');

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header("Location: ../auth/login.php");
    exit;
}

$serviceId = $_GET['service_id'] ?? null;
if (!$serviceId) {
    echo '<div class="error-message">ID de serviço inválido.</div>';
    include('../../includes/footer.php');
    exit;
}

$stmt = $pdo->prepare("SELECT title FROM Service WHERE id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    echo '<div class="error-message">Serviço não encontrado.</div>';
    include('../../includes/footer.php');
    exit;
}

$reviews = $pdo->prepare("SELECT r.id, r.rating, r.comment, r.created_at, u.username, u.name
                          FROM Review r
                          JOIN ServiceTransaction t ON r.transaction_id = t.id
                          JOIN User u ON t.client_id = u.id
                          WHERE t.service_id = ?
                          ORDER BY r.created_at DESC");
$reviews->execute([$serviceId]);
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Comentários | Admin Panel</title>
    <style>
        :root {
            --primary-color: #1E88E5;
            --secondary-color: #2575fc;
            --error-color: #d32f2f;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --border-color: #e0e0e0;
            --star-color: #ffc107;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--text-color);
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        main.reviews-page {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
            margin: 30px auto;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .page-header h2 {
            color: var(--primary-color);
            margin: 0;
            font-size: 28px;
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        .review-card {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .review-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .review-user {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .review-date {
            color: #666;
            font-size: 14px;
        }
        
        .review-rating {
            margin: 10px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .stars {
            color: var(--star-color);
            font-size: 20px;
            letter-spacing: 2px;
        }
        
        .rating-value {
            font-weight: 600;
            color: #666;
        }
        
        .review-comment {
            padding: 15px 0;
            line-height: 1.7;
            color: #444;
        }
        
        .delete-form {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px dashed var(--border-color);
        }
        
        .delete-btn {
            background-color: var(--error-color);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .delete-btn:hover {
            background-color: #b71c1c;
            transform: translateY(-1px);
        }
        
        .no-reviews {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }
        
        .error-message {
            background-color: rgba(211, 47, 47, 0.1);
            color: var(--error-color);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border-left: 4px solid var(--error-color);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            main.reviews-page {
                padding: 20px;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <main class="reviews-page">
            <div class="page-header">
                <h2>Comentários: <?= htmlspecialchars($service['title']) ?></h2>
                <a href="admin_services.php" class="back-link">← Voltar aos serviços</a>
            </div>
            
            <?php if ($reviews->rowCount() === 0): ?>
                <div class="no-reviews">
                    Este serviço ainda não tem comentários.
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $rev): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <span class="review-user"><?= htmlspecialchars($rev['name'] ?: $rev['username']) ?></span>
                            <span class="review-date"><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></span>
                        </div>
                        
                        <div class="review-rating">
                            <div class="stars">
                                <?php
                                    $rating = (float)$rev['rating'];
                                    $fullStars = floor($rating);
                                    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                                    $emptyStars = 5 - $fullStars - $halfStar;
                                    
                                    for ($i = 0; $i < $fullStars; $i++) echo '★';
                                    if ($halfStar) echo '½';
                                    for ($i = 0; $i < $emptyStars; $i++) echo '☆';
                                ?>
                            </div>
                            <span class="rating-value"><?= number_format($rating, 1) ?>/5</span>
                        </div>
                        
                        <?php if (!empty($rev['comment'])): ?>
                            <div class="review-comment">
                                <?= nl2br(htmlspecialchars($rev['comment'])) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form action="../review/delete_review.php" method="POST" class="delete-form">
                            <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                            <button type="submit" class="delete-btn">
                                Eliminar Comentário
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    
    <?php include('../../includes/footer.php'); ?>
</body>
</html>