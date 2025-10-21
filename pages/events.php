<?php
// pages/events.php - Events-Übersicht und Buchung
$events = get_all_events(true, true); // Nur aktive und zukünftige Events
?>

<div class="events-page">
    <div class="container">
        <div class="page-header">
            <h1>Unsere Events</h1>
            <p class="page-subtitle">Erleben Sie unvergessliche Weinmomente bei unseren exklusiven Veranstaltungen</p>
        </div>

        <?php if (empty($events)): ?>
            <div class="no-events">
                <div class="no-events-icon"><?php echo get_icon('calendar', 80, 'icon-secondary'); ?></div>
                <h2>Keine bevorstehenden Events</h2>
                <p>Derzeit sind keine Events geplant. Schauen Sie bald wieder vorbei!</p>
            </div>
        <?php else: ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <?php if (!empty($event['image_url'])): ?>
                                <img src="<?php echo safe_output($event['image_url']); ?>" alt="<?php echo safe_output($event['name']); ?>">
                            <?php else: ?>
                                <div class="event-image-placeholder">
                                    <?php echo get_icon('calendar', 80, 'icon-secondary'); ?>
                                </div>
                            <?php endif; ?>

                            <div class="event-date-badge">
                                <div class="date-day"><?php echo date('d', strtotime($event['event_date'])); ?></div>
                                <div class="date-month"><?php echo date('M', strtotime($event['event_date'])); ?></div>
                            </div>
                        </div>

                        <div class="event-content">
                            <h2><?php echo safe_output($event['name']); ?></h2>

                            <div class="event-meta">
                                <div class="event-meta-item">
                                    <?php echo get_icon('calendar', 18, 'icon-primary'); ?>
                                    <span><?php echo date('d.m.Y, H:i', strtotime($event['event_date'])); ?> Uhr</span>
                                </div>

                                <?php if (!empty($event['location'])): ?>
                                    <div class="event-meta-item">
                                        <?php echo get_icon('map-pin', 18, 'icon-primary'); ?>
                                        <span><?php echo safe_output($event['location']); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="event-meta-item">
                                    <?php echo get_icon('users', 18, 'icon-primary'); ?>
                                    <span><?php echo $event['available_tickets']; ?> Tickets verfügbar</span>
                                </div>
                            </div>

                            <?php if (!empty($event['description'])): ?>
                                <p class="event-description"><?php echo nl2br(safe_output($event['description'])); ?></p>
                            <?php endif; ?>

                            <div class="event-footer">
                                <div class="event-price">
                                    <span class="price-label">Preis:</span>
                                    <span class="price-value"><?php echo format_price($event['price']); ?></span>
                                </div>

                                <?php if ($event['available_tickets'] > 0): ?>
                                    <button
                                        onclick="bookEvent(<?php echo $event['id']; ?>, '<?php echo safe_output(addslashes($event['name'])); ?>', <?php echo $event['price']; ?>)"
                                        class="btn btn-primary">
                                        Jetzt buchen
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-secondary" disabled>Ausgebucht</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Booking Modal -->
<div id="booking-modal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeBookingModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Event buchen</h2>
            <button class="modal-close" onclick="closeBookingModal()">&times;</button>
        </div>

        <div class="modal-body">
            <h3 id="booking-event-name"></h3>

            <div class="booking-form">
                <div class="form-group">
                    <label>Anzahl Tickets *</label>
                    <input type="number" id="booking-quantity" min="1" value="1" onchange="updateBookingTotal()">
                </div>

                <div class="form-group">
                    <label>Ihr Name *</label>
                    <input type="text" id="booking-name" required>
                </div>

                <div class="form-group">
                    <label>E-Mail *</label>
                    <input type="email" id="booking-email" required>
                </div>

                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" id="booking-phone">
                </div>

                <div class="booking-total">
                    <strong>Gesamtpreis:</strong>
                    <span id="booking-total-price">0.00 CHF</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeBookingModal()">Abbrechen</button>
            <button class="btn btn-primary" onclick="confirmBooking()">Buchen & in Warenkorb</button>
        </div>
    </div>
</div>

<style>
.events-page {
    padding: 2rem 0;
    min-height: 60vh;
}

.page-header {
    text-align: center;
    margin-bottom: 3rem;
}

.page-header h1 {
    color: var(--primary-color);
    border-bottom: 3px solid var(--accent-gold);
    padding-bottom: 1rem;
    display: inline-block;
}

.page-subtitle {
    color: var(--text-light);
    font-size: 1.1rem;
    margin-top: 1rem;
}

.no-events {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-light);
    border-radius: 12px;
}

.no-events-icon {
    margin-bottom: 1rem;
    opacity: 0.5;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 2rem;
}

.event-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.event-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 24px rgba(114, 44, 44, 0.2);
}

.event-image {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
}

.event-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.event-image-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--bg-light) 0%, #f5f5f5 100%);
    display: flex;
    align-items: center;
    justify-content: center;
}

.event-date-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
    background: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.date-day {
    font-size: 1.8rem;
    font-weight: 700;
    line-height: 1;
}

.date-month {
    font-size: 0.9rem;
    text-transform: uppercase;
    margin-top: 0.2rem;
}

.event-content {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.event-content h2 {
    margin: 0 0 1rem 0;
    color: var(--primary-color);
    font-size: 1.5rem;
    border: none;
}

.event-meta {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
    margin-bottom: 1rem;
}

.event-meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-light);
    font-size: 0.95rem;
}

.event-description {
    color: var(--text-light);
    line-height: 1.6;
    margin-bottom: 1.5rem;
    flex: 1;
}

.event-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 2px solid var(--bg-light);
}

.event-price {
    display: flex;
    flex-direction: column;
}

.price-label {
    font-size: 0.85rem;
    color: var(--text-light);
}

.price-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
}

.modal-content {
    position: relative;
    background: white;
    border-radius: 12px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 2px solid var(--bg-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    color: var(--primary-color);
    border: none;
}

.modal-close {
    background: none;
    border: none;
    font-size: 2rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 0;
    width: 2rem;
    height: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-close:hover {
    color: var(--primary-color);
}

.modal-body {
    padding: 1.5rem;
}

.modal-body h3 {
    margin-top: 0;
    color: var(--primary-color);
}

.booking-form {
    margin-top: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: var(--primary-color);
}

.form-group input {
    width: 100%;
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.booking-total {
    padding: 1rem;
    background: var(--bg-light);
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 1.2rem;
    margin-top: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 2px solid var(--bg-light);
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
}

/* Responsive */
@media (max-width: 1024px) {
    .events-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    }

    .page-header h1 {
        font-size: 2rem;
    }

    .page-subtitle {
        font-size: 1rem;
    }
}

@media (max-width: 768px) {
    .events-page {
        padding: 1rem 0;
    }

    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 1.8rem;
    }

    .events-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .event-image {
        height: 220px;
    }

    .event-content {
        padding: 1.2rem;
    }

    .event-content h2 {
        font-size: 1.3rem;
    }

    .event-meta-item {
        font-size: 0.9rem;
    }

    .event-footer {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .event-footer .btn {
        width: 100%;
        padding: 0.8rem;
    }

    .price-value {
        font-size: 1.3rem;
    }

    .modal-content {
        width: 95%;
        max-height: 85vh;
    }

    .modal-header h2 {
        font-size: 1.3rem;
    }

    .modal-footer {
        flex-direction: column;
    }

    .modal-footer .btn {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .page-header h1 {
        font-size: 1.5rem;
        padding-bottom: 0.8rem;
    }

    .page-subtitle {
        font-size: 0.95rem;
    }

    .event-image {
        height: 200px;
    }

    .event-content h2 {
        font-size: 1.2rem;
    }

    .event-content {
        padding: 1rem;
    }

    .event-date-badge {
        padding: 0.4rem 0.8rem;
        top: 0.8rem;
        right: 0.8rem;
    }

    .date-day {
        font-size: 1.5rem;
    }

    .date-month {
        font-size: 0.8rem;
    }

    .event-meta-item {
        font-size: 0.85rem;
    }

    .event-description {
        font-size: 0.9rem;
    }

    .no-events {
        padding: 3rem 1.5rem;
    }

    .no-events h2 {
        font-size: 1.3rem;
    }
}
</style>

<script>
let currentEventBooking = {
    id: 0,
    name: '',
    price: 0
};

function bookEvent(eventId, eventName, price) {
    currentEventBooking = { id: eventId, name: eventName, price: price };

    document.getElementById('booking-event-name').textContent = eventName;
    document.getElementById('booking-quantity').value = 1;
    document.getElementById('booking-name').value = '';
    document.getElementById('booking-email').value = '';
    document.getElementById('booking-phone').value = '';

    updateBookingTotal();

    document.getElementById('booking-modal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeBookingModal() {
    document.getElementById('booking-modal').style.display = 'none';
    document.body.style.overflow = '';
}

function updateBookingTotal() {
    const quantity = parseInt(document.getElementById('booking-quantity').value) || 1;
    const total = currentEventBooking.price * quantity;
    document.getElementById('booking-total-price').textContent = total.toFixed(2) + ' CHF';
}

function confirmBooking() {
    const quantity = parseInt(document.getElementById('booking-quantity').value);
    const name = document.getElementById('booking-name').value.trim();
    const email = document.getElementById('booking-email').value.trim();
    const phone = document.getElementById('booking-phone').value.trim();

    if (!name || !email) {
        alert('Bitte füllen Sie alle erforderlichen Felder aus.');
        return;
    }

    if (!email.includes('@')) {
        alert('Bitte geben Sie eine gültige E-Mail-Adresse ein.');
        return;
    }

    // Add event ticket to cart
    if (typeof cart !== 'undefined') {
        cart.addEventTicket(currentEventBooking.id, currentEventBooking.name, currentEventBooking.price, quantity, {
            customer_name: name,
            customer_email: email,
            customer_phone: phone
        });

        closeBookingModal();
        showNotification(`${quantity} Ticket(s) für "${currentEventBooking.name}" zum Warenkorb hinzugefügt!`, 'success');
    } else {
        alert('Warenkorb-System nicht verfügbar. Bitte laden Sie die Seite neu.');
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeBookingModal();
    }
});
</script>
