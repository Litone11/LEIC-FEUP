<?php
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header('Location: ../auth/login.php');
    exit();
}

$freelancerId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_id'])) {
    $stmt = $pdo->prepare("UPDATE ServiceTransaction SET status = 'completed', completed_at = datetime('now') WHERE id = ? AND EXISTS (SELECT 1 FROM Service WHERE id = service_id AND user_id = ?)");
    $stmt->execute([$_POST['complete_id'], $freelancerId]);
    
    $_SESSION['success_message'] = "Encomenda marcada como concluída com sucesso!";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

$stmt = $pdo->prepare("SELECT t.id, t.created_at, t.status, u.id AS client_id, u.username AS client_name, u.email AS client_email, 
                              u.profile_picture AS client_avatar, s.title, s.id AS service_id,
                              (SELECT content FROM Inquiry WHERE sender_id = u.id AND service_id = t.service_id ORDER BY sent_at DESC LIMIT 1) AS content
                       FROM ServiceTransaction t
                       JOIN Service s ON s.id = t.service_id
                       JOIN User u ON u.id = t.client_id
                       WHERE s.user_id = ? AND t.status = 'accepted'
                       ORDER BY t.created_at DESC");
$stmt->execute([$freelancerId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  --success-bg: #f0fff4;
  --success-text: #38a169;
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.orders-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.orders-container {
  max-width: 800px;
  margin: 0 auto;
  background-color: white;
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.orders-header {
  background-color: var(--primary-color);
  color: white;
  padding: 1.5rem;
  text-align: center;
}

.orders-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
}

.orders-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 1.25rem;
  padding: 1.5rem;
}

.order-card {
  background-color: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  transition: var(--transition);
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.order-client {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.client-avatar {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border-color);
}

.client-info h3 {
  margin: 0;
  font-size: 1.125rem;
  color: var(--text-color);
}

.client-info p {
  margin: 0.25rem 0 0;
  color: var(--text-light);
  font-size: 0.875rem;
}

.order-details {
  display: grid;
  gap: 0.75rem;
  margin-top: 1rem;
}

.order-detail {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.order-detail strong {
  font-weight: 600;
  color: var(--text-color);
}

.order-detail span {
  color: var(--text-light);
}

.order-message {
  background-color: #f8f9fa;
  padding: 0.75rem;
  border-radius: var(--radius-sm);
  margin-top: 0.75rem;
  font-size: 0.875rem;
  color: var(--text-light);
}

.order-actions {
  display: flex;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  font-weight: 500;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: none;
  font-size: 0.875rem;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.btn-icon {
  margin-right: 0.5rem;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: var(--text-light);
}

.empty-state p {
  margin-top: 1rem;
  font-size: 1.125rem;
}

.alert {
  padding: 0.75rem 1rem;
  border-radius: var(--radius-sm);
  margin: 1rem;
  font-size: 0.875rem;
}

.alert-success {
  background-color: var(--success-bg);
  color: var(--success-text);
  border: 1px solid rgba(56, 161, 105, 0.2);
}

@media (max-width: 640px) {
  .orders-container {
    border-radius: 0;
  }
  
  .orders-header {
    padding: 1.25rem;
  }
  
  .orders-list {
    padding: 1.25rem;
  }
  
  .order-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>

<main class="orders-page">
  <div class="orders-container">
    <div class="orders-header">
      <h2>Encomendas em Andamento</h2>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
      <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
      <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (empty($orders)): ?>
      <div class="empty-state">
        <p>Não tens encomendas em andamento no momento.</p>
      </div>
    <?php else: ?>
      <ul class="orders-list">
        <?php foreach ($orders as $order): ?>
          <li class="order-card">
            <div class="order-client">
              <img src="<?= htmlspecialchars($order['client_avatar'] ?? '../img/default-profile.png') ?>" 
                   alt="Foto do cliente" 
                   class="client-avatar">
              <div class="client-info">
                <h3><?= htmlspecialchars($order['client_name']) ?></h3>
                <p><?= htmlspecialchars($order['client_email']) ?></p>
              </div>
            </div>
            
            <div class="order-details">
              <div class="order-detail">
                <strong>Serviço:</strong>
                <span><?= htmlspecialchars($order['title']) ?></span>
              </div>
              
              <div class="order-detail">
                <strong>Data do pedido:</strong>
                <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
              </div>
              
              <?php if ($order['content']): ?>
                <div class="order-message">
                  <strong>Mensagem:</strong> <?= htmlspecialchars($order['content']) ?>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="order-actions">
              <a href="../order/submit_delivery.php?id=<?= $order['id'] ?>" class="btn btn-primary">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                  <polyline points="7 10 12 15 17 10"></polyline>
                  <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                Entregar Encomenda
              </a>
              
              <a href="../chat/chat.php?freelancer_id=<?= $freelancerId ?>&client_id=<?= $order['client_id'] ?>" class="btn" style="background-color: var(--border-color); color: var(--text-color);">
                <svg class="btn-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                </svg>
                Conversar
              </a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>