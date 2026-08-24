const searchInput = document.getElementById('service-search');
const resultsDiv = document.getElementById('search-results');
const mainCards = document.getElementById('main-service-cards');

async function fetchSearchResults(query) {
  const params = new URLSearchParams({ q: query });
  const res = await fetch(`../../pages/search/search_service.php?${params.toString()}`);
  return await res.json();
}

function renderServiceCard(service) {
  const card = document.createElement('a');
  card.href = `service_detail.php?id=${service.id}`;
  card.className = 'search-result-item';
  card.style.display = 'flex';
  card.style.alignItems = 'center';
  card.style.gap = '16px';
  card.style.maxWidth = '700px';
  card.style.margin = '10px auto';
  card.style.padding = '16px';
  card.style.border = '1px solid #ddd';
  card.style.borderRadius = '10px';
  card.style.backgroundColor = '#fff';
  card.style.boxShadow = '0 2px 6px rgba(0,0,0,0.05)';
  card.style.textDecoration = 'none';
  card.style.color = '#333';

  const img = document.createElement('img');
  img.src = service.preview_image ? '/' + service.preview_image : '/assets/img/default-service.png';
  img.alt = `Preview of ${service.title}`;
  img.style.width = '100px';
  img.style.height = '100px';
  img.style.objectFit = 'cover';
  img.style.borderRadius = '8px';
  img.style.flexShrink = '0';
  img.style.border = '1px solid #ccc';

  const infoDiv = document.createElement('div');
  infoDiv.className = 'info';
  infoDiv.style.display = 'flex';
  infoDiv.style.flexDirection = 'column';
  infoDiv.style.flexGrow = '1';
  infoDiv.innerHTML = `
    <strong style="font-size: 1.1em; color: #161036;">${service.title}</strong>
    <span style="color: #666;">Freelancer: ${service.username}</span>
    <span style="color: #666;">Categoria: ${service.category}</span>
    <span style="color: #888;">Entrega: ${service.delivery_time ? service.delivery_time + ' dias' : 'n/d'}</span>
    <span style="color: #999;">Rating: ${service.rating}</span>
  `;

  const price = document.createElement('div');
  price.className = 'price';
  price.textContent = service.price !== undefined && service.price !== null ? service.price + '€' : 'n/d';
  price.style.fontWeight = 'bold';
  price.style.fontSize = '1.2em';
  price.style.color = '#4b0082';

  card.appendChild(img);
  card.appendChild(infoDiv);
  card.appendChild(price);

  return card;
}

searchInput.addEventListener('input', async () => {
  const query = searchInput.value.trim();
  if (query.length === 0) {
    resultsDiv.innerHTML = '';
    return;
  }

  const data = await fetchSearchResults(query);
  resultsDiv.innerHTML = '';

  if (!data.results || data.results.length === 0) {
    resultsDiv.innerHTML = '<p style="text-align:center;">Nenhum serviço encontrado.</p>';
    return;
  }

  data.results.forEach(service => {
    resultsDiv.appendChild(renderServiceCard(service));
  });
});

searchInput.addEventListener('keydown', async e => {
  if (e.key === 'Enter') {
    e.preventDefault();
    const query = searchInput.value.trim();
    if (query.length === 0) return;

    const data = await fetchSearchResults(query);
    mainCards.innerHTML = '';

    if (!data.results || data.results.length === 0) {
      mainCards.innerHTML = '<p style="text-align:center;">Nenhum serviço encontrado.</p>';
      return;
    }

    data.results.forEach(service => {
      mainCards.appendChild(renderServiceCard(service));
    });

    resultsDiv.innerHTML = '';
  }
});