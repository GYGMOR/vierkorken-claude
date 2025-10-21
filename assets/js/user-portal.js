// assets/js/user-portal.js - FINAL VERSION

// Helper function to generate star icons
function generateStars(rating, max = 5) {
    const starIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';
    const emptyStarIcon = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>';

    let html = '<span style="color: #ffc107;">';
    for (let i = 0; i < rating; i++) {
        html += starIcon;
    }
    html += '</span>';
    html += '<span style="color: #ddd;">';
    for (let i = rating; i < max; i++) {
        html += emptyStarIcon;
    }
    html += '</span>';
    return html;
}

function showNotification(message, type = 'info') {
    const notif = document.createElement('div');
    notif.className = `notification notification-${type}`;
    notif.innerHTML = `<p>${message}</p>`;
    document.body.appendChild(notif);
    
    setTimeout(() => {
        notif.style.opacity = '0';
        setTimeout(() => notif.remove(), 300);
    }, 3000);
}

const API_BASE = 'api/user-portal.php';

// ============================================
// RATINGS
// ============================================
function loadUserRatings() {
    fetch(`${API_BASE}?action=get_ratings`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showNotification('Fehler: ' + data.error, 'error');
                return;
            }
            
            const container = document.getElementById('ratings-container');
            if (!container) return;
            
            if (!data.ratings || data.ratings.length === 0) {
                container.innerHTML = '<div class="empty-state"><p>Du hast noch keine Bewertungen abgegeben.</p><a href="?page=shop" class="btn btn-secondary">Zum Shop</a></div>';
                return;
            }
            
            container.innerHTML = data.ratings.map(r => `
                <div class="rating-item">
                    <div class="rating-header">
                        <div class="rating-info">
                            <h4><a href="?page=product&id=${r.wine_id}">${r.wine_name}</a></h4>
                            <p class="rating-producer">${r.producer} • ${r.category}</p>
                            <p class="rating-date">${r.date}</p>
                        </div>
                        <div class="rating-stars-display">
                            ${generateStars(r.rating)}
                        </div>
                    </div>
                    ${r.review ? `<p class="rating-text">"${r.review}"</p>` : ''}
                    <button onclick="deleteRating(${r.id})" class="btn-delete-rating">Löschen</button>
                </div>
            `).join('');
        })
        .catch(e => showNotification('Fehler beim Laden', 'error'));
}

function deleteRating(ratingId) {
    if (!confirm('Bewertung wirklich löschen?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_rating');
    formData.append('rating_id', ratingId);
    
    fetch(API_BASE, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showNotification('Bewertung gelöscht', 'success');
                loadUserRatings();
            } else {
                showNotification(d.error, 'error');
            }
        });
}

// ============================================
// FAVORITES
// ============================================
function loadUserFavorites() {
    fetch(`${API_BASE}?action=get_favorites`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showNotification('Fehler: ' + data.error, 'error');
                return;
            }
            
            const container = document.getElementById('favorites-container');
            if (!container) return;
            
            if (!data.favorites || data.favorites.length === 0) {
                container.innerHTML = '<div class="empty-state"><p>Noch keine Lieblingsweine gespeichert.</p><a href="?page=shop" class="btn btn-secondary">Zum Shop</a></div>';
                return;
            }
            
            container.innerHTML = data.favorites.map(w => `
                <div class="wine-card-favorite">
                    <div class="wine-image-container">
                        ${w.image_url ? `<img src="${w.image_url}" alt="${w.name}">` : '<div style="font-size: 3rem;"><svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="2"><path d="M8 2h8l1 9H7l1-9z"></path><path d="M7 11v11a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V11"></path></svg></div>'}
                    </div>
                    <div class="wine-info">
                        <h4><a href="?page=product&id=${w.id}">${w.name}</a></h4>
                        <p class="wine-producer">${w.producer || 'Schweizer Wein'}</p>
                        ${w.vintage ? `<p class="wine-meta">Jahrgang: ${w.vintage}</p>` : ''}
                        ${w.region ? `<p class="wine-meta">Region: ${w.region}</p>` : ''}
                        <p class="wine-price">CHF ${parseFloat(w.price).toFixed(2)}</p>
                        <div class="favorite-actions">
                            <a href="?page=product&id=${w.id}" class="btn btn-secondary btn-small">Anschauen</a>
                            <button onclick="removeFavorite(${w.id})" class="btn btn-delete btn-small">Entfernen</button>
                        </div>
                    </div>
                </div>
            `).join('');
        })
        .catch(e => showNotification('Fehler beim Laden', 'error'));
}

function removeFavorite(wineId) {
    const formData = new FormData();
    formData.append('action', 'remove_favorite');
    formData.append('wine_id', wineId);
    
    fetch(API_BASE, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showNotification('Aus Favoriten entfernt', 'success');
                loadUserFavorites();
            } else {
                showNotification(d.error, 'error');
            }
        });
}

// ============================================
// ORDERS
// ============================================
function loadUserOrders() {
    fetch(`${API_BASE}?action=get_orders`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showNotification('Fehler: ' + data.error, 'error');
                return;
            }
            
            const container = document.getElementById('orders-container');
            if (!container) return;
            
            if (!data.orders || data.orders.length === 0) {
                container.innerHTML = '<div class="empty-state"><p>Du hast noch keine Bestellungen.</p><a href="?page=shop" class="btn btn-secondary">Zum Shop</a></div>';
                return;
            }
            
            container.innerHTML = data.orders.map(o => `
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-info">
                            <h4>Bestellung #${o.number}</h4>
                            <p class="order-date">${o.date} ${o.time}</p>
                        </div>
                        <div class="order-status">
                            <span class="status-badge status-${o.status}">
                                ${o.status === 'pending' ? 'Ausstehend' : o.status === 'paid' ? 'Bezahlt' : o.status === 'shipped' ? 'Versendet' : o.status === 'delivered' ? 'Zugestellt' : 'Storniert'}
                            </span>
                        </div>
                    </div>
                    <div class="order-details">
                        <p><strong>Summe:</strong> CHF ${parseFloat(o.total).toFixed(2)}</p>
                        <p><strong>Artikel:</strong> ${o.items_count}</p>
                        ${o.city ? `<p><strong>Stadt:</strong> ${o.city}</p>` : ''}
                    </div>
                    <button onclick="viewOrderDetails(${o.id})" class="btn btn-secondary btn-small">Details anzeigen</button>
                </div>
            `).join('');
        })
        .catch(e => showNotification('Fehler beim Laden', 'error'));
}

function viewOrderDetails(orderId) {
    fetch(`${API_BASE}?action=get_order_details&order_id=${orderId}`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showNotification(data.error, 'error');
                return;
            }
            
            const o = data.order;
            const items = data.items;
            
            const deliveryMethodLabel = o.delivery_method === 'pickup' ? 'Abholung' : 'Lieferung';
            const addressLabel = o.delivery_method === 'pickup' ? 'Kontaktdaten' : 'Lieferadresse';
            const paymentMethodLabel = o.payment_method === 'card' ? 'Karte' : o.payment_method === 'twint' ? 'TWINT' : 'Barzahlung';

            const modal = createModal('Bestelldetails', `
                <div class="order-detail-modal">
                    <div class="detail-group">
                        <h4>Bestellstatus</h4>
                        <span class="status-badge status-${o.order_status}">${o.order_status === 'pending' ? 'Ausstehend' : o.order_status === 'processing' ? 'In Bearbeitung' : o.order_status === 'shipped' ? 'Versendet' : o.order_status === 'delivered' ? 'Zugestellt' : 'Storniert'}</span>
                    </div>

                    <div class="detail-group">
                        <h4>${addressLabel}</h4>
                        <p>${o.delivery_first_name} ${o.delivery_last_name}<br>${o.delivery_street}<br>${o.delivery_postal_code} ${o.delivery_city}</p>
                        <p><strong>Methode:</strong> ${deliveryMethodLabel}</p>
                        <p><strong>Telefon:</strong> ${o.delivery_phone}</p>
                        <p><strong>E-Mail:</strong> ${o.delivery_email}</p>
                    </div>

                    <div class="detail-group">
                        <h4>Zahlungsinformation</h4>
                        <p><strong>Methode:</strong> ${paymentMethodLabel}</p>
                        <p><strong>Status:</strong> ${o.payment_status === 'pending' ? 'Ausstehend' : o.payment_status === 'completed' ? 'Abgeschlossen' : o.payment_status === 'failed' ? 'Fehlgeschlagen' : 'Rückerstattet'}</p>
                        <p><strong>Gesamtbetrag:</strong> CHF ${parseFloat(o.total_amount).toFixed(2)}</p>
                    </div>
                    
                    <div class="detail-group">
                        <h4>Artikel</h4>
                        <table class="order-items-table">
                            <thead>
                                <tr>
                                    <th>Wein</th>
                                    <th>Kategorie</th>
                                    <th>Menge</th>
                                    <th>Preis</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${items.map(i => `
                                    <tr>
                                        <td><strong>${i.name}</strong><br><small>${i.producer || '-'}</small></td>
                                        <td>${i.category}</td>
                                        <td>${i.quantity}</td>
                                        <td>CHF ${parseFloat(i.price_at_purchase).toFixed(2)}</td>
                                        <td>CHF ${parseFloat(i.subtotal).toFixed(2)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `);
            document.body.appendChild(modal);
        })
        .catch(e => showNotification('Fehler', 'error'));
}

// ============================================
// ADDRESSES
// ============================================
function loadUserAddresses() {
    fetch(`${API_BASE}?action=get_addresses`)
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                showNotification('Fehler: ' + data.error, 'error');
                return;
            }
            
            const container = document.getElementById('addresses-container');
            if (!container) return;
            
            if (!data.addresses || data.addresses.length === 0) {
                container.innerHTML = '<div class="empty-state"><p>Noch keine Adressen gespeichert.</p></div>';
                return;
            }
            
            container.innerHTML = data.addresses.map(a => `
                <div class="address-card ${a.is_default ? 'is-default' : ''}">
                    ${a.is_default ? '<span class="badge-default">Standardadresse</span>' : ''}
                    <h4>${a.label}</h4>
                    <p>${a.first_name} ${a.last_name}<br>${a.street}<br>${a.postal_code} ${a.city}<br>${a.country}</p>
                    <div class="address-actions">
                        <button onclick="editAddress(${a.id})" class="btn btn-secondary btn-small">Bearbeiten</button>
                        <button onclick="deleteAddress(${a.id})" class="btn btn-delete btn-small">Löschen</button>
                    </div>
                </div>
            `).join('');
        })
        .catch(e => showNotification('Fehler beim Laden', 'error'));
}

function addAddressForm() {
    const modal = createModal('Neue Adresse hinzufügen', `
        <form id="form-new-address" class="form-address" onsubmit="event.preventDefault(); saveAddress(this, 'add')">
            <div class="form-row">
                <div class="form-group">
                    <label>Vorname *</label>
                    <input type="text" name="first_name" required>
                </div>
                <div class="form-group">
                    <label>Nachname</label>
                    <input type="text" name="last_name">
                </div>
            </div>
            
            <div class="form-group">
                <label>Straße & Hausnummer *</label>
                <input type="text" name="street" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Postleitzahl *</label>
                    <input type="text" name="postal_code" required>
                </div>
                <div class="form-group">
                    <label>Stadt *</label>
                    <input type="text" name="city" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Land</label>
                    <input type="text" name="country" value="Schweiz">
                </div>
                <div class="form-group">
                    <label>Label</label>
                    <input type="text" name="label" value="Hauptadresse">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_default"> Als Standardadresse festlegen
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary">Adresse hinzufügen</button>
        </form>
    `);
    document.body.appendChild(modal);
}

function editAddress(addressId) {
    fetch(`${API_BASE}?action=get_addresses`)
        .then(r => r.json())
        .then(data => {
            const addr = data.addresses.find(a => a.id === addressId);
            if (!addr) return;
            
            const modal = createModal('Adresse bearbeiten', `
                <form class="form-address" onsubmit="event.preventDefault(); saveAddress(this, 'update')">
                    <input type="hidden" name="address_id" value="${addressId}">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Vorname *</label>
                            <input type="text" name="first_name" value="${addr.first_name}" required>
                        </div>
                        <div class="form-group">
                            <label>Nachname</label>
                            <input type="text" name="last_name" value="${addr.last_name || ''}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Straße & Hausnummer *</label>
                        <input type="text" name="street" value="${addr.street}" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Postleitzahl *</label>
                            <input type="text" name="postal_code" value="${addr.postal_code}" required>
                        </div>
                        <div class="form-group">
                            <label>Stadt *</label>
                            <input type="text" name="city" value="${addr.city}" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Land</label>
                            <input type="text" name="country" value="${addr.country}">
                        </div>
                        <div class="form-group">
                            <label>Label</label>
                            <input type="text" name="label" value="${addr.label}">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_default" ${addr.is_default ? 'checked' : ''}> Als Standardadresse festlegen
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </form>
            `);
            document.body.appendChild(modal);
        });
}

function saveAddress(form, mode) {
    const formData = new FormData(form);
    formData.append('action', mode === 'add' ? 'add_address' : 'update_address');
    
    fetch(API_BASE, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showNotification(d.message, 'success');
                document.querySelectorAll('.modal-backdrop').forEach(m => m.remove());
                loadUserAddresses();
            } else {
                showNotification(d.error || 'Fehler', 'error');
            }
        });
}

function deleteAddress(addressId) {
    if (!confirm('Adresse wirklich löschen?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete_address');
    formData.append('address_id', addressId);
    
    fetch(API_BASE, { method: 'POST', body: formData })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                showNotification('Adresse gelöscht', 'success');
                loadUserAddresses();
            } else {
                showNotification(d.error, 'error');
            }
        });
}

// ============================================
// MODAL
// ============================================
function createModal(title, content) {
    const backdrop = document.createElement('div');
    backdrop.className = 'modal-backdrop';
    backdrop.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h3>${title}</h3>
                <button onclick="this.closest('.modal-backdrop').remove()" class="modal-close"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>
            </div>
            <div class="modal-body">${content}</div>
        </div>
    `;
    backdrop.addEventListener('click', function(e) {
        if (e.target === backdrop) backdrop.remove();
    });
    return backdrop;
}

// ============================================
// PAGE LOAD
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || 'overview';
    
    if (tab === 'ratings') loadUserRatings();
    if (tab === 'favorites') loadUserFavorites();
    if (tab === 'orders') loadUserOrders();
    if (tab === 'addresses') loadUserAddresses();
});