<?php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
require_once '../../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

$userId = $_SESSION['user_id'];
if (!empty($_SESSION['is_client'])) {
    $role = 'client';
} elseif (!empty($_SESSION['is_freelancer'])) {
    $role = 'freelancer';
} else {
    header('Location: ../auth/login.php');
    exit();
}

if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT i.receiver_id AS user_id, u.username, u.profile_picture, 
                                  MAX(i.sent_at) as last_message
                           FROM Inquiry i JOIN User u ON u.id = i.receiver_id
                           WHERE i.sender_id = :uid AND (i.archived_by IS NULL OR i.archived_by != :uid)
                           GROUP BY i.receiver_id
                           ORDER BY last_message DESC");
} else {
    $stmt = $pdo->prepare("SELECT i.sender_id AS user_id, u.username, u.profile_picture, 
                                  MAX(i.sent_at) as last_message
                           FROM Inquiry i JOIN User u ON u.id = i.sender_id
                           WHERE i.receiver_id = :uid AND (i.archived_by IS NULL OR i.archived_by != :uid)
                           GROUP BY i.sender_id
                           ORDER BY last_message DESC");
}
$stmt->execute([':uid' => $userId]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($role === 'client') {
    $stmt = $pdo->prepare("SELECT i.receiver_id AS user_id, u.username, u.profile_picture, 
                                  MAX(i.sent_at) as last_message
                           FROM Inquiry i JOIN User u ON u.id = i.receiver_id
                           WHERE i.sender_id = :uid AND i.archived_by = :uid
                           GROUP BY i.receiver_id
                           ORDER BY last_message DESC");
} else {
    $stmt = $pdo->prepare("SELECT i.sender_id AS user_id, u.username, u.profile_picture, 
                                  MAX(i.sent_at) as last_message
                           FROM Inquiry i JOIN User u ON u.id = i.sender_id
                           WHERE i.receiver_id = :uid AND i.archived_by = :uid
                           GROUP BY i.sender_id
                           ORDER BY last_message DESC");
}
$stmt->execute([':uid' => $userId]);
$archived = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
  --error-bg: #fff5f5;
  --error-text: #e53e3e;
  --radius-sm: 0.375rem;
  --radius-md: 0.5rem;
  --radius-lg: 0.75rem;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.inbox-page {
  font-family: 'Inter', system-ui, sans-serif;
  background-color: var(--secondary-color);
  min-height: calc(100vh - 120px);
  padding: 2rem 1rem;
}

.inbox-container {
  max-width: 800px;
  margin: 0 auto;
}

.inbox-header {
  text-align: center;
  margin-bottom: 2rem;
}

.inbox-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--primary-color);
  margin: 0 0 0.5rem;
}

.inbox-header p {
  color: var(--text-light);
  margin: 0;
}

.conversation-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 1rem;
}

.conversation-item {
  background-color: var(--card-bg);
  border-radius: var(--radius-md);
  padding: 1.25rem;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--border-color);
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 1rem;
}

.conversation-item:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
}

.conversation-avatar {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid var(--border-color);
  flex-shrink: 0;
}

.conversation-content {
  flex: 1;
  min-width: 0;
}

.conversation-user {
  font-weight: 600;
  color: var(--text-color);
  margin: 0;
}

.conversation-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.25rem;
  font-size: 0.875rem;
}

.conversation-time {
  color: var(--text-light);
}

.conversation-actions {
  display: flex;
  gap: 0.5rem;
  margin-left: auto;
  flex-shrink: 0;
}

.btn {
  padding: 0.5rem 0.75rem;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  border: none;
  font-size: 0.875rem;
  font-weight: 500;
}

.btn-secondary {
  background-color: var(--border-color);
  color: var(--text-color);
}

.btn-secondary:hover {
  background-color: #d1d5db;
}

.btn-primary {
  background-color: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background-color: var(--primary-hover);
}

.btn-danger {
  background-color: #e53e3e;
  color: white;
}

.btn-danger:hover {
  background-color: #c53030;
}

.archived-section {
  margin-top: 2.5rem;
}

.archived-toggle {
  background: none;
  border: none;
  color: var(--text-light);
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  padding: 0.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.archived-toggle:hover {
  color: var(--text-color);
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: var(--text-light);
}

.empty-state p {
  margin-top: 0.5rem;
}

@media (max-width: 640px) {
  .conversation-item {
    flex-wrap: wrap;
  }
  
  .conversation-actions {
    margin-left: 0;
    margin-top: 0.5rem;
    width: 100%;
    justify-content: flex-end;
  }
}
</style>

<main class="inbox-page">
  <div class="inbox-container">
    <div class="inbox-header">
      <h2>Minhas Conversas</h2>
      <p>Gerencie suas mensagens e conversas</p>
    </div>
    
    <ul class="conversation-list">
      <?php if (empty($conversations)): ?>
        <li class="empty-state">
          <p>Não há conversas ativas no momento.</p>
        </li>
      <?php else: ?>
        <?php foreach ($conversations as $conv): ?>
          <li class="conversation-item">
            <a href="chat.php?<?= $role === 'client' ? 'freelancer_id' : 'client_id' ?>=<?= $conv['user_id'] ?>" 
               style="display: flex; align-items: center; gap: 1rem; flex: 1; text-decoration: none; color: inherit;">
              <img src="<?= !empty($conv['profile_picture']) ? '../' . htmlspecialchars($conv['profile_picture']) : '../img/default-profile.png' ?>" 
                   alt="Foto de perfil" 
                   class="conversation-avatar">
              
              <div class="conversation-content">
                <h3 class="conversation-user"><?= htmlspecialchars($conv['username']) ?></h3>
                <div class="conversation-meta">
                  <span class="conversation-time"><?= date('d/m/Y H:i', strtotime($conv['last_message'])) ?></span>
                </div>
              </div>
            </a>
            
            <div class="conversation-actions">
              <form method="POST" action="manage_conversation.php" style="display: inline;">
                <input type="hidden" name="user_id" value="<?= $conv['user_id'] ?>">
                <input type="hidden" name="role" value="<?= $role ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" name="action" value="archive" class="btn btn-secondary">Arquivar</button>
              </form>
              
              <form method="POST" action="manage_conversation.php" style="display: inline;">
                <input type="hidden" name="user_id" value="<?= $conv['user_id'] ?>">
                <input type="hidden" name="role" value="<?= $role ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" name="action" value="delete" class="btn btn-danger">Remover</button>
              </form>
            </div>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>
    
    <div class="archived-section">
      <button class="archived-toggle" onclick="this.nextElementSibling.hidden = !this.nextElementSibling.hidden">
        Conversas Arquivadas
      </button>
      
      <div hidden>
        <ul class="conversation-list" style="margin-top: 1rem;">
          <?php if (empty($archived)): ?>
            <li class="empty-state">
              <p>Nenhuma conversa arquivada.</p>
            </li>
          <?php else: ?>
            <?php foreach ($archived as $conv): ?>
              <li class="conversation-item">
                <a href="chat.php?<?= $role === 'client' ? 'freelancer_id' : 'client_id' ?>=<?= $conv['user_id'] ?>" 
                   style="display: flex; align-items: center; gap: 1rem; flex: 1; text-decoration: none; color: inherit;">
                  <img src="<?= !empty($conv['profile_picture']) ? '../' . htmlspecialchars($conv['profile_picture']) : '../img/default-profile.png' ?>" 
                       alt="Foto de perfil" 
                       class="conversation-avatar">
                  
                  <div class="conversation-content">
                    <h3 class="conversation-user"><?= htmlspecialchars($conv['username']) ?></h3>
                    <div class="conversation-meta">
                      <span class="conversation-time"><?= date('d/m/Y H:i', strtotime($conv['last_message'])) ?></span>
                    </div>
                  </div>
                </a>
                
                <div class="conversation-actions">
                  <form method="POST" action="manage_conversation.php" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?= $conv['user_id'] ?>">
                    <input type="hidden" name="role" value="<?= $role ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" name="action" value="unarchive" class="btn btn-primary">Restaurar</button>
                  </form>
                  
                  <form method="POST" action="manage_conversation.php" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?= $conv['user_id'] ?>">
                    <input type="hidden" name="role" value="<?= $role ?>">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <button type="submit" name="action" value="delete" class="btn btn-danger">Remover</button>
                  </form>
                </div>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</main>

<?php include('../../includes/footer.php'); ?>