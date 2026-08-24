<?php 
session_start();
require_once '../../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}
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

body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
  background-color: var(--light);
  color: var(--dark);
  line-height: 1.6;
  margin: 0;
  padding: 0;
}

.main-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--space-xl);
}

.search-container {
  margin: var(--space-xl) auto;
  text-align: center;
  position: relative;
  max-width: 800px;
}

.search-input {
  width: 100%;
  padding: var(--space-md) var(--space-lg);
  font-size: 1.1rem;
  border: 2px solid var(--gray-light);
  border-radius: var(--radius-lg);
  background-color: var(--white);
  box-shadow: var(--shadow-sm);
  transition: var(--transition);
  padding-left: 3rem;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-light);
  box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.2);
}

.search-icon {
  position: absolute;
  left: var(--space-lg);
  top: 50%;
  transform: translateY(-50%);
  color: var(--gray);
}

.page-layout {
  display: grid;
  grid-template-columns: 280px 1fr;
  gap: var(--space-xl);
  margin-top: var(--space-xl);
}

.filter-sidebar {
  background: var(--white);
  padding: var(--space-lg);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-light);
  height: fit-content;
  position: sticky;
  top: var(--space-xl);
}

.section-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: var(--space-md);
  color: var(--dark);
  display: flex;
  align-items: center;
  gap: var(--space-sm);
}

.section-title svg {
  width: 20px;
  height: 20px;
}

.filter-group {
  margin-bottom: var(--space-xl);
}

.filter-label {
  display: block;
  margin-bottom: var(--space-xs);
  font-weight: 600;
  font-size: 0.9375rem;
  color: var(--gray-dark);
}

.filter-select, .filter-input {
  width: 100%;
  padding: var(--space-sm) var(--space-md);
  border: 1px solid var(--gray-light);
  border-radius: var(--radius-md);
  font-size: 0.9375rem;
  background-color: var(--white);
  transition: var(--transition);
}

.filter-select:focus, .filter-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.1);
}

.price-range {
  display: flex;
  gap: var(--space-sm);
  margin-top: var(--space-xs);
}

.price-range input {
  flex: 1;
}

.star-rating-filter {
  display: flex;
  gap: var(--space-xs);
  margin-top: var(--space-xs);
}

.star-rating-filter span {
  font-size: 1.5rem;
  color: var(--gray-light);
  cursor: pointer;
  transition: var(--transition);
}

.star-rating-filter span.active {
  color: #F59E0B;
}

.filter-button {
  width: 100%;
  padding: var(--space-md);
  background: linear-gradient(135deg, var(--primary), var(--primary-dark));
  color: var(--white);
  border: none;
  border-radius: var(--radius-md);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  margin-top: var(--space-sm);
  box-shadow: var(--shadow-sm);
}

.filter-button:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.reset-button {
  width: 100%;
  padding: var(--space-md);
  background: var(--light);
  color: var(--gray-dark);
  border: 1px solid var(--gray-light);
  border-radius: var(--radius-md);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  margin-top: var(--space-sm);
}

.reset-button:hover {
  background: var(--gray-light);
}

.main-content {
  display: flex;
  flex-direction: column;
  gap: var(--space-xl);
}

.services-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: var(--space-lg);
}

.service-card {
  background: var(--white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-light);
  transition: var(--transition);
  display: flex;
  flex-direction: column;
  text-decoration: none;
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-light);
}

.service-image {
  width: 100%;
  height: 220px;
  object-fit: cover;
}

.service-content {
  padding: var(--space-lg);
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.service-title {
  font-size: 1.25rem;
  font-weight: 700;
  margin-bottom: var(--space-sm);
  color: var(--dark);
}

.service-meta {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  margin-bottom: var(--space-md);
}

.service-category {
  background: var(--primary-light);
  color: var(--white);
  padding: var(--space-xs) var(--space-md);
  border-radius: var(--radius-full);
  font-size: 0.8125rem;
  font-weight: 500;
}

.service-rating {
  display: flex;
  align-items: center;
  gap: var(--space-xs);
  font-size: 0.9375rem;
  color: var(--gray-dark);
}

.star-filled {
  color: #F59E0B;
}

.service-freelancer {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  margin-bottom: var(--space-md);
}

.freelancer-avatar {
  width: 32px;
  height: 32px;
  border-radius: var(--radius-full);
  object-fit: cover;
}

.freelancer-name {
  font-size: 0.9375rem;
  color: var(--gray-dark);
}

.service-details {
  margin-top: auto;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
}

.service-price {
  font-size: 1.5rem;
  font-weight: 800;
  color: var(--primary);
}

.service-delivery {
  font-size: 0.9375rem;
  color: var(--gray);
}

.no-results {
  grid-column: 1 / -1;
  text-align: center;
  padding: var(--space-xl);
  background: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-sm);
  border: 1px solid var(--gray-light);
}

@media (max-width: 1024px) {
  .page-layout {
    grid-template-columns: 1fr;
  }
  
  .filter-sidebar {
    position: static;
  }
  
  .services-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  }
}

@media (max-width: 768px) {
  .services-grid {
    grid-template-columns: 1fr;
  }
  
  .search-container {
    padding: 0 var(--space-md);
  }
  
  .main-container {
    padding: var(--space-md);
  }
}
</style>

<div class="main-container">
  <div class="search-container">
    <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="11" cy="11" r="8"></circle>
      <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
    </svg>
    <input type="text" id="service-search" class="search-input" placeholder="Pesquisar serviços...">
  </div>
  
  <div id="search-results"></div>

  <div class="page-layout">
    <aside class="filter-sidebar">
      <h2 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" y1="21" x2="4" y2="14"></line>
          <line x1="4" y1="10" x2="4" y2="3"></line>
          <line x1="12" y1="21" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12" y2="3"></line>
          <line x1="20" y1="21" x2="20" y2="16"></line>
          <line x1="20" y1="12" x2="20" y2="3"></line>
          <line x1="1" y1="14" x2="7" y2="14"></line>
          <line x1="9" y1="8" x2="15" y2="8"></line>
          <line x1="17" y1="16" x2="23" y2="16"></line>
        </svg>
        Filtros
      </h2>
      
      <form id="filter-form">
        <div class="filter-group">
          <label for="sort" class="filter-label">Ordenar por</label>
          <select id="sort" name="sort" class="filter-select">
            <option value="price_asc">Preço Crescente</option>
            <option value="price_desc">Preço Decrescente</option>
            <option value="rating">Melhor Avaliados</option>
            <option value="newest">Mais Recentes</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label for="category" class="filter-label">Categoria</label>
          <select id="category" name="category" class="filter-select">
            <option value="">Todas as categorias</option>
          </select>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Faixa de Preço</label>
          <div class="price-range">
            <input type="number" id="min_price" name="min_price" placeholder="Mínimo" class="filter-input" min="0" step="0.01">
            <input type="number" id="max_price" name="max_price" placeholder="Máximo" class="filter-input" min="0" step="0.01">
          </div>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Prazo de Entrega</label>
          <div class="price-range">
            <input type="number" id="min_days" name="min_days" placeholder="Mínimo" class="filter-input" min="0">
            <input type="number" id="max_days" name="max_days" placeholder="Máximo" class="filter-input" min="0">
          </div>
        </div>
        
        <div class="filter-group">
          <label class="filter-label">Avaliação Mínima</label>
          <div class="star-rating-filter">
            <span data-value="1">☆</span>
            <span data-value="2">☆</span>
            <span data-value="3">☆</span>
            <span data-value="4">☆</span>
            <span data-value="5">☆</span>
            <input type="hidden" id="min_rating" name="min_rating" value="">
          </div>
        </div>
        
        <button type="submit" class="filter-button">Aplicar Filtros</button>
        <button type="button" id="reset-filters" class="reset-button">Limpar Filtros</button>
      </form>
    </aside>
    
    <main class="main-content">
      <h2 class="section-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        Serviços Recomendados
      </h2>
      
      <div id="main-service-cards" class="services-grid">
        <?php
        $stmt = $pdo->query("
          SELECT s.*, u.username, u.profile_picture, c.name AS category
          FROM Service s
          JOIN User u ON s.user_id = u.id
          LEFT JOIN Category c ON s.category_id = c.id
          WHERE s.status = 'active'
          ORDER BY RANDOM()
          LIMIT 6
        ");
        $services = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($services as $service): 
          $stmtRating = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM Review r JOIN ServiceTransaction t ON r.transaction_id = t.id WHERE t.service_id = ?");
          $stmtRating->execute([$service['id']]);
          $ratingData = $stmtRating->fetch(PDO::FETCH_ASSOC);
          $avgRating = $ratingData['avg_rating'] ? number_format($ratingData['avg_rating'], 1) : null;

          $profilePic = $service['profile_picture'] && file_exists($service['profile_picture']) ? $service['profile_picture'] : '../../assets/img/default-profile.png';

          $imageDir = '../../assets/img/service_images/' . $service['id'] . '/';
          $images = file_exists($imageDir) ? glob($imageDir . '*') : [];
          $previewImage = !empty($images) ? $images[0] : '../../assets/img/default-service.png';
        ?>
          <a href="../service/service_detail.php?id=<?= $service['id'] ?>" class="service-card">
            <img src="<?= htmlspecialchars($previewImage) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-image">
            
            <div class="service-content">
              <h3 class="service-title"><?= htmlspecialchars($service['title']) ?></h3>
              
              <div class="service-meta">
                <span class="service-category"><?= isset($service['category']) ? htmlspecialchars($service['category']) : 'Geral' ?></span>
                
                <?php if ($avgRating): ?>
                  <span class="service-rating">
                    <?php
                      $fullStars = floor($avgRating);
                      $halfStar = ($avgRating - $fullStars) >= 0.5 ? 1 : 0;
                      
                      for ($i = 0; $i < $fullStars; $i++) echo '<span class="star-filled">★</span>';
                      if ($halfStar) echo '<span class="star-filled">½</span>';
                      for ($i = 0; $i < (5 - $fullStars - $halfStar); $i++) echo '<span>☆</span>';
                    ?>
                    (<?= $avgRating ?>)
                  </span>
                <?php endif; ?>
              </div>
              
              <div class="service-freelancer">
                <img src="<?= htmlspecialchars($profilePic) ?>" alt="<?= htmlspecialchars($service['username']) ?>" class="freelancer-avatar">
                <span class="freelancer-name"><?= htmlspecialchars($service['username']) ?></span>
              </div>
              
              <div class="service-details">
                <span class="service-price"><?= number_format($service['price'], 2) ?>€</span>
                <span class="service-delivery"><?= htmlspecialchars($service['delivery_time']) ?> dias</span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</div>

<?php include('../../includes/footer.php'); ?>

<script>

document.addEventListener('DOMContentLoaded', function() {
  fetch('../category/get_categories.php')
    .then(response => response.json())
    .then(data => {
      const categorySelect = document.getElementById('category');
      const categories = Array.isArray(data) ? data : [];
      categories.forEach(cat => {
        const option = document.createElement('option');
        option.value = cat.id;
        option.textContent = cat.name;
        categorySelect.appendChild(option);
      });
    });
});

document.querySelectorAll('.star-rating-filter span').forEach(star => {
  star.addEventListener('click', function() {
    const selected = parseInt(this.dataset.value);
    document.getElementById('min_rating').value = selected;

    document.querySelectorAll('.star-rating-filter span').forEach((s, index) => {
      const value = index + 1;
      s.textContent = value <= selected ? '★' : '☆';
      s.classList.toggle('active', value <= selected);
    });
  });
});

document.getElementById('reset-filters').addEventListener('click', () => {
  document.getElementById('filter-form').reset();
  document.getElementById('min_rating').value = '';
  document.querySelectorAll('.star-rating-filter span').forEach(s => {
    s.textContent = '☆';
    s.classList.remove('active');
  });
  document.getElementById('filter-form').dispatchEvent(new Event('submit'));
});

document.getElementById('service-search').addEventListener('input', debounce(async function() {
  const query = this.value.trim();
  if (query.length === 0) {
    document.getElementById('search-results').innerHTML = '';
    return;
  }
  
  try {
    const response = await fetch(`../search/search_service.php?q=${encodeURIComponent(query)}`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    
    let html = '';
    if (!data.results || data.results.length === 0) {
      html = '<div class="no-results"><p>Nenhum serviço encontrado com sua pesquisa.</p></div>';
    } else {
      html = '<div class="services-grid">';
      data.results.forEach(service => {
        html += `
          <a href="../service/service_detail.php?id=${service.id}" class="service-card">
            <img src="${service.preview_image ? (service.preview_image.startsWith('img/') ? '../' + service.preview_image : service.preview_image) : '../../assets/img/default-service.png'}" alt="${service.title}" class="service-image">
            
            <div class="service-content">
              <h3 class="service-title">${service.title}</h3>
              
              <div class="service-meta">
                <span class="service-category">${service.category || 'Geral'}</span>
                
                ${service.rating ? `
                  <span class="service-rating">
                    ${'★'.repeat(Math.floor(service.rating))}${service.rating % 1 >= 0.5 ? '½' : ''}${'☆'.repeat(5 - Math.ceil(service.rating))}
                    (${service.rating.toFixed(1)})
                  </span>
                ` : ''}
              </div>
              
              <div class="service-freelancer">
                <img src="${service.profile_picture || '../../assets/img/default-profile.png'}" alt="${service.username}" class="freelancer-avatar">
                <span class="freelancer-name">${service.username}</span>
              </div>
              
              <div class="service-details">
                <span class="service-price">${parseFloat(service.price).toFixed(2)}€</span>
                <span class="service-delivery">${service.delivery_time} dias</span>
              </div>
            </div>
          </a>
        `;
      });
      html += '</div>';
    }
    document.getElementById('search-results').innerHTML = html;
  } catch (error) {
    console.error('Fetch error:', error);
    document.getElementById('search-results').innerHTML = '<div class="no-results"><p>Ocorreu um erro ao buscar os resultados.</p></div>';
  }
}, 300));

document.getElementById('filter-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const params = new URLSearchParams();
  
  for (const [key, value] of formData.entries()) {
    if (value) params.append(key, value);
  }
  
  try {
    const response = await fetch(`../search/search_service.php?${params.toString()}`);
    if (!response.ok) throw new Error('Network response was not ok');
    const data = await response.json();
    
    const cardContainer = document.getElementById('main-service-cards');
    cardContainer.innerHTML = '';
    
    if (!data.results || data.results.length === 0) {
      cardContainer.innerHTML = '<div class="no-results"><p>Nenhum serviço encontrado com os filtros selecionados.</p></div>';
      return;
    }
    
    data.results.forEach(service => {
      const card = document.createElement('a');
      card.href = `../service/service_detail.php?id=${service.id}`;
      card.className = 'service-card';
      
      card.innerHTML = `
        <img src="${service.preview_image ? (service.preview_image.startsWith('img/') ? '../' + service.preview_image : service.preview_image) : '../img/default-service.png'}" alt="${service.title}" class="service-image">
        
        <div class="service-content">
          <h3 class="service-title">${service.title}</h3>
          
          <div class="service-meta">
            <span class="service-category">${service.category || 'Geral'}</span>
            
            ${service.rating ? `
              <span class="service-rating">
                ${'★'.repeat(Math.floor(service.rating))}${service.rating % 1 >= 0.5 ? '½' : ''}${'☆'.repeat(5 - Math.ceil(service.rating))}
                (${service.rating.toFixed(1)})
              </span>
            ` : ''}
          </div>
          
          <div class="service-freelancer">
            <img src="${service.profile_picture || '../../assets/img/default-profile.png'}" alt="${service.username}" class="freelancer-avatar">
            <span class="freelancer-name">${service.username}</span>
          </div>
          
          <div class="service-details">
            <span class="service-price">${parseFloat(service.price).toFixed(2)}€</span>
            <span class="service-delivery">${service.delivery_time} dias</span>
          </div>
        </div>
      `;
      
      cardContainer.appendChild(card);
    });
  } catch (error) {
    console.error('Fetch error:', error);
    cardContainer.innerHTML = '<div class="no-results"><p>Ocorreu um erro ao filtrar os serviços.</p></div>';
  }
});

function debounce(func, wait) {
  let timeout;
  return function() {
    const context = this, args = arguments;
    clearTimeout(timeout);
    timeout = setTimeout(() => func.apply(context, args), wait);
  };
}
</script>