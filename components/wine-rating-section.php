<?php
// components/wine-rating-section.php
// Zeige Bewertungen & Rating-Formular auf Produktseiten

if (!isset($wine_id)) return;

$user_id = $_SESSION['user_id'] ?? 0;
$is_logged_in = isset($_SESSION['user_id']);

$wine = $db->query("SELECT * FROM wines WHERE id = $wine_id")->fetch_assoc();

$ratings = $db->query("
    SELECT r.*, u.first_name, u.last_name
    FROM wine_ratings r
    JOIN users u ON r.user_id = u.id
    WHERE r.wine_id = $wine_id
    ORDER BY r.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$user_rating = null;
if ($is_logged_in) {
    $check = $db->query("
        SELECT * FROM wine_ratings 
        WHERE wine_id = $wine_id AND user_id = $user_id
    ");
    if ($check->num_rows > 0) {
        $user_rating = $check->fetch_assoc();
    }
}

$avg_rating = $wine['avg_rating'] ?? 0;
$rating_count = $wine['rating_count'] ?? 0;
$rating_distribution = [];
for ($i = 1; $i <= 5; $i++) {
    $count = $db->query("SELECT COUNT(*) as c FROM wine_ratings WHERE wine_id = $wine_id AND rating = $i")->fetch_assoc()['c'];
    $rating_distribution[$i] = $count;
}
?>

<div class="wine-rating-section">
    <!-- RATING OVERVIEW -->
    <div class="rating-overview">
        <div class="rating-summary">
            <div class="rating-big">
                <div class="rating-stars-big">
                    <span style="color: #ffc107;">
                        <?php 
                        $full_stars = floor($avg_rating);
                        $half_star = ($avg_rating - $full_stars) >= 0.5 ? 1 : 0;
                        echo str_repeat('★', $full_stars);
                        if ($half_star) echo '½';
                        echo str_repeat('☆', 5 - $full_stars - $half_star);
                        ?>
                    </span>
                </div>
                <p class="rating-number"><?php echo number_format($avg_rating, 1); ?> / 5</p>
                <p class="rating-count"><?php echo $rating_count; ?> Bewertungen</p>
            </div>

            <!-- RATING DISTRIBUTION -->
            <div class="rating-distribution">
                <?php for ($i = 5; $i >= 1; $i--): ?>
                    <div class="rating-bar">
                        <span class="bar-label"><?php echo str_repeat('★', $i); ?></span>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: <?php echo $rating_count > 0 ? ($rating_distribution[$i] / $rating_count * 100) : 0; ?>%"></div>
                        </div>
                        <span class="bar-count"><?php echo $rating_distribution[$i]; ?></span>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- RATING FORM -->
        <div class="rating-form-box">
            <?php if ($is_logged_in): ?>
                <h3>Deine Bewertung</h3>
                
                <?php if ($user_rating): ?>
                    <p style="color: var(--text-light); margin-bottom: 1rem;">Du hast diesen Wein bereits bewertet. Du kannst deine Bewertung aktualisieren:</p>
                <?php endif; ?>
                
                <form id="rating-form-<?php echo $wine_id; ?>" class="rating-form-inline">
                    <input type="hidden" name="wine_id" value="<?php echo $wine_id; ?>">

                    <div class="rating-stars-selector">
                        <div class="stars-container">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <label class="star-label">
                                    <input type="radio" name="rating" value="<?php echo $i; ?>" 
                                           <?php echo ($user_rating && $user_rating['rating'] == $i) ? 'checked' : ''; ?>>
                                    <span class="star-display" data-value="<?php echo $i; ?>">★</span>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <span class="rating-text">Wähle eine Bewertung</span>
                    </div>

                    <textarea name="review" placeholder="Schreib optional einen Kommentar..." rows="3"><?php echo $user_rating ? safe_output($user_rating['review_text']) : ''; ?></textarea>

                    <button type="submit" class="btn btn-primary">Bewertung speichern</button>
                    
                    <?php if ($user_rating): ?>
                        <button type="button" class="btn btn-secondary" onclick="deleteUserRating(<?php echo $wine_id; ?>)">Bewertung löschen</button>
                    <?php endif; ?>
                </form>
            <?php else: ?>
                <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; text-align: center;">
                    <p style="margin: 0 0 1rem;">Melde dich an um diesen Wein zu bewerten</p>
                    <a href="?modal=login" class="btn btn-primary">Anmelden</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- REVIEWS LIST -->
    <div class="reviews-list">
        <h3>Bewertungen von Nutzern</h3>

        <?php if (count($ratings) > 0): ?>
            <?php foreach ($ratings as $r): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <p class="review-user"><?php echo safe_output($r['first_name'] . ' ' . $r['last_name']); ?></p>
                            <p class="review-date"><?php echo date('d. M Y', strtotime($r['created_at'])); ?></p>
                        </div>
                        <div class="review-rating">
                            <span style="color: #ffc107; font-size: 1.2rem;">
                                <?php echo str_repeat('★', $r['rating']) . str_repeat('☆', 5 - $r['rating']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($r['review_text']): ?>
                        <p class="review-text"><?php echo safe_output($r['review_text']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: var(--text-light); text-align: center; padding: 2rem;">Noch keine Bewertungen. Sei der Erste!</p>
        <?php endif; ?>
    </div>
</div>

<style>
.wine-rating-section {
    background: white;
    border-radius: 10px;
    margin: 2rem 0;
    border-top: 3px solid var(--primary-color);
}

.rating-overview {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    padding: 2rem;
    border-bottom: 1px solid #f0f0f0;
}

.rating-summary {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 2rem;
    align-items: start;
}

.rating-big {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    border-radius: 10px;
}

.rating-stars-big {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
}

.rating-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary-color);
    margin: 0.5rem 0;
}

.rating-count {
    color: var(--text-light);
    font-size: 0.9rem;
    margin: 0;
}

.rating-distribution {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.rating-bar {
    display: grid;
    grid-template-columns: 30px 1fr 30px;
    gap: 1rem;
    align-items: center;
}

.bar-label {
    color: #ffc107;
    font-weight: 600;
    font-size: 0.9rem;
}

.bar-container {
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
}

.bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #ffc107, #ffb700);
    transition: width 0.3s ease;
}

.bar-count {
    text-align: right;
    color: var(--text-light);
    font-size: 0.9rem;
    min-width: 30px;
}

.rating-form-box {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 10px;
}

.rating-form-box h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.rating-form-inline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.stars-container {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.star-label {
    cursor: pointer;
    font-size: 2rem;
    color: #ddd;
    transition: all 0.2s;
    line-height: 1;
}

.star-label input {
    display: none;
}

.star-label:hover .star-display,
.star-label input:checked ~ .star-display {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-text {
    display: block;
    color: var(--text-light);
    font-size: 0.9rem;
    margin-top: 0.5rem;
}

.rating-form-inline textarea {
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-family: inherit;
    resize: vertical;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.rating-form-inline textarea:focus {
    outline: none;
    border-color: var(--primary-color);
}

.reviews-list {
    padding: 2rem;
}

.reviews-list h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.review-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1rem;
    border-left: 4px solid var(--accent-gold);
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.review-user {
    font-weight: 600;
    color: var(--primary-color);
    margin: 0;
}

.review-date {
    color: var(--text-light);
    font-size: 0.85rem;
    margin: 0.3rem 0 0;
}

.review-rating {
    text-align: right;
}

.review-text {
    color: var(--text-dark);
    line-height: 1.6;
    margin: 1rem 0 0;
}

@media (max-width: 768px) {
    .rating-overview {
        grid-template-columns: 1fr;
    }
    
    .rating-summary {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Rating Stars Selector - Update Styling
document.querySelectorAll('.stars-container input[type="radio"]').forEach(star => {
    star.addEventListener('change', function() {
        const value = this.value;
        const container = this.closest('.stars-container');
        const textSpan = container.nextElementSibling;
        
        container.querySelectorAll('.star-display').forEach((s, i) => {
            if (i < value) {
                s.style.color = '#ffc107';
            } else {
                s.style.color = '#ddd';
            }
        });
        
        const labels = ['Schrecklich', 'Nicht gut', 'Okay', 'Gut', 'Großartig'];
        textSpan.textContent = labels[value - 1];
    });
});

// Submit Rating - KORRIGIERT
document.getElementById('rating-form-<?php echo $wine_id; ?>')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const rating = this.querySelector('input[name="rating"]:checked')?.value;
    const review = this.querySelector('textarea[name="review"]').value;
    
    if (!rating) {
        alert('Bitte wähle eine Bewertung aus');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'rate_wine');
    formData.append('wine_id', <?php echo $wine_id; ?>);
    formData.append('rating', rating);
    formData.append('review', review);
    
    fetch('api/user-portal.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            alert('Bewertung gespeichert!');
            location.reload();
        } else {
            alert('Fehler: ' + (d.error || 'Unbekannter Fehler'));
            console.error('Response:', d);
        }
    })
    .catch(e => {
        console.error('Fehler:', e);
        alert('Fehler beim Speichern');
    });
});

function deleteUserRating(wineId) {
    if (confirm('Bewertung wirklich löschen?')) {
        const formData = new FormData();
        formData.append('action', 'delete_rating');
        formData.append('rating_id', wineId);
        
        fetch('api/user-portal.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                alert('Bewertung gelöscht');
                location.reload();
            } else {
                alert('Fehler: ' + d.error);
            }
        });
    }
}
</script>