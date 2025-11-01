<?php
// pages/wishlist.php - Merkliste
?>

<div class="container" style="padding: 2rem 1rem; min-height: 60vh;">
    <h1>Meine Merkliste</h1>
    <p class="page-subtitle">Hier findest du alle Produkte, die du dir gemerkt hast.</p>

    <div id="wishlist-content">
        <p class="loading">Lade Merkliste...</p>
    </div>
</div>

<style>
.page-subtitle {
    color: #6b7280;
    margin-bottom: 2rem;
}

.wishlist-empty {
    text-align: center;
    padding: 3rem;
    background: #f9fafb;
    border-radius: 12px;
    margin-top: 2rem;
}

.wishlist-empty svg {
    color: #d1d5db;
    margin-bottom: 1rem;
}

.wishlist-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 2rem;
    margin-top: 2rem;
}

.wishlist-item {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.wishlist-item:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.wishlist-item h3 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
}

.wishlist-item-info {
    color: #6b7280;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.wishlist-item-actions {
    display: flex;
    gap: 0.5rem;
}

.loading {
    text-align: center;
    padding: 3rem;
    color: #999;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    renderWishlistPage();
});

function renderWishlistPage() {
    const container = document.getElementById('wishlist-content');
    const items = wishlist.getItems();

    if (items.length === 0) {
        container.innerHTML = `
            <div class="wishlist-empty">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <h2>Deine Merkliste ist leer</h2>
                <p>Füge Produkte zur Merkliste hinzu, um sie später wiederzufinden.</p>
                <a href="?page=shop" class="btn btn-primary" style="margin-top: 1rem;">Zum Shop</a>
            </div>
        `;
        return;
    }

    const html = `
        <div class="wishlist-grid">
            ${items.map(item => `
                <div class="wishlist-item">
                    <h3>${escapeHtml(item.name)}</h3>
                    <div class="wishlist-item-info">
                        Hinzugefügt: ${new Date(item.addedAt).toLocaleDateString('de-CH')}
                    </div>
                    <div class="wishlist-item-actions">
                        <a href="?page=product&id=${item.id}" class="btn btn-primary">Ansehen</a>
                        <button onclick="removeFromWishlist('${item.id}')" class="btn btn-secondary">Entfernen</button>
                    </div>
                </div>
            `).join('')}
        </div>
    `;

    container.innerHTML = html;
}

function removeFromWishlist(productId) {
    wishlist.removeItem(productId);
    renderWishlistPage();
    showNotification('Von Merkliste entfernt', 'info');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Listen to wishlist updates
window.addEventListener('wishlistUpdated', function() {
    renderWishlistPage();
});
</script>
