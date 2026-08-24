<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "Serviço inválido.";
  exit();
}

$serviceId = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT s.*, u.username, u.email, u.profile_picture, u.bio, c.name AS category
                       FROM Service s
                       JOIN User u ON s.user_id = u.id
                       LEFT JOIN Category c ON s.category_id = c.id
                       WHERE s.id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
  echo "Serviço não encontrado.";
  exit();
}

$imageDir = '../../assets/img/service_images/' . $serviceId . '/';
$images = file_exists($imageDir) ? glob($imageDir . '*') : [];
?>

<?php include('../../includes/header.php'); ?>

<style>

:root {

  --primary: #1E88E5;
  --primary-light: #64B5F6;
  --primary-dark: #1565C0;
  --secondary: #10B981;
  --dark: #1F2937;
  --gray-dark: #374151;
  --gray: #6B7280;
  --gray-light: #E5E7EB;
  --light: #F9FAFB;
  --white: #FFFFFF;
  

  --space-xs: 0.25rem;
  --space-sm: 0.5rem;
  --space-md: 1rem;
  --space-lg: 1.5rem;
  --space-xl: 2rem;
  --space-2xl: 3rem;
  
  
  --radius-sm: 4px;
  --radius-md: 8px;
  --radius-lg: 12px;
  --radius-full: 9999px;
  
  
  --shadow-sm: 0 1px 2px 0 rgba(0,0,0,0.05);
  --shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
  --shadow-md: 0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05);
  --shadow-lg: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04);
  
 
  --transition: all 0.2s ease-in-out;
}


* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  line-height: 1.6;
  color: var(--dark);
  background-color: var(--light);
  -webkit-font-smoothing: antialiased;
}

h1, h2, h3, h4 {
  font-weight: 700;
  line-height: 1.2;
  color: var(--dark);
}

h1 { font-size: 2.5rem; }
h2 { font-size: 1.75rem; }
h3 { font-size: 1.5rem; }

p {
  margin-bottom: var(--space-md);
}

a {
  color: var(--primary);
  text-decoration: none;
  transition: var(--transition);
}

a:hover {
  color: var(--primary-dark);
}

.main-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: var(--space-xl);
}

.service-header {
  margin-bottom: var(--space-2xl);
  text-align: center;
  background: transparent;
  padding: 0;
}

.service-title {
  margin-bottom: var(--space-sm);
  color: var(--dark);
}

.service-meta {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: var(--space-md);
  margin-bottom: var(--space-md);
}

.service-category {
  background-color: var(--primary-light);
  color: var(--white);
  padding: var(--space-xs) var(--space-md);
  border-radius: var(--radius-full);
  font-size: 0.875rem;
  font-weight: 500;
}

.service-rating {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  font-weight: 500;
}

.star-filled {
  color: #F59E0B;
}

.star-empty {
  color: var(--gray-light);
}

.service-subtitle {
  color: var(--gray);
  max-width: 600px;
  margin: 0 auto;
}


.service-grid {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: var(--space-xl);
  margin-bottom: var(--space-2xl);
}


.gallery-container {
  width: 100%;
  margin-bottom: var(--space-xl);
}

.gallery {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: var(--space-md);
  width: 100%;
}

.gallery-item {
  position: relative;
  overflow: hidden;
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  aspect-ratio: 4/3; 
}

.gallery-item:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-lg);
}

.gallery-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: var(--transition);
}

.gallery-item:hover .gallery-img {
  transform: scale(1.03);
}


.freelancer-card {
  background: var(--light);
  border-radius: var(--radius-lg);
  padding: var(--space-xl);
  box-shadow: var(--shadow-md);
  position: sticky;
  top: var(--space-xl);
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid var(--gray-light);
}

.freelancer-header-box {
  text-align: center;
  margin-bottom: var(--space-lg);
  width: 100%;
  background: var(--white);
  border-radius: var(--radius-md);
  padding: var(--space-md);
}

.freelancer-avatar-container {
  position: relative;
  margin: 0 auto var(--space-md);
  width: 140px;
  height: 140px;
}

.freelancer-avatar {
  width: 100%;
  height: 100%;
  border-radius: var(--radius-full);
  object-fit: cover;
  border: 5px solid var(--white);
  box-shadow: var(--shadow-lg);
  transition: var(--transition);
}

.freelancer-avatar-container:hover .freelancer-avatar {
  transform: scale(1.05);
}

.freelancer-name {
  margin-bottom: var(--space-sm);
  font-size: 1.5rem;
  color: var(--dark);
  position: relative;
  display: inline-block;
}

.freelancer-name::after {
  content: '';
  display: block;
  width: 50px;
  height: 3px;
  background: var(--primary);
  margin: var(--space-xs) auto 0;
  border-radius: var(--radius-full);
}

.freelancer-contact {
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
  margin-bottom: var(--space-lg);
  width: 100%;
}

.freelancer-email {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-sm);
  color: var(--gray-dark);
  font-size: 0.9375rem;
  padding: var(--space-sm);
  background: var(--light);
  border-radius: var(--radius-md);
  transition: var(--transition);
}

.freelancer-email:hover {
  background: var(--gray-light);
  color: var(--dark);
}

.freelancer-email svg {
  width: 18px;
  height: 18px;
  color: var(--primary);
}

.freelancer-bio {
  color: var(--gray-dark);
  font-size: 0.9375rem;
  line-height: 1.7;
  text-align: center;
  margin-bottom: var(--space-xl);
  padding: var(--space-lg);
  background: var(--light);
  border-radius: var(--radius-md);
  border-left: 4px solid var(--primary);
  position: relative;
}

.freelancer-bio::before {
  content: '"';
  font-size: 3rem;
  color: var(--primary-light);
  position: absolute;
  top: 10px;
  left: 15px;
  line-height: 1;
  opacity: 0.3;
}

.freelancer-actions {
  width: 100%;
  margin-top: auto;
}

.btn-view-profile {
  width: 100%;
  padding: var(--space-md);
  font-size: 1rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-sm);
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: var(--white);
  font-weight: 600;
  transition: var(--transition);
  box-shadow: var(--shadow-sm);
}

.btn-view-profile:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
  background: linear-gradient(135deg, var(--primary-dark), var(--primary));
}

.btn-view-profile svg {
  width: 18px;
  height: 18px;
}

.service-details {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--space-xl);
  box-shadow: var(--shadow-sm);
  margin-bottom: var(--space-xl);
  width: 100%;
  border: 1px solid var(--gray-light);
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-md) 0;
  border-bottom: 1px solid var(--gray-light);
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-label {
  font-weight: 600;
  color: var(--gray-dark);
  font-size: 0.9375rem;
}

.detail-value {
  font-weight: 700;
  color: var(--dark);
}

.price-value {
  font-size: 2rem;
  color: var(--primary);
  font-weight: 800;
}

.delivery-value {
  color: var(--secondary);
  font-weight: 600;
}

.service-description {
  margin-top: var(--space-xl);
  padding-top: var(--space-lg);
  border-top: 1px solid var(--gray-light);
}

.service-description h3 {
  margin-bottom: var(--space-md);
  color: var(--dark);
}

.service-description p {
  color: var(--gray-dark);
  line-height: 1.8;
}

.btn-group {
  display: flex;
  gap: var(--space-md);
  margin-top: var(--space-xl);
}

.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: var(--space-md) var(--space-lg);
  border-radius: var(--radius-md);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  border: none;
  flex: 1;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: var(--white);
  box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
  background: linear-gradient(135deg, var(--primary-dark), var(--primary));
}

.btn-outline {
  background-color: transparent;
  color: var(--primary);
  border: 2px solid var(--primary);
}

.btn-outline:hover {
  background-color: rgba(124, 58, 237, 0.1);
  transform: translateY(-2px);
}

.reviews-section {
  background: var(--white);
  border-radius: var(--radius-lg);
  padding: var(--space-xl);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-light);
}

.review-list {
  margin-top: var(--space-xl);
}

.review-card {
  padding: var(--space-xl);
  border-radius: var(--radius-md);
  background: var(--light);
  margin-bottom: var(--space-lg);
  border: 1px solid var(--gray-light);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: var(--space-md);
}

.review-user {
  display: flex;
  align-items: center;
  gap: var(--space-md);
}

.review-avatar {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-full);
  object-fit: cover;
  border: 2px solid var(--white);
  box-shadow: var(--shadow-sm);
}

.review-meta {
  display: flex;
  flex-direction: column;
}

.review-name {
  font-weight: 700;
  color: var(--dark);
}

.review-date {
  font-size: 0.8125rem;
  color: var(--gray);
}

.review-rating {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
}

.review-content {
  color: var(--gray-dark);
  line-height: 1.8;
  font-size: 0.9375rem;
}

@media (max-width: 768px) {
  .service-grid {
    grid-template-columns: 1fr;
  }
  
  .freelancer-card {
    position: static;
    margin-top: var(--space-xl);
  }
  
  .btn-group {
    flex-direction: column;
  }
  
  .gallery {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  }
}

@media (max-width: 480px) {
  .main-container {
    padding: var(--space-md);
  }
  
  .service-header {
    margin-bottom: var(--space-xl);
  }
  
  .service-title {
    font-size: 2rem;
  }
  
  .service-meta {
    flex-direction: column;
    gap: var(--space-sm);
  }
  
  .freelancer-avatar-container {
    width: 120px;
    height: 120px;
  }
}
</style>

<main class="main-container">
  <header class="service-header">
    <h1 class="service-title"><?= htmlspecialchars($service['title']) ?></h1>
    
    <div class="service-meta">
      <span class="service-category"><?= isset($service['category']) ? htmlspecialchars($service['category']) : 'Geral' ?></span>
      
      <?php
        $stmt = $pdo->prepare("SELECT ROUND(AVG(r.rating), 1) AS avg_rating FROM Review r
                               JOIN ServiceTransaction t ON r.transaction_id = t.id
                               WHERE t.service_id = ?");
        $stmt->execute([$serviceId]);
        $avgRating = $stmt->fetchColumn();
        
        if ($avgRating): ?>
          <div class="service-rating">
            <span><?= number_format($avgRating, 1) ?></span>
            <div class="stars">
              <?php
                $rounded = round($avgRating * 2) / 2;
                $fullStars = floor($rounded);
                $halfStar = ($rounded - $fullStars) >= 0.5 ? 1 : 0;
                $emptyStars = 5 - $fullStars - $halfStar;
                
                for ($i = 0; $i < $fullStars; $i++) echo '<span class="star-filled">★</span>';
                if ($halfStar) echo '<span class="star-filled">½</span>';
                for ($i = 0; $i < $emptyStars; $i++) echo '<span class="star-empty">☆</span>';
              ?>
            </div>
            <span>(<?= $avgRating ?>/5)</span>
          </div>
      <?php endif; ?>
    </div>
    
    <p class="service-subtitle">Descubra todos os detalhes deste serviço personalizado</p>
  </header>

  <div class="service-grid">
    <div>
      <div class="gallery-container">
        <?php if (!empty($images)): ?>
          <div class="gallery">
            <?php foreach ($images as $img): ?>
              <a href="<?= htmlspecialchars($img) ?>" target="_blank" class="gallery-item">
                <img src="<?= htmlspecialchars($img) ?>" alt="Imagem do serviço" class="gallery-img">
              </a>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="no-images" style="background: var(--white); padding: var(--space-xl); border-radius: var(--radius-lg); text-align: center;">
            <p style="color: var(--gray);">Este serviço ainda não possui imagens</p>
          </div>
        <?php endif; ?>
      </div>
      
      <div class="service-details">
        <h2>Detalhes do Serviço</h2>
        
        <div class="detail-row">
          <span class="detail-label">Preço</span>
          <span class="detail-value price-value">€<?= htmlspecialchars($service['price']) ?></span>
        </div>
        
        <div class="detail-row">
          <span class="detail-label">Prazo de Entrega</span>
          <span class="detail-value delivery-value">
            <?= isset($service['delivery_time']) ? htmlspecialchars($service['delivery_time']) . ' dias' : 'A combinar' ?>
          </span>
        </div>
        
        <div class="service-description">
          <h3>Sobre este serviço</h3>
          <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
        </div>
        
        <?php if (!empty($_SESSION['is_client'])): ?>
          <div class="btn-group">
            <a href="../order/order_service.php?service_id=<?= $serviceId ?>" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
              </svg>
              Encomendar Serviço
            </a>
            <a href="../chat/chat.php?freelancer_id=<?= $service['user_id'] ?>" class="btn btn-outline">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
              Falar com o Freelancer
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
    
    <aside class="freelancer-card">
      <div class="freelancer-header-box">
        <div class="freelancer-avatar-container">
          <?php
            $profilePic = !empty($service['profile_picture']) && file_exists('../' . $service['profile_picture'])
              ? '../' . $service['profile_picture']
              : '../../assets/img/default-profile.png';
          ?>
          <img src="<?= htmlspecialchars($profilePic) ?>" alt="Foto de perfil" class="freelancer-avatar">
        </div>
        <h3 class="freelancer-name"><?= htmlspecialchars($service['username']) ?></h3>
        
        <div class="freelancer-contact">
          <a href="mailto:<?= htmlspecialchars($service['email']) ?>" class="freelancer-email">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <?= htmlspecialchars($service['email']) ?>
          </a>
        </div>
      </div>
      
      <?php if (!empty($service['bio'])): ?>
        <div class="freelancer-bio">
          <p><?= nl2br(htmlspecialchars($service['bio'])) ?></p>
        </div>
      <?php endif; ?>
      
      <div class="freelancer-actions">
        <a href="../profile/profile.php?id=<?= $service['user_id'] ?>" class="btn-view-profile">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          Ver Perfil Completo
        </a>
      </div>
    </aside>
  </div>
  
  <section class="reviews-section">
    <h2>Avaliações de Clientes</h2>
    
    <?php
      $stmt = $pdo->prepare("SELECT r.id, r.rating, r.comment, r.created_at, u.username, u.profile_picture FROM Review r
                             JOIN ServiceTransaction t ON r.transaction_id = t.id
                             JOIN User u ON t.client_id = u.id
                             WHERE t.service_id = ?");
      $stmt->execute([$serviceId]);
      $reviews = $stmt->fetchAll();
      
      if (!$reviews): ?>
        <div class="no-reviews" style="text-align: center; padding: var(--space-xl); background: var(--light); border-radius: var(--radius-md);">
          <p style="color: var(--gray);">Este serviço ainda não tem avaliações.</p>
        </div>
    <?php else: ?>
      <div class="review-list">
        <?php foreach ($reviews as $rev): ?>
          <div class="review-card">
            <div class="review-header">
              <div class="review-user">
                <img src="<?= !empty($rev['profile_picture']) ? '../' . htmlspecialchars($rev['profile_picture']) : '../../assets/img/default-profile.png' ?>" 
                     alt="Foto do cliente" class="review-avatar">
                <div class="review-meta">
                  <span class="review-name"><?= htmlspecialchars($rev['username']) ?></span>
                  <span class="review-date"><?= date('d/m/Y', strtotime($rev['created_at'])) ?></span>
                </div>
              </div>
              
              <div class="review-rating">
                <?php
                  $rating = (float)$rev['rating'];
                  $fullStars = floor($rating);
                  $halfStar = ($rating - $fullStars) >= 0.5 ? 1 : 0;
                  $emptyStars = 5 - $fullStars - $halfStar;
                  
                  for ($i = 0; $i < $fullStars; $i++) echo '<span class="star-filled">★</span>';
                  if ($halfStar) echo '<span class="star-filled">½</span>';
                  for ($i = 0; $i < $emptyStars; $i++) echo '<span class="star-empty">☆</span>';
                ?>
                <span>(<?= number_format($rating, 1) ?>)</span>
              </div>
            </div>
            
            <?php if (!empty($rev['comment'])): ?>
              <div class="review-content">
                <p><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
              </div>
            <?php endif; ?>
            
            <?php if (!empty($_SESSION['is_admin'])): ?>
              <form action="../review/delete_review.php" method="POST" style="text-align: right; margin-top: var(--space-md);">
                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" class="btn btn-outline" style="padding: var(--space-xs) var(--space-md); font-size: 0.875rem;">
                  Eliminar Comentário
                </button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include('../../includes/footer.php'); ?>