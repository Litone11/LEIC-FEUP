<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<?php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<header class="freelancer-header">
  <nav class="freelancer-nav">
    <div class="logo-container">
      <a href="../../pages/home/freelancer_homepage.php" class="logo-link">
        <img src="../../assets/img/image1.png" alt="SkillSwap Logo" class="logo-image">
      </a>
    </div>
    
    <div class="main-nav">
      <a href="../../pages/order/in_progress.php" class="nav-button">
        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          <path d="M14.05 2a9 9 0 0 1 8 7.94"></path>
          <path d="M14.05 6A5 5 0 0 1 18 10"></path>
        </svg>
        <span class="nav-text">Projetos Ativos</span>
      </a>
      
      <a href="../../pages/order/deliveries.php" class="nav-button">
        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 20h9"></path>
          <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
        </svg>
        <span class="nav-text">Entregas Recebidas</span>
      </a>
      
      <a href="../../pages/chat/inbox.php" class="nav-button">
        <svg class="nav-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
        <span class="nav-text">Mensagens</span>
      </a>
    </div>
    
    <div class="user-area">
      <?php if (!empty($_SESSION['is_admin'])): ?>
        <a href="../../pages/home/admin_dashboard.php" class="admin-button">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
            <path d="M2 17l10 5 10-5"></path>
            <path d="M2 12l10 5 10-5"></path>
          </svg>
          Admin
        </a>
      <?php endif; ?>
      
      <div class="user-dropdown">
        <?php
          require_once __DIR__ . '/../database/db.php';
          $stmt = $pdo->prepare("SELECT profile_picture, username FROM User WHERE id = ?");
          $stmt->execute([$_SESSION['user_id']]);
          $user = $stmt->fetch();
          $imgSrc = isset($user['profile_picture']) && file_exists($user['profile_picture']) ? $user['profile_picture'] : '../../assets/img/default-profile.png';
        ?>
        <button class="user-button">
          <img src="<?= htmlspecialchars($imgSrc) ?>" alt="Perfil" class="user-avatar">
          <span class="user-name"><?= htmlspecialchars($user['username']) ?></span>
          <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
        
        <div class="dropdown-menu">
          <a href="../../pages/profile/profile.php" class="dropdown-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
            Meu Perfil
          </a>
          <div class="dropdown-divider"></div>
          <a href="../../pages/auth/logout.php" class="dropdown-item logout-item">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            Sair
          </a>
        </div>
      </div>
    </div>
  </nav>
</header>

<style>
:root {
  --primary: #1E88E5;
  --primary-light: #64B5F6;
  --primary-dark: #1565C0;
  --secondary: #10B981;
  --dark: #1F2937;
  --dark-bg: #0D47A1;
  --gray-dark: #374151;
  --gray: #6B7280;
  --gray-light: #E5E7EB;
  --light: #F9FAFB;
  --white: #FFFFFF;
  --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.12);
  --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
  --transition: all 0.2s ease;
}

.freelancer-header {
  background-color: var(--dark-bg);
  box-shadow: var(--shadow-md);
  position: sticky;
  top: 0;
  z-index: 1000;
  padding: 0.75rem 2rem;
  border-radius: 0 0 16px 16px;
}

.freelancer-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  max-width: 1400px;
  margin: 0 auto;
  gap: 1.5rem;
}

.logo-container {
  flex-shrink: 0;
}

.logo-image {
  height: 2.8rem;
  transition: transform 0.3s ease;
}

.logo-image:hover {
  transform: scale(1.05);
}

.main-nav {
  display: flex;
  gap: 1rem;
  margin-right: auto;
  margin-left: 2rem;
}

.nav-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1.2rem;
  background-color: rgba(255, 255, 255, 0.1);
  color: var(--white);
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  transition: var(--transition);
  position: relative;
}

.nav-button:hover {
  background-color: rgba(255, 255, 255, 0.2);
  transform: translateY(-1px);
}

.nav-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.notification-badge {
  position: absolute;
  top: -0.5rem;
  right: -0.5rem;
  background-color: var(--secondary);
  color: white;
  border-radius: 50%;
  width: 1.25rem;
  height: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: bold;
}

.user-area {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.admin-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  background-color: var(--secondary);
  color: var(--white);
  border-radius: 0.5rem;
  text-decoration: none;
  font-weight: 500;
  font-size: 0.95rem;
  transition: var(--transition);
}

.admin-button:hover {
  background-color: #0e9d6e;
  transform: translateY(-1px);
}

.user-dropdown {
  position: relative;
}

.user-button {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem 1rem;
  border-radius: 0.5rem;
  transition: var(--transition);
}

.user-button:hover {
  background-color: rgba(255, 255, 255, 0.1);
}

.user-avatar {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid rgba(255, 255, 255, 0.2);
}

.user-name {
  color: var(--white);
  font-weight: 500;
  font-size: 0.95rem;
}

.dropdown-arrow {
  transition: transform 0.2s ease;
}

.user-dropdown:hover .dropdown-arrow {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  right: 0;
  top: 100%;
  margin-top: 0.5rem;
  min-width: 12rem;
  background-color: var(--white);
  border-radius: 0.5rem;
  box-shadow: var(--shadow-lg);
  padding: 0.5rem 0;
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
  z-index: 100;
}

.user-dropdown:hover .dropdown-menu {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1.5rem;
  color: var(--gray-dark);
  text-decoration: none;
  font-size: 0.9rem;
  transition: var(--transition);
}

.dropdown-item:hover {
  background-color: var(--light);
  color: var(--primary);
}

.dropdown-item svg {
  width: 1rem;
  height: 1rem;
  color: var(--gray);
}

.dropdown-item:hover svg {
  color: var(--primary);
}

.dropdown-divider {
  height: 1px;
  background-color: var(--gray-light);
  margin: 0.5rem 0;
}

.logout-item {
  color: #EF4444;
}

.logout-item:hover {
  color: #DC2626;
  background-color: rgba(239, 68, 68, 0.1);
}

@media (max-width: 1024px) {
  .freelancer-nav {
    gap: 1rem;
  }
  
  .main-nav {
    margin-left: 1rem;
  }
  
  .nav-button .nav-text {
    display: none;
  }
  
  .nav-button {
    padding: 0.6rem;
  }
  
  .user-name {
    display: none;
  }
}

@media (max-width: 768px) {
  .freelancer-header {
    padding: 0.75rem 1rem;
  }
  
  .logo-image {
    height: 2.5rem;
  }
  
  .admin-button span {
    display: none;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const userButton = document.querySelector('.user-button');
  const dropdownMenu = document.querySelector('.dropdown-menu');
  
  userButton.addEventListener('click', function(e) {
    e.stopPropagation();
    const isVisible = dropdownMenu.style.opacity === '1';
    dropdownMenu.style.opacity = isVisible ? '0' : '1';
    dropdownMenu.style.visibility = isVisible ? 'hidden' : 'visible';
    dropdownMenu.style.transform = isVisible ? 'translateY(-10px)' : 'translateY(0)';
  });
  
  document.addEventListener('click', function() {
    dropdownMenu.style.opacity = '0';
    dropdownMenu.style.visibility = 'hidden';
    dropdownMenu.style.transform = 'translateY(-10px)';
  });
});
</script>