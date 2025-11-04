# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Vier Korken** is a Swiss wine e-commerce website built with PHP, MySQL, and vanilla JavaScript. The application features an admin dashboard for content management, a shopping cart system, and editable content capabilities.

## Technology Stack

- **Backend**: PHP 8.x with MySQLi
- **Database**: MySQL (MariaDB)
- **Frontend**: Vanilla JavaScript (ES6+)
- **Architecture**: Traditional PHP MVC-like structure with page routing
- **No build tools**: Direct file serving (no webpack, npm, etc.)

## Database Configuration

Database credentials are stored in `config/database.php`:
- Connection uses MySQLi (not PDO)
- Global `$db` variable for database access
- Helper function `db_query()` for prepared statements at config/database.php:40

**Important**: The database password is hardcoded in config/database.php:11. Never commit changes to this file if credentials are modified.

## Architecture

### Entry Point & Routing

`index.php` is the single entry point:
- Uses query parameter routing: `?page=home`, `?page=shop`, etc.
- Allowed pages defined in `$allowed_pages` array (index.php:19)
- Session management and admin authentication handled here
- Loads theme colors dynamically from database (index.php:41)

### Directory Structure

```
├── api/                    # JSON API endpoints
├── assets/
│   ├── css/               # Stylesheets (including dynamic PHP-generated CSS)
│   ├── images/            # Images, wines, banners, kantone
│   └── js/                # Client-side JavaScript
├── components/            # Reusable PHP components
├── config/                # Database and configuration
│   ├── database.php       # DB connection (gitignored)
│   ├── keys.php           # API keys (gitignored)
│   └── security.php       # Security functions
├── database/              # Database files
│   └── migrations-archive/ # Archived SQL migrations
├── DB/                    # Database dumps (gitignored)
├── docs/                  # Documentation
│   ├── guides/            # Integration guides, security docs
│   └── reports/           # Status reports, debugging logs
├── includes/              # Shared PHP includes (header, footer, functions)
├── pages/                 # Page templates loaded by index.php
├── utils/                 # Utilities
│   └── scripts/           # Utility scripts (gitignored)
├── index.php              # Main entry point
├── CLAUDE.md              # Project documentation for Claude Code
└── CLEANUP_REPORT.md      # Latest cleanup documentation
```

### Key Files

- `includes/functions.php`: Global helper functions for the entire application
- `includes/editable.php`: Content editing system for admin users
- `config/database.php`: Database connection and query helpers
- `assets/js/main.js`: Global JavaScript utilities, navigation, notifications
- `assets/js/cart.js`: Shopping cart implementation using localStorage

## Admin System

### Authentication & Access

- Admin check: `is_admin()` function in includes/functions.php:69
- Admin users stored in `admins` table linked to `users` table
- Session variable: `$_SESSION['is_admin']`
- Protected pages check admin status in index.php:30-38

### Admin Dashboard

Located at `pages/admin-dashboard.php`:
- Tab-based interface using `?page=admin-dashboard&tab=overview`
- Manages: website content, wines, featured wines, colors/theme, footer, pages
- CRUD operations for wines (create, edit, delete)
- Settings stored in `settings` table via `get_setting()` and `update_setting()`

### Editable Content System

The `includes/editable.php` file provides helper functions for inline content editing:
- `editable($key, $content, $tag, $class)`: Outputs editable text
- `editable_textarea()`, `editable_link()`, `editable_button()`: Specialized variants
- Content stored in database `settings` table with key-value pairs
- Edit mode toggled via `utils/scripts/toggle-edit.php` (sets `$_SESSION['edit_mode']`)
- API endpoint: `api/edit-content.php` handles saves

## Shopping Cart

### Implementation

Client-side cart using localStorage (`assets/js/cart.js`):
- `ShoppingCart` class manages cart state
- Storage key: `vier_korken_cart`
- Methods: `addItem()`, `removeItem()`, `updateQuantity()`, `clearCart()`
- Cart count badge updated via `updateCartCount()` function

### Server Integration

- `api/cart.php`: Validates wine availability and stock
- Actions: `add`, `get_count`, `validate`
- Uses `get_wine_by_id()` from includes/functions.php:61

## Database Schema

### Key Tables

- `wines`: Product catalog (id, name, category_id, price, stock, producer, vintage, region, alcohol_content, image_url, description, is_featured)
- `categories`: Wine categories (id, name)
- `settings`: Key-value store for site content and configuration
- `theme_settings`: Separate table for theme colors
- `users`: User accounts
- `admins`: Admin user mapping
- `orders`: Order records

### Theme Settings

Managed separately from general settings:
- Functions: `get_theme_color()`, `update_theme_color()`, `get_all_theme_settings()` (includes/functions.php:180-216)
- Used for dynamic CSS color injection in index.php:82-88

## Common Development Patterns

### Database Queries

**Safe queries using prepared statements:**
```php
$result = db_query("SELECT * FROM wines WHERE id = ?", [$wine_id]);
```

**Direct queries (use with caution):**
```php
global $db;
$result = $db->query("SELECT * FROM wines");
$wines = $result->fetch_all(MYSQLI_ASSOC);
```

### Settings Management

```php
// Get setting
$value = get_setting('hero_title', 'Default Title');

// Update setting
update_setting('hero_title', 'New Title');

// Load all settings
$settings = get_all_settings();
```

### Adding New Pages

1. Add page name to `$allowed_pages` in index.php:19
2. Create `pages/your-page.php`
3. Add title mapping in index.php:54
4. Link to page: `?page=your-page`

### API Endpoints

Pattern for `api/` files:
```php
header('Content-Type: application/json');
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

$action = $_REQUEST['action'] ?? '';
try {
    // Handle actions
    echo json_encode(['success' => true, 'data' => $result]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
```

## Security Considerations

### XSS Protection

Always use `safe_output()` for user-generated content:
```php
echo safe_output($wine['name']);
```

### SQL Injection

- Use `db_query()` with parameterized queries
- Use `$db->real_escape_string()` for direct queries
- Type cast IDs: `$wine_id = (int)$_GET['id'];`

### Authentication

Admin-only pages/APIs must check:
```php
if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    // Redirect or return error
}
```

## JavaScript Architecture

### Global Functions (main.js)

- `showNotification(message, type)`: Toast notifications
- `apiCall(endpoint, method, data)`: Async API wrapper
- `updateCartCount()`: Sync cart badge with localStorage
- Keyboard shortcuts: Ctrl+K (shop), Ctrl+C (cart)

### Cart System (cart.js)

- Global `cart` instance of `ShoppingCart` class
- `addToCart(wineId, wineName, price, quantity)`: Add items
- `renderCartPage()`: Render cart UI on cart page
- Events: `cartUpdated` event fired on cart changes

## Testing & Development

### Local Development

This appears to be a direct-deployment PHP application. To run locally:
1. Use XAMPP, WAMP, or similar PHP/MySQL stack
2. Import database schema (not included in repo)
3. Update database credentials in `config/database.php`
4. Access via `http://localhost/vierkorken/`

### Utility Scripts

Located in `utils/scripts/` (gitignored):

**Generate password hash:**
```bash
php utils/scripts/generate-hash.php
```

**Download Kanton wappen:**
```bash
php utils/scripts/download_kantone_wappen.php
```

**Toggle edit mode (for development):**
```bash
php utils/scripts/toggle-edit.php
```

## Important Notes

- **Version control**: Git repository with .gitignore for sensitive files
- **No package manager**: No npm/composer dependencies
- **Direct database credentials**: Stored in config/database.php (gitignored)
- **API keys**: Stored in config/keys.php (gitignored)
- **Session-based auth**: Admin authentication uses PHP sessions
- **No REST API**: Uses traditional form submissions and custom API endpoints
- **Inline styles**: Some components have embedded `<style>` tags (e.g., admin-dashboard.php)
- **Dynamic CSS**: `assets/css/dynamic-colors.php` generates CSS from database settings
- **Documentation**: All docs in `docs/` folder (guides and reports separated)
