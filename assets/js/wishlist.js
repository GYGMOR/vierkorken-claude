// wishlist.js - Merkliste/Wishlist System (mit und ohne Account)
// Funktioniert mit localStorage für nicht-eingeloggte User

class Wishlist {
    constructor() {
        this.storageKey = 'vier_korken_wishlist';
        this.items = this.loadFromStorage();
        this.updateUI();
    }

    loadFromStorage() {
        try {
            const data = localStorage.getItem(this.storageKey);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error loading wishlist:', e);
            return [];
        }
    }

    saveToStorage() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.items));
            this.updateUI();
            this.dispatchEvent();
        } catch (e) {
            console.error('Error saving wishlist:', e);
        }
    }

    addItem(productId, productName) {
        if (!this.hasItem(productId)) {
            this.items.push({
                id: productId,
                name: productName,
                addedAt: new Date().toISOString()
            });
            this.saveToStorage();
            return true;
        }
        return false;
    }

    removeItem(productId) {
        const index = this.items.findIndex(item => item.id === productId);
        if (index !== -1) {
            this.items.splice(index, 1);
            this.saveToStorage();
            return true;
        }
        return false;
    }

    toggleItem(productId, productName) {
        if (this.hasItem(productId)) {
            this.removeItem(productId);
            return false; // removed
        } else {
            this.addItem(productId, productName);
            return true; // added
        }
    }

    hasItem(productId) {
        return this.items.some(item => item.id === productId);
    }

    getCount() {
        return this.items.length;
    }

    getItems() {
        return [...this.items];
    }

    clearAll() {
        this.items = [];
        this.saveToStorage();
    }

    updateUI() {
        const badge = document.getElementById('wishlist-count');
        const count = this.getCount();

        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }

        // Update alle Wishlist-Buttons
        document.querySelectorAll('[data-wishlist-id]').forEach(btn => {
            const productId = btn.getAttribute('data-wishlist-id');
            if (this.hasItem(productId)) {
                btn.classList.add('active');
                btn.setAttribute('title', 'Von Merkliste entfernen');
            } else {
                btn.classList.remove('active');
                btn.setAttribute('title', 'Zur Merkliste hinzufügen');
            }
        });
    }

    dispatchEvent() {
        window.dispatchEvent(new CustomEvent('wishlistUpdated', {
            detail: { count: this.getCount(), items: this.getItems() }
        }));
    }
}

// Global Wishlist Instance
const wishlist = new Wishlist();

// Global Toggle Function
function toggleWishlist(productId, productName) {
    const added = wishlist.toggleItem(productId, productName);

    if (added) {
        showNotification('Zur Merkliste hinzugefügt', 'success');
    } else {
        showNotification('Von Merkliste entfernt', 'info');
    }
}

// Listen to storage changes (from other tabs)
window.addEventListener('storage', function(e) {
    if (e.key === wishlist.storageKey) {
        wishlist.items = wishlist.loadFromStorage();
        wishlist.updateUI();
    }
});
