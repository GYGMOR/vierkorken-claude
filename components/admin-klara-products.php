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
                        <label for="klara-product-kanton">Kanton</label>
                        <select id="klara-product-kanton" name="kanton" class="form-control">
                            <option value="">-- Kein Kanton --</option>
                            <?php
                            require_once __DIR__ . '/../includes/kantone.php';
                            $kantone = get_schweizer_kantone();
                            foreach ($kantone as $code => $name) {
                                echo "<option value=\"$code\">$name</option>";
                            }
                            ?>
                        </select>
                        <small class="form-hint">Wappen wird auf Produktseite angezeigt</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="klara-product-alcohol">Alkoholgehalt (%)</label>
                        <input type="number" id="klara-product-alcohol" name="alcohol_content" class="form-control" step="0.1" min="0" max="100">
                    </div>
                    <div class="form-group">
                        <!-- Leer lassen für Layout -->
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
                        <input type="color" id="klara-product-bg-color" name="featured_bg_color" value="#722c2c" style="width: 100%; height: 50px; border: 2px solid #ccc; border-radius: 4px; cursor: pointer;">
                        <small class="form-hint">Füllfarbe der Neuheiten-Karte</small>
                    </div>
                    <div class="form-group">
                        <label for="klara-product-text-color">Textfarbe</label>
                        <input type="color" id="klara-product-text-color" name="featured_text_color" value="#ffffff" style="width: 100%; height: 50px; border: 2px solid #ccc; border-radius: 4px; cursor: pointer;">
                        <small class="form-hint">Farbe des Texts auf der Karte</small>
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
    flex-shrink: 0; /* Verhindert dass Bild kleiner wird */
}

/* Icon-Placeholder für Produkte ohne Bild */
.wine-image-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    width: 80px;
    height: 80px;
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

function loadKlaraProducts() {
    console.log('loadKlaraProducts() called');
    fetch('api/klara-products-extended.php?action=get_all')
        .then(r => {
            console.log('API Response status:', r.status);
            return r.json();
        })
        .then(data => {
            console.log('API Response data:', data);
            if (data.success) {
                klaraProductsData = data.data;
                console.log('Loaded products count:', klaraProductsData.length);
                renderKlaraProducts(klaraProductsData);
            } else {
                console.error('API returned error:', data.error);
                document.getElementById('klara-products-list').innerHTML = '<p>Fehler beim Laden: ' + data.error + '</p>';
            }
        })
        .catch(e => {
            console.error('Fetch error:', e);
            document.getElementById('klara-products-list').innerHTML = '<p>Fehler beim Laden der Produkte. Siehe Konsole für Details.</p>';
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
    console.log('renderKlaraProducts() called with', products.length, 'products');
    const container = document.getElementById('klara-products-list');

    if (!container) {
        console.error('Container #klara-products-list not found!');
        return;
    }

    if (products.length === 0) {
        console.warn('No products to render');
        container.innerHTML = '<p>Keine Produkte gefunden.</p>';
        return;
    }

    console.log('First product:', products[0]);

    const html = products.map(product => {
        const hasExtended = product.image_url || product.producer || product.description;
        const price = parseFloat(product.custom_price || product.price || 0);

        return `
            <div class="klara-product-card">
                ${product.image_url ?
                    `<img src="${product.image_url}" alt="${escapeHtml(product.name)}" class="klara-product-image" onerror="this.style.display='none'">` :
                    `<div class="klara-product-image wine-image-placeholder" style="display:flex;align-items:center;justify-content:center;background:#f5f5f5;">${'🍷'}</div>`
                }

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

    console.log('Opening modal with product:', product);
    console.log('is_featured:', product.is_featured);
    console.log('vintage:', product.vintage);
    console.log('featured_bg_color:', product.featured_bg_color);

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
    document.getElementById('klara-product-kanton').value = product.kanton || '';
    document.getElementById('klara-product-alcohol').value = product.alcohol_content || '';
    document.getElementById('klara-product-short-desc').value = product.short_description || product.description || '';
    document.getElementById('klara-product-extended-desc').value = product.extended_description || '';
    document.getElementById('klara-product-custom-price').value = product.custom_price || '';
    document.getElementById('klara-product-featured').checked = product.is_featured == 1;

    // Farben setzen
    document.getElementById('klara-product-bg-color').value = product.featured_bg_color || '#722c2c';
    document.getElementById('klara-product-text-color').value = product.featured_text_color || '#ffffff';

    modal.style.display = 'flex';
}

function closeKlaraProductModal() {
    document.getElementById('klara-product-modal').style.display = 'none';
}

function saveKlaraProduct(event) {
    event.preventDefault();
    console.log('saveKlaraProduct() called');

    const form = document.getElementById('klara-product-form');
    const data = {};

    // Klara Article ID (erforderlich)
    data.klara_article_id = document.getElementById('klara-product-id')?.value;
    console.log('klara_article_id:', data.klara_article_id);

    // Bild-URL - nur wenn ausgefüllt
    const imageUrl = document.getElementById('klara-product-image')?.value?.trim();
    if (imageUrl) {
        data.image_url = imageUrl;
    }

    // Producer - nur wenn ausgefüllt
    const producer = document.getElementById('klara-product-producer')?.value?.trim();
    if (producer) {
        data.producer = producer;
    }

    // Vintage - nur wenn ausgefüllt, als Zahl
    const vintage = document.getElementById('klara-product-vintage')?.value?.trim();
    if (vintage) {
        data.vintage = parseInt(vintage);
    }

    // Region - nur wenn ausgefüllt
    const region = document.getElementById('klara-product-region')?.value?.trim();
    if (region) {
        data.region = region;
    }

    // Kanton - nur wenn ausgefüllt
    const kanton = document.getElementById('klara-product-kanton')?.value?.trim();
    if (kanton) {
        data.kanton = kanton;
    }

    // Alkoholgehalt - nur wenn ausgefüllt, als Zahl
    const alcohol = document.getElementById('klara-product-alcohol')?.value?.trim();
    if (alcohol) {
        data.alcohol_content = parseFloat(alcohol);
    }

    // Kurzbeschreibung - nur wenn ausgefüllt
    const shortDesc = document.getElementById('klara-product-short-desc')?.value?.trim();
    if (shortDesc) {
        data.short_description = shortDesc;
    }

    // Erweiterte Beschreibung - nur wenn ausgefüllt
    const extDesc = document.getElementById('klara-product-extended-desc')?.value?.trim();
    if (extDesc) {
        data.extended_description = extDesc;
    }

    // Custom Price - nur wenn ausgefüllt, als Zahl
    const customPrice = document.getElementById('klara-product-custom-price')?.value?.trim();
    if (customPrice) {
        data.custom_price = parseFloat(customPrice);
    }

    // Featured - immer senden (0 oder 1)
    data.is_featured = document.getElementById('klara-product-featured')?.checked ? 1 : 0;

    // Farben - immer senden (mit Standardwerten falls leer)
    data.featured_bg_color = document.getElementById('klara-product-bg-color')?.value || '#722c2c';
    data.featured_text_color = document.getElementById('klara-product-text-color')?.value || '#ffffff';

    console.log('Sending data to API:', data);

    fetch('api/klara-products-extended.php?action=update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(r => {
        console.log('API Response status:', r.status);
        return r.json();
    })
    .then(result => {
        console.log('API Response:', result);
        if (result.success) {
            alert('Produkt aktualisiert!');
            closeKlaraProductModal();
            loadKlaraProducts();
        } else {
            alert('Fehler: ' + result.error);
            console.error('API error:', result.error, 'DB error:', result.db_error);
        }
    })
    .catch(e => {
        console.error('Fetch error:', e);
        alert('Fehler beim Speichern');
    });
}
</script>
