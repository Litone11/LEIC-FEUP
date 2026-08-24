<?php 
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';
include('../../includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$user_id = $_GET['id'] ?? $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM User WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "<p>Utilizador não encontrado.</p>";
    include('../includes/footer.php');
    exit();
}
?>

<style>
:root {
  --primary-color: #6a1b9a;
  --primary-hover: #7c43bd;
  --secondary-color: #f8f9fa;
  --text-color: #2d3748;
  --text-light: #718096;
  --border-color: #e2e8f0;
  --card-bg: #ffffff;
  --star-filled: #ffc107;
  --star-empty: #e2e8f0;
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.profile-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  padding: 2rem 1rem;
}

.profile-container {
  max-width: 800px;
  margin: 0 auto;
}

.profile-header {
  background-color: var(--card-bg);
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-md);
  margin-bottom: 2rem;
}

.profile-info {
  display: flex;
  align-items: center;
  gap: 2rem;
}

.profile-avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid var(--primary-color);
  flex-shrink: 0;
}

.profile-details h2 {
  margin: 0 0 0.5rem;
  color: var(--primary-color);
}

.profile-details p {
  margin: 0.5rem 0;
  color: var(--text-color);
}

.profile-details strong {
  font-weight: 600;
}

.edit-profile-btn {
  margin-top: 1.5rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  border-radius: var(--radius-md);
  cursor: pointer;
  transition: var(--transition);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
}

.section {
  background-color: var(--card-bg);
  border-radius: var(--radius-lg);
  padding: 2rem;
  box-shadow: var(--shadow-md);
  margin-bottom: 2rem;
}

.section-title {
  font-size: 1.5rem;
  color: var(--primary-color);
  margin: 0 0 1.5rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid var(--border-color);
}

.service-card {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 2rem;
  padding-bottom: 2rem;
  border-bottom: 1px solid var(--border-color);
}

.service-card:last-child {
  border-bottom: none;
  margin-bottom: 0;
  padding-bottom: 0;
}

.service-image {
  width: 200px;
  height: 150px;
  border-radius: var(--radius-md);
  object-fit: cover;
  flex-shrink: 0;
}

.service-content {
  flex: 1;
}

.service-title {
  font-size: 1.25rem;
  color: var(--text-color);
  margin: 0 0 0.5rem;
}

.service-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
}

.service-price {
  font-weight: 600;
  color: var(--primary-color);
}

.btn-sm {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.stars {
  display: flex;
  gap: 0.1rem;
  margin: 0.5rem 0;
}

.star {
  color: var(--star-empty);
}

.star.filled {
  color: var(--star-filled);
}

.review-card {
  margin-bottom: 1.5rem;
  padding: 1.25rem;
  background-color: #fdfcff;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-color);
}

.review-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 0.5rem;
}

.review-user {
  font-weight: 600;
  color: var(--text-color);
}

.review-service {
  font-style: italic;
  color: var(--text-light);
}

.review-comment {
  margin-top: 0.75rem;
  color: var(--text-color);
  line-height: 1.5;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: var(--text-light);
}

@media (max-width: 768px) {
  .profile-info {
    flex-direction: column;
    text-align: center;
  }
  
  .service-card {
    flex-direction: column;
  }
  
  .service-image {
    width: 100%;
    height: 200px;
  }
}
</style>

<main class="profile-page">
  <div class="profile-container">
    <div class="profile-header">
      <div class="profile-info">
        <div class="avatar-container">
          <?php if (!empty($user['profile_picture'])): ?>
            <img src="../<?= htmlspecialchars($user['profile_picture']) ?>" alt="Foto de perfil" class="profile-avatar">
          <?php else: ?>
            <div class="profile-avatar" style="background-color: #f5f3fc; display: flex; align-items: center; justify-content: center; color: #999;">
              Sem foto
            </div>
          <?php endif; ?>
        </div>
        
        <div class="profile-details">
          <h2>@<?= !empty($user['username']) ? htmlspecialchars($user['username']) : 'N/D' ?></h2>
          <p><strong>Nome:</strong> <?= htmlspecialchars($user['name'] ?? 'N/D') ?></p>
          <p><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? 'N/D') ?></p>
        
          
          <?php if (!isset($_GET['id']) || (isset($user['id']) && $_SESSION['user_id'] === $user['id'])): ?>
            <div class="edit-profile-btn">
              <a href="edit_profile.php" class="btn btn-primary">Editar Perfil</a>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if (!empty($user['is_freelancer'])): ?>
      
      <div class="section">
        <h3 class="section-title">Serviços Publicados</h3>
          <?php
          $stmt = $pdo->prepare("SELECT s.id, s.title, s.description, s.price, s.delivery_time, s.main_image,
                                        (SELECT ROUND(AVG(r.rating),1)
                                         FROM Review r
                                         JOIN ServiceTransaction t ON r.transaction_id = t.id
                                         WHERE t.service_id = s.id) AS avg_rating
                                 FROM Service s
                                 WHERE s.user_id = ? AND s.status = 'active'");
          $stmt->execute([$user['id']]);
          $services = $stmt->fetchAll();
        ?>

        <?php if (empty($services)): ?>
          <div class="empty-state">
            <p>Este freelancer ainda não publicou serviços.</p>
          </div>
        <?php else: ?>
          <?php foreach ($services as $service): ?>
            <div class="service-card">
              <?php
                  $imageDir = __DIR__ . '/../../assets/img/service_images/' . $service['id'] . '/';
                  $images = glob($imageDir . '*');
                  $imageSrc = !empty($images)
                      ? '../../assets/img/service_images/' . $service['id'] . '/' . basename($images[0])
                      : '../../assets/img/default-service.png';
              ?>
              <img src="<?= htmlspecialchars($imageSrc) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-image">
              
              <div class="service-content">
                <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
                
                <div class="service-meta">
                  <span class="service-price">€<?= number_format($service['price'], 2) ?></span>
                  <span>Entrega em <?= (int)$service['delivery_time'] ?> dias</span>
                </div>
                
                <?php if ($service['avg_rating']): ?>
                  <div class="stars">
                    <?php
                      $rounded = round($service['avg_rating']);
                      for ($i = 0; $i < $rounded; $i++) echo '<span class="star filled">★</span>';
                      for ($i = $rounded; $i < 5; $i++) echo '<span class="star">★</span>';
                    ?>
                    <span>(<?= number_format($service['avg_rating'], 1) ?>/5)</span>
                  </div>
                <?php endif; ?>
                
                <a href="../service/service_detail.php?id=<?= $service['id'] ?>" class="btn btn-primary btn-sm">Ver Serviço</a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      
      <div class="section">
        <h3 class="section-title">Avaliações</h3>
        
        <?php
          $stmt = $pdo->prepare("SELECT AVG(r.rating) AS avg_rating FROM Review r
                                 JOIN ServiceTransaction t ON r.transaction_id = t.id
                                 JOIN Service s ON t.service_id = s.id
                                 WHERE s.user_id = ?");
          $stmt->execute([$user['id']]);
          $avgRating = $stmt->fetchColumn();

          $stmt = $pdo->prepare("SELECT r.rating, r.comment, r.created_at, s.title, u.username
                                 FROM Review r
                                 JOIN ServiceTransaction t ON r.transaction_id = t.id
                                 JOIN Service s ON t.service_id = s.id
                                 JOIN User u ON t.client_id = u.id
                                 WHERE s.user_id = ?
                                 ORDER BY r.created_at DESC");
          $stmt->execute([$user['id']]);
          $reviews = $stmt->fetchAll();
        ?>

        <?php if ($avgRating): ?>
          <div style="margin-bottom: 1.5rem;">
            <strong>Média: </strong>
            <?php
              $rounded = round($avgRating);
              for ($i = 0; $i < $rounded; $i++) echo '<span class="star filled">★</span>';
              for ($i = $rounded; $i < 5; $i++) echo '<span class="star">★</span>';
              echo ' (' . number_format($avgRating, 1) . '/5)';
            ?>
          </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
          <div class="empty-state">
            <p>Este freelancer ainda não recebeu avaliações.</p>
          </div>
        <?php else: ?>
          <?php foreach ($reviews as $review): ?>
            <div class="review-card">
              <div class="review-header">
                <span class="review-user"><?= htmlspecialchars($review['username']) ?></span>
                <span>avaliou</span>
                <span class="review-service">"<?= htmlspecialchars($review['title']) ?>"</span>
                <span>com</span>
                <div class="stars">
                  <?php
                    $rating = (float)$review['rating'];
                    $fullStars = floor($rating);
                    $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                    $emptyStars = 5 - $fullStars - $halfStar;

                    for ($i = 0; $i < $fullStars; $i++) echo '<span class="star filled">★</span>';
                    if ($halfStar) echo '<span class="star half">☆</span>';
                    for ($i = 0; $i < $emptyStars; $i++) echo '<span class="star">★</span>';
                  ?>
                </div>
                <span>(<?= number_format($rating, 1) ?>/5)</span>
              </div>
              
              <?php if (!empty($review['comment'])): ?>
                <div class="review-comment"><?= nl2br(htmlspecialchars($review['comment'])) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>