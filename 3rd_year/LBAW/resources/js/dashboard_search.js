export function setupDashboardSearch() {

    const searchInput = document.getElementById('dashboardSearch');
    const resultsDiv = document.getElementById('searchResults');
    const dashboardContent = document.getElementById('dashboardContent');

    if (!searchInput || !resultsDiv || !dashboardContent) return;

    let debounceId;
    searchInput.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            const query = searchInput.value.trim();

            if (!query) {
                dashboardContent.classList.remove('hidden');
                resultsDiv.classList.add('hidden');
                resultsDiv.innerHTML = '';
                return;
            }

            fetch(`/search?search=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                resultsDiv.innerHTML = html;
                resultsDiv.classList.remove('hidden');
                dashboardContent.classList.add('hidden');
            })
            .catch(console.error);
        }, 300);
    });
}
