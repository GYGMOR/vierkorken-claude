<?php
// pages/reset-password.php - Neues Passwort setzen
$token = $_GET['token'] ?? '';
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>Neues Passwort setzen</h1>
        <p class="auth-subtitle">Gib dein neues Passwort ein.</p>

        <div id="token-loading" style="text-align: center; padding: 2rem;">
            <p>Überprüfe Token...</p>
        </div>

        <div id="reset-form-container" style="display: none;">
            <form id="reset-password-form" onsubmit="resetPassword(event)">
                <input type="hidden" id="reset-token" value="<?php echo safe_output($token); ?>">

                <div class="form-group">
                    <label for="new-password">Neues Passwort</label>
                    <input type="password" id="new-password" name="password" minlength="8" required>
                    <small class="form-hint">Mindestens 8 Zeichen</small>
                </div>

                <div class="form-group">
                    <label for="confirm-password">Passwort bestätigen</label>
                    <input type="password" id="confirm-password" name="confirm_password" minlength="8" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Passwort ändern</button>
            </form>
        </div>

        <div id="reset-message" style="margin-top: 1rem; display: none;"></div>

        <div class="auth-links">
            <a href="?modal=login">Zum Login</a>
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

.form-hint {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 0.3rem;
    display: block;
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
document.addEventListener('DOMContentLoaded', function() {
    verifyToken();
});

function verifyToken() {
    const token = document.getElementById('reset-token').value;

    fetch(`api/password-reset.php?action=verify&token=${token}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('token-loading').style.display = 'none';

            if (data.success) {
                document.getElementById('reset-form-container').style.display = 'block';
            } else {
                const messageDiv = document.getElementById('reset-message');
                messageDiv.style.display = 'block';
                messageDiv.className = 'message-box message-error';
                messageDiv.innerHTML = '<strong>Ungültiger Link:</strong><br>' + data.error;
            }
        })
        .catch(e => {
            console.error('Error:', e);
            document.getElementById('token-loading').style.display = 'none';
            const messageDiv = document.getElementById('reset-message');
            messageDiv.style.display = 'block';
            messageDiv.className = 'message-box message-error';
            messageDiv.innerHTML = '<strong>Fehler:</strong> Verbindungsproblem.';
        });
}

function resetPassword(event) {
    event.preventDefault();

    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    const token = document.getElementById('reset-token').value;
    const messageDiv = document.getElementById('reset-message');
    const submitBtn = event.target.querySelector('button[type="submit"]');

    // Validate passwords match
    if (newPassword !== confirmPassword) {
        messageDiv.style.display = 'block';
        messageDiv.className = 'message-box message-error';
        messageDiv.innerHTML = '<strong>Fehler:</strong> Passwörter stimmen nicht überein.';
        return;
    }

    submitBtn.disabled = true;
    submitBtn.textContent = 'Speichern...';

    fetch('api/password-reset.php?action=reset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            token: token,
            password: newPassword
        })
    })
    .then(r => r.json())
    .then(data => {
        messageDiv.style.display = 'block';

        if (data.success) {
            messageDiv.className = 'message-box message-success';
            messageDiv.innerHTML = '<strong>Erfolg!</strong><br>' + data.message;
            document.getElementById('reset-password-form').reset();

            // Redirect to login after 2 seconds
            setTimeout(() => {
                window.location.href = '?modal=login';
            }, 2000);
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
        submitBtn.textContent = 'Passwort ändern';
    });
}
</script>
