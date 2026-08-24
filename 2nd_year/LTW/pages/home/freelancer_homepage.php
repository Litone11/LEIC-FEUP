<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

// Verificar autenticação e permissões
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Configuração do banco de dados
try {
    $dbPath = __DIR__ . '/../../database/database.db';
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// Buscar serviços do freelancer com avaliações
$user_id = $_SESSION['user_id'];
$services = [];
try {
    $stmt = $db->prepare('
        SELECT s.id, s.title, s.description, s.price, c.name AS category,
        (SELECT ROUND(AVG(r.rating), 1) FROM Review r 
        JOIN ServiceTransaction t ON r.transaction_id = t.id 
        WHERE t.service_id = s.id) AS avg_rating,
        (SELECT COUNT(*) FROM Review r 
        JOIN ServiceTransaction t ON r.transaction_id = t.id 
        WHERE t.service_id = s.id) AS review_count
        FROM Service s
        LEFT JOIN Category c ON s.category_id = c.id
        WHERE s.user_id = :user_id
        ORDER BY s.created_at DESC
    ');
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $services = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Erro ao buscar serviços: " . $e->getMessage());
}
?>

<?php include('../../includes/header.php'); ?>

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

.freelancer-dashboard {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.dashboard-container {
  max-width: 1200px;
  margin: 0 auto;
}

.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.dashboard-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--primary-color);
  margin: 0;
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
  gap: 0.5rem;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
  transform: translateY(-2px);
  box-shadow: var(--shadow-sm);
}

.btn-danger {
  background-color: #e53e3e;
  color: white;
  border: none;
}

.btn-danger:hover {
  background-color: #c53030;
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
}

.service-card {
  background-color: var(--card-bg);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  border: 1px solid var(--border-color);
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.service-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.service-content {
  padding: 1.25rem;
}

.service-title {
  font-size: 1.125rem;
  font-weight: 600;
  color: var(--text-color);
  margin: 0 0 0.5rem;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.service-description {
  color: var(--text-light);
  font-size: 0.875rem;
  margin: 0.5rem 0;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.service-meta {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-top: 1rem;
  font-size: 0.875rem;
}

.service-price {
  font-weight: 600;
  color: var(--primary-color);
}

.service-category {
  color: var(--text-light);
}

.rating-container {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin: 0.5rem 0;
}

.stars {
  display: flex;
  gap: 0.1rem;
}

.star {
  color: var(--star-empty);
  font-size: 1rem;
}

.star.filled {
  color: var(--star-filled);
}

.star.half {
  position: relative;
}

.star.half::before {
  content: "★";
  position: absolute;
  width: 50%;
  overflow: hidden;
  color: var(--star-filled);
}

.rating-value {
  font-weight: 500;
  color: var(--text-color);
}

.review-count {
  color: var(--text-light);
  font-size: 0.75rem;
}

.service-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.service-actions .btn {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
  flex: 1;
  text-align: center;
  justify-content: center;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  grid-column: 1 / -1;
}

.empty-state p {
  font-size: 1.125rem;
  color: var(--text-light);
  margin-top: 1rem;
}

@media (max-width: 768px) {
  .services-grid {
    grid-template-columns: 1fr;
  }
  
  .dashboard-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>

<main class="freelancer-dashboard">
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1 class="dashboard-title">Meus Serviços</h1>
      <a href="../service/add_service.php" class="btn btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"></line>
          <line x1="5" y1="12" x2="19" y2="12"></line>
        </svg>
        Adicionar Serviço
      </a>
    </div>

    <div class="services-grid">
      <?php if (empty($services)): ?>
        <div class="empty-state">
          <p>Você ainda não possui serviços cadastrados.</p>
          <a href="../service/add_service.php" class="btn btn-primary">Criar primeiro serviço</a>
        </div>
      <?php else: ?>
        <?php foreach ($services as $service): ?>
          <?php
          $imageDir = '../../assets/img/service_images/' . $service['id'] . '/';
          $images = glob($imageDir . '*');
          $previewImage = !empty($images) ? $images[0] : '../../assets/img/default-service.png';
          ?>
          <div class="service-card">
            <img src="<?= htmlspecialchars($previewImage) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-image">
            <div class="service-content">
              <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
              <p class="service-description"><?= htmlspecialchars($service['description']) ?></p>
              
              <div class="service-meta">
                <span class="service-price">€<?= htmlspecialchars($service['price']) ?></span>
                <span class="service-category"><?= htmlspecialchars($service['category']) ?></span>
                
                <div class="rating-container">
                  <?php if (!empty($service['avg_rating'])): ?>
                    <?php
                    $rating = (float)$service['avg_rating'];
                    $fullStars = floor($rating);
                    $hasHalfStar = ($rating - $fullStars) >= 0.5;
                    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                    ?>
                    <div class="stars">
                      <?php for ($i = 0; $i < $fullStars; $i++): ?>
                        <span class="star filled">★</span>
                      <?php endfor; ?>
                      <?php if ($hasHalfStar): ?>
                        <span class="star half">★</span>
                      <?php endif; ?>
                      <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                        <span class="star">★</span>
                      <?php endfor; ?>
                    </div>
                    <span class="rating-value"><?= number_format($rating, 1) ?></span>
                    <span class="review-count">(<?= $service['review_count'] ?>)</span>
                  <?php else: ?>
                    <span class="rating-value">Sem avaliações</span>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="service-actions">
                <a href="../service/edit_service.php?id=<?= $service['id'] ?>" class="btn btn-primary">Editar</a>
                <form action="../service/delete_service.php" method="POST" style="display: inline; flex: 1;">
                  <input type="hidden" name="service_id" value="<?= $service['id'] ?>">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <button type="submit" class="btn btn-danger">Apagar</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>