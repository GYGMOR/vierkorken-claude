# 🔒 SECURITY QUICKSTART - VIER KORKEN

## 🎯 SCHNELLEINSTIEG

Die wichtigsten Sicherheitsfunktionen auf einen Blick.

---

## 📥 INSTALLATION

**Bereits erledigt!** Die Sicherheitsfunktionen sind bereits integriert in:
- `config/security.php` - Zentrale Security-Library
- `index.php` - Security-Init automatisch aktiv
- `includes/functions.php` - Alle SQL-Queries gesichert
- `api/auth.php` - Login/Register mit Rate-Limiting

---

## 🚀 VERWENDUNG IN NEUEN SEITEN/APIs

### 1. In neuen PHP-Seiten:
```php
<?php
// Security-Library laden (wenn nicht über index.php geladen)
require_once 'config/security.php';
init_secure_session();

// Rest deines Codes
?>
```

### 2. In neuen API-Endpunkten:
```php
<?php
header('Content-Type: application/json');

require_once '../config/security.php';
init_secure_session();

// CSRF-Schutz für POST/PUT/DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    require_csrf();
}

// Rate Limiting (optional)
check_rate_limit('api_action', 10, 60); // Max 10 in 60 Sek

require_once '../config/database.php';
// ... Rest der API
?>
```

---

## 🛡️ WICHTIGSTE FUNKTIONEN

### SQL-Injection verhindern
```php
// ✅ RICHTIG - Prepared Statements:
$stmt = $db->prepare("SELECT * FROM wines WHERE id = ?");
$stmt->bind_param("i", $wine_id);
$stmt->execute();
$result = $stmt->get_result();

// ❌ FALSCH - String-Interpolation:
$result = $db->query("SELECT * FROM wines WHERE id = $wine_id");
```

### XSS verhindern
```php
// ✅ RICHTIG - Output escapen:
echo safe_output($user_input);
echo sanitize_output($data); // Auch für Arrays

// ❌ FALSCH - Direct Output:
echo $user_input;
```

### CSRF-Protection in Formularen
```php
<form method="POST" action="api/example.php">
    <?php echo csrf_field(); ?> <!-- ✅ CSRF-Token -->
    <input type="text" name="name">
    <button type="submit">Senden</button>
</form>
```

### CSRF-Protection in APIs
```php
// Am Anfang der API-Datei:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf(); // Automatischer Check
}
```

### Input-Validierung
```php
// Email
$email = secure_email($_POST['email']);
if (!$email) {
    die("Ungültige E-Mail");
}

// Integer
$id = secure_int($_GET['id'], 0);

// String
$name = sanitize_string($_POST['name']);

// Telefon
$phone = sanitize_phone($_POST['phone']);
```

### File Upload
```php
$validation = validate_upload_file($_FILES['image']);
if (!$validation['success']) {
    die($validation['error']);
}

$safe_name = generate_safe_filename($_FILES['image']['name']);
move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $safe_name);
```

### Rate Limiting
```php
// Max 5 Versuche in 60 Sekunden
check_rate_limit('action_name', 5, 60);
```

### Access Control
```php
// Nur eingeloggte User
require_login();

// Nur Admins
require_admin();
```

### Security Logging
```php
log_security_event('failed_login', [
    'email' => $email,
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

---

## 🔥 HÄUFIGE FEHLER VERMEIDEN

### ❌ FEHLER 1: Direkte SQL-Queries
```php
// FALSCH:
$db->query("SELECT * FROM users WHERE email = '$email'");

// RICHTIG:
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
```

### ❌ FEHLER 2: Kein Output-Escaping
```php
// FALSCH:
echo $_POST['name'];

// RICHTIG:
echo safe_output($_POST['name']);
```

### ❌ FEHLER 3: Fehlende CSRF-Tokens
```php
// FALSCH:
<form method="POST">
    <input name="data">
</form>

// RICHTIG:
<form method="POST">
    <?php echo csrf_field(); ?>
    <input name="data">
</form>
```

### ❌ FEHLER 4: Schwache Passwörter erlauben
```php
// FALSCH:
if (strlen($password) < 6) { /* ... */ }

// RICHTIG:
$check = validate_password_strength($password);
if (!$check['valid']) {
    die(json_encode(['errors' => $check['errors']]));
}
```

---

## 📋 CHECKLISTE FÜR NEUE FEATURES

Bei jedem neuen Feature prüfen:

- [ ] Alle SQL-Queries mit Prepared Statements?
- [ ] Alle User-Inputs escapet vor Ausgabe?
- [ ] CSRF-Token in Formularen?
- [ ] CSRF-Check in APIs?
- [ ] Input-Validierung (Email, Int, String)?
- [ ] Rate Limiting bei kritischen Actions?
- [ ] Access Control (Login/Admin)?
- [ ] Security Logging bei wichtigen Events?
- [ ] File Uploads validiert?
- [ ] Error Messages generisch (keine DB-Details)?

---

## 🆘 HÄUFIGE PROBLEME & LÖSUNGEN

### Problem: "CSRF-Token ungültig"
**Lösung:**
- Browser-Cache leeren
- Session ist aktiv? (`init_secure_session()` aufgerufen?)
- Token im Formular? (`csrf_field()` vorhanden?)
- Token bei API-Call mitgeschickt?

### Problem: "Too Many Requests" (429)
**Lösung:**
- Rate Limit erreicht (gewollt!)
- Warten oder Limits anpassen in `check_rate_limit()`

### Problem: Session verliert Daten
**Lösung:**
- `init_secure_session()` statt `session_start()` verwenden
- IP-Binding deaktivieren in `config/security.php` (Zeile 40-47)

### Problem: SQL-Query funktioniert nicht
**Lösung:**
- Prepared Statement korrekt?
- `bind_param()` Types korrekt? (i=integer, s=string, d=double)
- `execute()` aufgerufen?
- `get_result()` für SELECT-Queries?

---

## 📚 WEITERE INFOS

- **Vollständiger Bericht:** `SECURITY_REPORT.md`
- **TODO-Liste:** `TODO_SECURITY.md`
- **Alle Funktionen:** `config/security.php` (mit inline-Docs)

---

## 🎉 FERTIG!

Deine Webseite ist jetzt **deutlich sicherer**. Beachte die TODO-Liste für weitere Verbesserungen (HTTPS, CSRF in allen APIs, etc.).

**Bei Fragen:** Siehe `SECURITY_REPORT.md` oder Code-Kommentare in `config/security.php`

---

**Version:** 1.0
**Letzte Aktualisierung:** 2025-01-21
