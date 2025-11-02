<?php
// components/admin-klara-products.php - Admin UI für Klara-Produkte
// Wird in admin-dashboard.php eingebunden
?>

<div class="wines-header">
    <h3>Klara-Produkte verwalten</h3>
    <p class="admin-info-text">Produkte aus Klara mit zusätzlichen Informationen erweitern</p>
</div>

<div class="klara-products-filter">
    <input type="text" id="klara-product-search" placeholder="Produkt suchen..." class="form-control" style="max-width: 400px;">
    <select id="klara-category-filter" class="form-control" style="max-width: 250px;">
        <option value="">Alle Kategorien</option>
    </select>
</div>

<div id="klara-products-list">
    <p>Lade Klara-Produkte...</p>
</div>

<!-- Klara Product Edit Modal -->
<div id="klara-product-modal" class="modal-overlay" style="display: none;">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="klara-product-modal-title">Produkt bearbeiten</h3>
            <button onclick="closeKlaraProductModal()" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="klara-product-form" onsubmit="saveKlaraProduct(event)">
                <input type="hidden" id="klara-product-id" name="klara_article_id">

                <div class="form-section-divider">
                    <h4>Basis-Information (von Klara)</h4>
                    <p class="form-hint">Diese Daten kommen aus Klara und werden automatisch aktualisiert</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Artikelname</label>
                        <input type="text" id="klara-product-name" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>Artikelnummer</label>
                        <input type="text" id="klara-product-number" class="form-control" disabled>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Klara-Preis</label>
                        <input type="text" id="klara-product-price-display" class="form-control" disabled>
                    </div>
                    <div class="form-group">
                        <label>Kategorie (Klara)</label>
                        <input type="text" id="klara-product-categories" class="form-control" disabled>
                    </div>
                </div>

                <div class="form-section-divider">
                    <h4>Erweiterte Informationen</h4>
                    <p class="form-hint">Diese Informationen kannst du frei bearbeiten</p>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="klara-product-image">Bild-URL</label>
                        <input type="text" id="klara-product-image" name="image_url" class="form-control" placeholder="z.B. assets/images/wines/wein.jpg">
                        <small class="form-hint">URL zum Produkt-Bild</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="klara-product-producer">Produzent</label>
                        <input type="text" id="klara-product-producer" name="producer" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="klara-product-vintage">Jahrgang</label>
                        <input type="number" id="klara-product-vintage" name="vintage" class="form-control" min="1900" max="2100">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="klara-product-region">Region</label>
                        <input type="text" id="klara-product-region" name="region" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="klara-product-alcohol">Alkoholgehalt (%)</label>
                        <input type="number" id="klara-product-alcohol" name="alcohol_content" class="form-control" step="0.1" min="0" max="100">
                    </div>
                </div>

                <div class="form-section-divider">
                    <h4>Beschreibungen</h4>
                    <p class="form-hint">Kurzbeschreibung für alle Kunden, erweiterte Beschreibung für Weinkenner</p>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="klara-product-short-desc">Kurzbeschreibung</label>
                        <textarea id="klara-product-short-desc" name="short_description" rows="3" class="form-control" placeholder="Kurze Beschreibung für alle Kunden (2-3 Sätze)..."></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group full-width">
                        <label for="klara-product-extended-desc">Erweiterte Beschreibung (optional)</label>
                        <textarea id="klara-product-extended-desc" name="extended_description" rows="5" class="form-control" placeholder="Detaillierte Beschreibung für Weinkenner (Aromen, Rebsorten, Herstellung, etc.)..."></textarea>
                    </div>
                </div>

                <div class="form-section-divider">
                    <h4>Preis & Features</h4>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="klara-product-custom-price">Eigener Preis (optional)</label>
                        <input type="number" id="klara-product-custom-price" name="custom_price" class="form-control" step="0.01" min="0" placeholder="Leer = Klara-Preis verwenden">
                        <small class="form-hint">Überschreibt den Klara-Preis falls gesetzt</small>
                    </div>
                    <div class="form-group">
                        <label for="klara-product-featured">
                            <input type="checkbox" id="klara-product-featured" name="is_featured" value="1">
                            Als Neuheit markieren
                        </label>
                        <small class="form-hint">Erscheint auf der Startseite bei "Neuheiten"</small>
                    </div>
                </div>

                <div class="form-section-divider">
                    <h4>Neuheiten-Farben</h4>
                    <p class="form-hint">Farben für die Anzeige auf der Startseite (nur wenn als Neuheit markiert)</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="klara-product-bg-color">Hintergrundfarbe</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="klara-product-bg-color" name="featured_bg_color" value="#722c2c" onchange="updateColorPreview()" oninput="updateColorPreview()" style="width: 80px; height: 40px; border: 2px solid #ccc; border-radius: 4px; cursor: pointer;">
                            <input type="text" id="klara-product-bg-color-text" readonly value="#722c2c" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #f5f5f5; font-family: monospace;">
                        </div>
                        <small class="form-hint">Füllfarbe der Neuheiten-Karte</small>
                    </div>
                    <div class="form-group">
                        <label for="klara-product-text-color">Textfarbe</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="color" id="klara-product-text-color" name="featured_text_color" value="#ffffff" onchange="updateColorPreview()" oninput="updateColorPreview()" style="width: 80px; height: 40px; border: 2px solid #ccc; border-radius: 4px; cursor: pointer;">
                            <input type="text" id="klara-product-text-color-text" readonly value="#ffffff" style="flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 4px; background: #f5f5f5; font-family: monospace;">
                        </div>
                        <small class="form-hint">Farbe des Texts auf der Karte</small>
                    </div>
                </div>

                <!-- Farb-Vorschau -->
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Vorschau:</label>
                    <div id="klara-color-preview" style="padding: 20px; border-radius: 8px; text-align: center; font-weight: bold; background: #722c2c; color: #ffffff;">
                        Beispiel: Neuheit auf der Startseite
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" onclick="closeKlaraProductModal()" class="btn btn-secondary">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.klara-products-filter {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.klara-product-card {
    display: grid;
    grid-template-columns: 80px 1fr auto;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    margin-bottom: 1rem;
    transition: all 0.3s ease;
}

.klara-product-card:hover {
    border-color: var(--primary-color);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.klara-product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    background: #f3f4f6;
}

.klara-product-info h4 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
}

.klara-product-meta {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: 0.85rem;
    color: #6b7280;
}

.klara-product-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
}

.featured-badge {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.extended-badge {
    background: #3b82f6;
    color: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}
</style>

<script>
// Klara Products Management
let klaraProductsData = [];
let klaraCategoriesData = [];

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('klara-products-list')) {
        loadKlaraProducts();
        loadKlaraCategories();

        // Search filter
        document.getElementById('klara-product-search')?.addEventListener('input', filterKlaraProducts);
        document.getElementById('klara-category-filter')?.addEventListener('change', filterKlaraProducts);
    }
});

function updateColorPreview() {
    const preview = document.getElementById('klara-color-preview');
    const bgColor = document.getElementById('klara-product-bg-color')?.value || '#722c2c';
    const textColor = document.getElementById('klara-product-text-color')?.value || '#ffffff';
    const bgText = document.getElementById('klara-product-bg-color-text');
    const textText = document.getElementById('klara-product-text-color-text');

    if (preview) {
        preview.style.backgroundColor = bgColor;
        preview.style.color = textColor;
    }
    if (bgText) bgText.value = bgColor;
    if (textText) textText.value = textColor;
}

function loadKlaraProducts() {
    fetch('api/klara-products-extended.php?action=get_all')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                klaraProductsData = data.data;
                renderKlaraProducts(klaraProductsData);
            } else {
                document.getElementById('klara-products-list').innerHTML = '<p>Fehler beim Laden: ' + data.error + '</p>';
            }
        })
        .catch(e => {
            console.error('Error:', e);
            document.getElementById('klara-products-list').innerHTML = '<p>Fehler beim Laden</p>';
        });
}

function loadKlaraCategories() {
    // Kategorien aus bereits geladenen Produkten extrahieren
    const categories = new Set();
    klaraProductsData.forEach(product => {
        if (product.categories && Array.isArray(product.categories)) {
            product.categories.forEach(cat => categories.add(cat));
        }
    });

    // TODO: Kategorie-Namen von Klara API holen
    const select = document.getElementById('klara-category-filter');
    if (select) {
        categories.forEach(catId => {
            const option = document.createElement('option');
            option.value = catId;
            option.textContent = `Kategorie ${catId}`;
            select.appendChild(option);
        });
    }
}

function filterKlaraProducts() {
    const searchTerm = document.getElementById('klara-product-search')?.value.toLowerCase() || '';
    const categoryFilter = document.getElementById('klara-category-filter')?.value || '';

    const filtered = klaraProductsData.filter(product => {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
                             (product.articleNumber && product.articleNumber.toLowerCase().includes(searchTerm));

        const matchesCategory = !categoryFilter ||
                                (product.categories && product.categories.includes(categoryFilter));

        return matchesSearch && matchesCategory;
    });

    renderKlaraProducts(filtered);
}

function renderKlaraProducts(products) {
    const container = document.getElementById('klara-products-list');

    if (products.length === 0) {
        container.innerHTML = '<p>Keine Produkte gefunden.</p>';
        return;
    }

    const html = products.map(product => {
        const hasExtended = product.image_url || product.producer || product.description;
        const price = product.custom_price || product.price || 0;

        return `
            <div class="klara-product-card">
                <img src="${product.image_url || 'assets/images/placeholder.jpg'}" alt="${escapeHtml(product.name)}" class="klara-product-image" onerror="this.src='assets/images/placeholder.jpg'">

                <div class="klara-product-info">
                    <h4>${escapeHtml(product.name)}</h4>
                    <div class="klara-product-meta">
                        <span>Nr: ${product.articleNumber || '-'}</span>
                        <span>CHF ${price.toFixed(2)}</span>
                        ${product.producer ? `<span>| ${escapeHtml(product.producer)}</span>` : ''}
                        ${product.is_featured ? '<span class="featured-badge">Neuheit</span>' : ''}
                        ${hasExtended ? '<span class="extended-badge">Erweitert</span>' : ''}
                    </div>
                </div>

                <div class="klara-product-actions">
                    <button onclick="editKlaraProduct('${product.id}')" class="btn-small-admin">Bearbeiten</button>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = html;
}

function editKlaraProduct(id) {
    fetch(`api/klara-products-extended.php?action=get_one&id=${id}`)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                openKlaraProductModal(data.data);
            } else {
                alert('Fehler: ' + data.error);
            }
        })
        .catch(e => {
            console.error('Error:', e);
            alert('Fehler beim Laden');
        });
}

function openKlaraProductModal(product) {
    const modal = document.getElementById('klara-product-modal');

    // Basis-Daten (read-only)
    document.getElementById('klara-product-id').value = product.id;
    document.getElementById('klara-product-name').value = product.name || '';
    document.getElementById('klara-product-number').value = product.articleNumber || '';
    document.getElementById('klara-product-price-display').value = 'CHF ' + (product.price || 0).toFixed(2);
    document.getElementById('klara-product-categories').value = (product.categories || []).join(', ');

    // Erweiterte Daten (editable)
    document.getElementById('klara-product-image').value = product.image_url || '';
    document.getElementById('klara-product-producer').value = product.producer || '';
    document.getElementById('klara-product-vintage').value = product.vintage || '';
    document.getElementById('klara-product-region').value = product.region || '';
    document.getElementById('klara-product-alcohol').value = product.alcohol_content || '';
    document.getElementById('klara-product-short-desc').value = product.short_description || product.description || '';
    document.getElementById('klara-product-extended-desc').value = product.extended_description || '';
    document.getElementById('klara-product-custom-price').value = product.custom_price || '';
    document.getElementById('klara-product-featured').checked = product.is_featured == 1;

    // Farben setzen
    const bgColor = product.featured_bg_color || '#722c2c';
    const textColor = product.featured_text_color || '#ffffff';

    // Warte kurz, dann setze die Werte (damit die Felder existieren)
    setTimeout(function() {
        const bgPicker = document.getElementById('klara-product-bg-color');
        const textPicker = document.getElementById('klara-product-text-color');
        const bgText = document.getElementById('klara-product-bg-color-text');
        const textText = document.getElementById('klara-product-text-color-text');

        if (bgPicker) bgPicker.value = bgColor;
        if (textPicker) textPicker.value = textColor;
        if (bgText) bgText.value = bgColor;
        if (textText) textText.value = textColor;

        updateColorPreview();
    }, 100);

    modal.style.display = 'flex';
}

function closeKlaraProductModal() {
    document.getElementById('klara-product-modal').style.display = 'none';
}

function saveKlaraProduct(event) {
    event.preventDefault();

    const form = document.getElementById('klara-product-form');
    const formData = new FormData(form);
    const data = {};

    formData.forEach((value, key) => {
        if (key === 'is_featured') {
            data[key] = 1;
        } else {
            data[key] = value;
        }
    });

    if (!document.getElementById('klara-product-featured').checked) {
        data.is_featured = 0;
    }

    fetch('api/klara-products-extended.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => r.json())
    .then(result => {
        if (result.success) {
            alert('Produkt aktualisiert!');
            closeKlaraProductModal();
            loadKlaraProducts();
        } else {
            alert('Fehler: ' + result.error);
        }
    })
    .catch(e => {
        console.error('Error:', e);
        alert('Fehler beim Speichern');
    });
}
</script>
