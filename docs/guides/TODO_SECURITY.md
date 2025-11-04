# 🔒 SICHERHEITS-TODO LISTE

## ✅ BEREITS ERLEDIGT

- [x] SQL-Injection in `includes/functions.php` behoben (20+ Funktionen)
- [x] SQL-Injection in `api/auth.php` behoben
- [x] Session Security implementiert (`config/security.php`)
- [x] CSRF-Protection implementiert
- [x] Rate Limiting für Login/Register
- [x] Passwort-Stärke-Prüfung
- [x] Security Headers
- [x] File Upload Validierung
- [x] Security Logging
- [x] Input Sanitization Funktionen

---

## ⚠️ NOCH ZU TUN (EMPFOHLEN)

### 1. HTTPS AKTIVIEREN (WICHTIG für Produktion)

**Datei:** `config/security.php`, Zeile 12

**Ändern:**
```php
// VON:
ini_set('session.cookie_secure', 0);

// ZU:
ini_set('session.cookie_secure', 1); // Nur über HTTPS
```

**WICHTIG:** Erst aktivieren, wenn SSL-Zertifikat installiert ist!

---

### 2. CSRF-TOKENS IN ALLE FORMULARE EINBAUEN

#### Admin-Dashboard Formulare:
**Datei:** `pages/admin-dashboard.php`

**Alle Formulare (z.B. Wein erstellen, Settings ändern, etc.):**
```php
<form method="POST" action="...">
    <?php echo csrf_field(); ?>
    <!-- Rest der Formularfelder -->
</form>
```

#### Weitere Formulare prüfen:
- `pages/checkout.php` - Falls direkte POST-Requests (aktuell JavaScript)
- `pages/user-portal.php` - Falls Formulare vorhanden
- Alle Admin-Formulare

---

### 3. API-ENDPUNKTE MIT CSRF ABSICHERN

**Betroffene Dateien:**

#### `api/orders.php`
```php
// Am Anfang der Datei hinzufügen:
require_once '../config/security.php';
init_secure_session();

// Bei POST-Aktionen:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf();
}
```

#### `api/coupons.php`
```php
require_once '../config/security.php';
init_secure_session();
// Bei Änderungen (create, update, delete):
require_csrf();
```

#### Weitere APIs:
- `api/wishlist.php`
- `api/events.php`
- `api/news-items.php`
- `api/user-addresses.php` (bereits auth, CSRF hinzufügen)
- `api/user-portal.php`

**Pattern:**
```php
<?php
header('Content-Type: application/json');

require_once '../config/security.php';
init_secure_session();

require_once '../config/database.php';
require_once '../includes/functions.php';

// Bei schreibenden Operationen:
if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
    require_csrf();
}

$action = $_REQUEST['action'] ?? '';
// ... Rest der API
```

---

### 4. ERROR MESSAGES GENERISCH MACHEN

**Problem:** Datenbank-Fehler werden teilweise direkt ausgegeben

#### `config/database.php`
```php
// VORHER (Zeile 26-30):
if ($connection->connect_error) {
    http_response_code(500);
    die(json_encode([
        'error' => 'Datenbankverbindung fehlgeschlagen',
        'message' => $connection->connect_error  // ❌ Zu detailliert
    ]));
}

// NACHHER (sicherer):
if ($connection->connect_error) {
    error_log("DB Connection Error: " . $connection->connect_error);
    http_response_code(500);
    die(json_encode([
        'error' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.'
    ]));
}
```

#### `includes/functions.php`
```php
// Funktion db_error() überarbeiten (Zeile 124-127):
function db_error($error_msg) {
    error_log("DB ERROR: " . $error_msg);
    // Nicht: die("Datenbankfehler: " . safe_output($error_msg));
    // Sondern:
    die("Ein Fehler ist aufgetreten. Bitte kontaktieren Sie den Support.");
}
```

---

### 5. CONTENT SECURITY POLICY VERSCHÄRFEN

**Datei:** `config/security.php`, Zeile 235

**Aktuell (weniger sicher):**
```php
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval'; img-src 'self' data: https:;");
```

**Empfohlen (nach Tests):**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:;");
```

**WICHTIG:** Vorher testen, ob JavaScript/CSS noch funktioniert!

**Schrittweise Verschärfung:**
1. Testen mit aktuellem CSP
2. `'unsafe-eval'` entfernen (meist nicht nötig)
3. Inline-Scripts in externe Dateien auslagern
4. `'unsafe-inline'` für Scripts entfernen
5. Nonces für kritische Inline-Scripts

---

### 6. PHP.INI KONFIGURATION (Produktionsserver)

**Wichtige Settings:**
```ini
; Fehler nicht anzeigen (Sicherheit)
display_errors = Off
display_startup_errors = Off

; Aber loggen
log_errors = On
error_log = /var/log/php/error.log

; Session Security (zusätzlich zu Security.php)
session.use_strict_mode = 1
session.cookie_httponly = 1
session.cookie_secure = 1    ; nur mit HTTPS
session.cookie_samesite = Lax

; File Uploads
file_uploads = On
upload_max_filesize = 5M
post_max_size = 10M

; Disable gefährliche Funktionen
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

; SQL-Safe Mode
sql.safe_mode = On
```

---

## 🧪 TESTING NACH FIXES

### 1. CSRF-Token Testen
```javascript
// Im Browser Console:
// Formular ohne CSRF-Token absenden sollte Fehler 403 geben
fetch('api/orders.php', {
    method: 'POST',
    body: JSON.stringify({action: 'create'}),
    headers: {'Content-Type': 'application/json'}
}).then(r => r.json()).then(console.log);
// Erwartung: {"success": false, "error": "CSRF-Token ungültig"}
```

### 2. Rate Limiting Testen
```bash
# 6x Login-Request hintereinander:
for i in {1..6}; do
  curl -X POST http://localhost/vierkorken/api/auth.php \
    -d "action=login&email=test@test.com&password=wrong"
done
# Ab 6. Request: HTTP 429 Too Many Requests
```

### 3. SQL-Injection Testen (sollte NICHT funktionieren)
```bash
# Früher: SQL-Injection möglich
# Jetzt: Prepared Statements schützen
curl "http://localhost/vierkorken/?page=product&id=1' OR '1'='1"
# Erwartung: Kein Fehler, keine unerwarteten Daten
```

### 4. XSS Testen (sollte NICHT funktionieren)
```html
<!-- In Input-Feld eingeben: -->
<script>alert('XSS')</script>

<!-- Nach Speichern sollte Output sein: -->
&lt;script&gt;alert('XSS')&lt;/script&gt;
```

---

## 📝 DEPLOYMENT CHECKLISTE

### Vor Live-Gang:

- [ ] Backup der Datenbank erstellen
- [ ] Backup der aktuellen Dateien erstellen
- [ ] SSL-Zertifikat installiert und getestet
- [ ] `session.cookie_secure = 1` aktiviert
- [ ] `display_errors = Off` in php.ini
- [ ] Alle CSRF-Tokens in Formularen
- [ ] Alle APIs mit CSRF gesichert
- [ ] Error Messages generisch gemacht
- [ ] CSP verschärft (optional, nach Tests)
- [ ] PHP.ini Production-Settings
- [ ] Security Scan durchführen (z.B. OWASP ZAP)
- [ ] Logs-Verzeichnis schreibbar
- [ ] Firewall-Regeln geprüft
- [ ] Backup-Strategie dokumentiert

### Nach Live-Gang:

- [ ] Monitoring aktivieren
- [ ] Error Logs regelmäßig prüfen
- [ ] Security Logs regelmäßig prüfen
- [ ] Penetration Test durchführen (optional)
- [ ] Updates regelmäßig einspielen

---

## 🆘 SUPPORT & HILFE

### Bei Problemen:

1. **Error Logs prüfen:**
   ```bash
   tail -f /var/log/php/error.log
   ```

2. **Security Events prüfen:**
   ```bash
   grep "SECURITY:" /var/log/php/error.log
   ```

3. **CSRF-Token Probleme:**
   - Browser-Cache leeren
   - Session neu starten
   - CSRF-Token im Formular prüfen

4. **Session-Probleme:**
   - Sessions-Verzeichnis schreibbar?
   - `session_start()` Fehler?
   - `init_secure_session()` aufgerufen?

---

## 📚 WEITERE RESSOURCEN

- **OWASP Top 10:** https://owasp.org/www-project-top-ten/
- **PHP Security Checklist:** https://www.php.net/manual/en/security.php
- **CSRF Prevention:** https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
- **SQL Injection Prevention:** https://cheatsheetseries.owasp.org/cheatsheets/SQL_Injection_Prevention_Cheat_Sheet.html

---

**Erstellt:** 2025-01-21
**Version:** 1.0
