<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['is_freelancer'])) {
    header('Location: ../auth/login.php');
    exit();
}

$freelancerId = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT t.id, t.created_at, t.status, u.id AS client_id, u.username AS client_name, u.email AS client_email, s.title,
           (SELECT content FROM Inquiry WHERE sender_id = u.id AND service_id = t.service_id ORDER BY sent_at DESC LIMIT 1) AS content
    FROM ServiceTransaction t
    JOIN Service s ON s.id = t.service_id
    JOIN User u ON u.id = t.client_id
    WHERE s.user_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$freelancerId]);
$deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('../../includes/header.php'); ?>

<style>
:root {
  --primary-color: #6a1b9a;
  --primary-hover: #7c43bd;
  --secondary-color: #f8f9fa;
  --text-color: #2d3748;
  --text-light:rgb(38, 38, 38);
  --border-color: #e2e8f0;
  --card-bg: #ffffff;
  --success: #38a169;
  --warning: #dd6b20;
  --info: #3182ce;
  --danger: #e53e3e;
  --pending: #f0ad4e;
  --in-progress: #5bc0de;
  --completed: #5cb85c;
  --received: #0275d8;
  --rejected: #d9534f;
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
  --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
  --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
  --radius-sm: 0.25rem;
  --radius-md: 0.5rem;
  --radius-lg: 1rem;
}

.deliveries-page {
  font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.deliveries-container {
  max-width: 800px;
  margin: 0 auto;
}

.deliveries-page h2 {
  text-align: center;
  color: var(--primary-color);
  margin-bottom: 2rem;
  font-size: 1.75rem;
  font-weight: 700;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  background-color: var(--card-bg);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-sm);
  color: var(--text-light);
}

.order-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 1.25rem;
}

.order-card {
  background-color: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  transition: all 0.2s ease;
}

.order-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.order-meta {
  display: grid;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.order-meta-item {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.order-meta-item strong {
  font-weight: 600;
  color: var(--text-color);
}

.order-meta-item span {
  color: var(--text-light);
}

.order-status {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-sm);
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
  text-transform: capitalize;
}

.order-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  margin-top: 1.25rem;
}

.btn {
  padding: 0.5rem 1rem;
  border-radius: var(--radius-sm);
  font-weight: 500;
  font-size: 0.875rem;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
  transform: translateY(-1px);
}

.btn-secondary {
  background-color: var(--border-color);
  color: var(--text-color);
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

.btn-danger {
  background-color: var(--danger);
  color: white;
}

.btn-danger:hover {
  background-color: #c53030;
}

.btn-chat {
  background-color: var(--info);
  color: white;
}

.btn-chat:hover {
  background-color: #2c5282;
}

.status-pending { background-color: var(--pending); color: white; }
.status-in-progress { background-color: var(--in-progress); color: white; }
.status-completed { background-color: var(--completed); color: white; }
.status-received { background-color: var(--received); color: white; }
.status-rejected { background-color: var(--rejected); color: white; }

.message-preview {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  text-overflow: ellipsis;
  color: var(--text-light);
  font-size: 0.875rem;
  margin-top: 0.5rem;
}

@media (min-width: 640px) {
  .order-meta {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .order-actions {
    justify-content: flex-start;
  }
}
</style>

<main class="deliveries-page">
  <div class="deliveries-container">
    <h2>As Minhas Encomendas Recebidas</h2>
    
    <?php if (empty($deliveries)): ?>
      <div class="empty-state">
        <p>Ainda não recebeste encomendas.</p>
      </div>
    <?php else: ?>
      <ul class="order-list">
        <?php foreach ($deliveries as $order): ?>
          <li class="order-card" id="order-<?= $order['id'] ?>">
            <div class="order-meta">
              <div class="order-meta-item">
                <strong>Cliente:</strong>
                <span><?= htmlspecialchars($order['client_name']) ?></span>
              </div>
              
              <div class="order-meta-item">
                <strong>Email:</strong>
                <span><?= htmlspecialchars($order['client_email']) ?></span>
              </div>
              
              <div class="order-meta-item">
                <strong>Serviço:</strong>
                <span><?= htmlspecialchars($order['title']) ?></span>
              </div>
              
              <div class="order-meta-item">
                <strong>Data:</strong>
                <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
              </div>
              
              <div class="order-meta-item">
                <strong>Estado:</strong>
                <span class="order-status status-<?= $order['status'] ?>">
                  <?= htmlspecialchars(ucfirst($order['status'])) ?>
                </span>
              </div>
            </div>
            
            <?php if ($order['content']): ?>
              <div>
                <strong>Mensagem:</strong>
                <p class="message-preview"><?= htmlspecialchars($order['content']) ?></p>
              </div>
            <?php endif; ?>
            
            <div class="order-actions">
              <?php if ($order['status'] === 'pending'): ?>
                <button onclick="updateStatus(<?= $order['id'] ?>, 'accept')" class="btn btn-primary">Aceitar</button>
                <button onclick="updateStatus(<?= $order['id'] ?>, 'reject')" class="btn btn-secondary">Recusar</button>
                <button onclick="updateStatus(<?= $order['id'] ?>, 'delete')" class="btn btn-danger">Remover</button>
              <?php endif; ?>
              
              <a href="../chat/chat.php?freelancer_id=<?= $freelancerId ?>&client_id=<?= $order['client_id'] ?>" class="btn btn-chat">
                Ver Conversa
              </a>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</main>

<script>
  const CSRF_TOKEN = <?= json_encode($_SESSION['csrf_token']) ?>;
  async function updateStatus(transactionId, action) {
    if (action === 'delete' && !confirm('Tens a certeza que queres remover esta encomenda?')) {
      return;
    }
    
    if (action === 'reject' && !confirm('Tens a certeza que queres recusar esta encomenda?')) {
      return;
    }
    
    try {
      const response = await fetch('../order/update_status.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `transaction_id=${transactionId}&action=${action}&csrf_token=${encodeURIComponent(CSRF_TOKEN)}`
      });
      
      if (!response.ok) {
        throw new Error('Erro na resposta do servidor');
      }
      
      const item = document.getElementById('order-' + transactionId);
      
      if (action === 'delete') {
        item.remove();
        
        if (document.querySelectorAll('.order-card').length === 0) {
          document.querySelector('.order-list').innerHTML = `
            <div class="empty-state">
              <p>Não tens encomendas no momento.</p>
            </div>
          `;
        }
      } else {
        const statusSpan = item.querySelector('.order-status');
        let newStatus, newClass;
        
        switch (action) {
          case 'accept':
            newStatus = 'In Progress';
            newClass = 'status-in-progress';
            break;
          case 'reject':
            newStatus = 'Rejected';
            newClass = 'status-rejected';
            break;
          case 'complete':
            newStatus = 'Completed';
            newClass = 'status-completed';
            break;
          case 'received':
            newStatus = 'Received';
            newClass = 'status-received';
            break;
        }
        
        statusSpan.textContent = newStatus;
        statusSpan.className = `order-status ${newClass}`;
        
        if (action === 'accept' || action === 'reject') {
          const buttons = item.querySelectorAll('.btn-primary, .btn-secondary, .btn-danger');
          buttons.forEach(btn => btn.remove());
        }
      }
    } catch (error) {
      console.error('Erro:', error);
      alert('Ocorreu um erro ao atualizar o estado da encomenda.');
    }
  }
</script>

<?php include('../../includes/footer.php'); ?>