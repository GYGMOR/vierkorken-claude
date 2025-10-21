// assets/js/main.js
// Hauptscript für Vier Korken Webseite

console.log('Vier Korken App geladen');

// ============================================
// 1. GLOBALE FUNKTIONEN
// ============================================

// Nachricht anzeigen (Toast)
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerText = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ecc71' : type === 'error' ? '#e74c3c' : '#3498db'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 5px;
        z-index: 10000;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// API Call Funktion
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json'
        }
    };
    
    if (data && method !== 'GET') {
        options.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(`api/${endpoint}`, options);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API Fehler:', error);
        showNotification('Es ist ein Fehler aufgetreten', 'error');
        return null;
    }
}

// ============================================
// 2. WARENKORB COUNTER AKTUALISIEREN
// ============================================

function updateCartCount() {
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const count = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartBadge = document.getElementById('cart-count');
    
    if (cartBadge) {
        cartBadge.innerText = count;
        cartBadge.style.display = count > 0 ? 'flex' : 'none';
    }
}

// Bei Seite laden
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    console.log('DOM geladen');
});

// Bei Storage-Änderung (andere Tabs)
window.addEventListener('storage', updateCartCount);

// ============================================
// 3. SCROLL TO TOP BUTTON
// ============================================

const scrollToTopBtn = document.createElement('button');
scrollToTopBtn.id = 'scroll-to-top';
scrollToTopBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"></polyline></svg>';
scrollToTopBtn.style.cssText = `
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--primary-color, #722c2c);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    font-size: 1.5rem;
    cursor: pointer;
    z-index: 999;
    opacity: 0;
    transition: opacity 0.3s ease;
    display: none;
`;

document.body.appendChild(scrollToTopBtn);

window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollToTopBtn.style.opacity = '1';
        scrollToTopBtn.style.display = 'block';
    } else {
        scrollToTopBtn.style.opacity = '0';
        scrollToTopBtn.style.display = 'none';
    }
});

scrollToTopBtn.addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// ============================================
// 4. NAVIGATION AKTIV MARK
// ============================================

function markActiveNav() {
    const currentPage = new URLSearchParams(window.location.search).get('page') || 'home';
    const navLinks = document.querySelectorAll('.nav a');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.href.includes(`page=${currentPage}`)) {
            link.classList.add('active');
        }
    });
}

document.addEventListener('DOMContentLoaded', markActiveNav);

// ============================================
// 5. FORMULAR VALIDIERUNG
// ============================================

function validateEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.style.borderColor = 'red';
            isValid = false;
        } else {
            input.style.borderColor = '';
        }
        
        if (input.type === 'email' && !validateEmail(input.value)) {
            input.style.borderColor = 'red';
            isValid = false;
        }
    });
    
    return isValid;
}

// ============================================
// 6. LOADING ANIMATION
// ============================================

function showLoading(show = true) {
    let loader = document.getElementById('loader');
    
    if (show && !loader) {
        loader = document.createElement('div');
        loader.id = 'loader';
        loader.innerHTML = 'Lädt...';
        loader.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            z-index: 9999;
        `;
        document.body.appendChild(loader);
    } else if (!show && loader) {
        loader.remove();
    }
}

// ============================================
// 7. KEYBOARD SHORTCUTS
// ============================================

document.addEventListener('keydown', function(e) {
    // Strg+K = Zum Shop
    if (e.ctrlKey && e.key === 'k') {
        e.preventDefault();
        window.location.href = '?page=shop';
    }
    
    // Strg+C = Zum Warenkorb
    if (e.ctrlKey && e.key === 'c') {
        e.preventDefault();
        window.location.href = '?page=cart';
    }
});

console.log('Shortcuts: Strg+K = Shop, Strg+C = Warenkorb');