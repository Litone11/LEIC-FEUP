<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_client'])) {
    header('Location: ../auth/login.php');
    exit();
}

if (!isset($_GET['service_id']) || !is_numeric($_GET['service_id'])) {
    echo "Serviço inválido.";
    exit();
}

$clientId = $_SESSION['user_id'];
$serviceId = (int) $_GET['service_id'];

$stmt = $pdo->prepare("SELECT s.*, u.username as freelancer_name FROM Service s JOIN User u ON s.user_id = u.id WHERE s.id = ?");
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

if (!$service) {
    echo "Serviço não encontrado.";
    exit();
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
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.order-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.order-container {
  max-width: 700px;
  margin: 0 auto;
  background-color: var(--card-bg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  padding: 2rem;
}

.order-header {
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid var(--border-color);
}

.order-header h2 {
  color: var(--primary-color);
  font-size: 1.75rem;
  font-weight: 600;
  margin: 0 0 0.5rem;
}

.order-header p {
  color: var(--text-color);
  margin: 0;
}

.service-info {
  display: flex;
  gap: 1.5rem;
  margin-bottom: 1.5rem;
  align-items: center;
}

.service-image {
  width: 120px;
  height: 120px;
  border-radius: var(--radius-md);
  object-fit: cover;
  border: 1px solid var(--border-color);
}

.service-details h3 {
  font-size: 1.25rem;
  color: var(--text-color);
  margin: 0 0 0.5rem;
}

.service-details p {
  color: var(--text-light);
  margin: 0.25rem 0;
}

.service-price {
  font-weight: 600;
  color: var(--primary-color);
  font-size: 1.1rem;
}

.order-form {
  margin-top: 2rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 500;
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.form-control {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid var(--border-color);
  border-radius: var(--radius-md);
  font-size: 1rem;
  transition: var(--transition);
}

.form-control:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(106, 27, 154, 0.1);
}

textarea.form-control {
  min-height: 150px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 1rem;
  margin-top: 2rem;
  flex-wrap: wrap;
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
  font-size: 1rem;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
}

.btn-secondary {
  background-color: var(--border-color);
  color: var(--text-color);
  border: none;
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

@media (max-width: 640px) {
  .service-info {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>

<main class="order-page">
  <div class="order-container">
    <div class="order-header">
      <h2>Solicitação de Encomenda</h2>
      <p>Preencha os detalhes do seu pedido</p>
    </div>
    
    <div class="service-info">
      <?php
      $imageDir = '../img/service_images/' . $service['id'] . '/';
      $images = glob($imageDir . '*');
      $previewImage = !empty($images) ? $images[0] : '../img/default-service.png';
      ?>
      <img src="<?= htmlspecialchars($previewImage) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-image">
      
      <div class="service-details">
        <h3><?= htmlspecialchars($service['title']) ?></h3>
        <p>Freelancer: <?= htmlspecialchars($service['freelancer_name']) ?></p>
        <p class="service-price">Preço: €<?= htmlspecialchars($service['price']) ?></p>
      </div>
    </div>
    
    <form class="order-form" action="../order/submit_order.php" method="POST">
      <input type="hidden" name="client_id" value="<?= $clientId ?>">
      <input type="hidden" name="freelancer_id" value="<?= $service['user_id'] ?>">
      <input type="hidden" name="service_id" value="<?= $serviceId ?>">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      
      <div class="form-group">
        <label for="message">Mensagem para o freelancer</label>
        <textarea name="message" id="message" class="form-control" placeholder="Descreva com detalhes o que você precisa..."></textarea>
      </div>
      
      <div class="form-actions">
        <button type="submit" class="btn btn-primary">Enviar Pedido</button>
        <a href="../chat/chat.php?freelancer_id=<?= $service['user_id'] ?>" class="btn btn-secondary">Conversar com Freelancer</a>
      </div>
    </form>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>