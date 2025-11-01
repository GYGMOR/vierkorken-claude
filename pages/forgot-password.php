<?php
// pages/forgot-password.php - Passwort vergessen
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Passwort vergessen?</h1>
        <p class="auth-subtitle">Gib deine E-Mail-Adresse ein und wir senden dir einen Link zum Zurücksetzen des Passworts.</p>

        <form id="forgot-password-form" onsubmit="sendPasswordResetEmail(event)">
            <div class="form-group">
                <label for="email">E-Mail-Adresse</label>
                <input type="email" id="forgot-email" name="email" autocomplete="email" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Reset-Link senden</button>
        </form>

        <div id="forgot-password-message" style="margin-top: 1rem; display: none;"></div>

        <div class="auth-links">
            <a href="?modal=login">Zurück zum Login</a>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 60vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem 1rem;
}

.auth-box {
    background: white;
    padding: 2.5rem;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    max-width: 450px;
    width: 100%;
}

.auth-box h1 {
    margin: 0 0 0.5rem 0;
    color: var(--primary-color);
    text-align: center;
}

.auth-subtitle {
    text-align: center;
    color: #6b7280;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.form-group input {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
}

.btn-block {
    width: 100%;
}

.auth-links {
    text-align: center;
    margin-top: 1.5rem;
}

.auth-links a {
    color: var(--primary-color);
    text-decoration: none;
}

.auth-links a:hover {
    text-decoration: underline;
}

.message-box {
    padding: 1rem;
    border-radius: 8px;
    margin-top: 1rem;
}

.message-success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #6ee7b7;
}

.message-error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #fca5a5;
}
</style>

<script>
function sendPasswordResetEmail(event) {
    event.preventDefault();

    const email = document.getElementById('forgot-email').value;
    const messageDiv = document.getElementById('forgot-password-message');
    const submitBtn = event.target.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Senden...';

    fetch('api/password-reset.php?action=request', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: email })
    })
    .then(r => r.json())
    .then(data => {
        messageDiv.style.display = 'block';

        if (data.success) {
            messageDiv.className = 'message-box message-success';
            messageDiv.innerHTML = '<strong>Email gesendet!</strong><br>' + data.message;
            document.getElementById('forgot-password-form').reset();
        } else {
            messageDiv.className = 'message-box message-error';
            messageDiv.innerHTML = '<strong>Fehler:</strong> ' + data.error;
        }
    })
    .catch(e => {
        console.error('Error:', e);
        messageDiv.style.display = 'block';
        messageDiv.className = 'message-box message-error';
        messageDiv.innerHTML = '<strong>Fehler:</strong> Etwas ist schiefgegangen.';
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Reset-Link senden';
    });
}
</script>
