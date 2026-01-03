# Terminkalender - Online Terminbuchungssystem

Ein sicheres und benutzerfreundliches Online-Terminbuchungssystem für Arztpraxen.

## Features

✅ **Patientenseitig:**
- Übersichtliche Kalenderansicht (6 Monate im Voraus)
- Einfache Terminbuchung mit Namen, Telefon und optionalem Kommentar
- Sofortige Verfügbarkeitsaktualisierung
- Deutsche Benutzeroberfläche
- Responsive Design (mobile-friendly)

✅ **Administratorbereich:**
- Sichere Login-Verwaltung
- Wöchentliche Verfügbarkeit festlegen (mit Dauer: 20-60 Min.)
- Ausnahmen für spezielle Tage
- Mehrere Standorte verwalten
- Gebuchte Termine einsehen und stornieren
- Automatische E-Mail-Benachrichtigungen

✅ **Sicherheit:**
- Passwort-Hashing
- CSRF-Schutz
- SQL-Injection-Schutz
- XSS-Schutz
- Session-Management
- Brute-Force-Schutz
- Automatische Löschung alter Termine (DSGVO-konform)

## Installation

### Schritt 1: Dateien hochladen

1. Laden Sie den kompletten Ordner "Terminkalender" via FTP auf Ihren Webserver
2. Empfohlener Pfad: `public_html/terminkalender/`

### Schritt 2: Konfiguration anpassen

**2.1 Datenbank-Konfiguration**

Bearbeiten Sie `install.php`:
```php
define('DB_HOST', 'mysqlsvr80.world4you.com');
define('DB_NAME', '5355254db1');
define('DB_USER', 'sql8079772');
define('DB_PASS', 'IHR_DATENBANK_PASSWORT'); // ← ÄNDERN!

// Admin-Zugangsdaten festlegen
define('ADMIN_USERNAME', 'doctor'); // ← ÄNDERN!
define('ADMIN_PASSWORD', 'SicheresPasswort123!'); // ← ÄNDERN!
```

**2.2 SMTP-Konfiguration**

Bearbeiten Sie `includes/config.php`:
```php
define('SMTP_PASSWORD', 'IHR_EMAIL_PASSWORT'); // ← ÄNDERN!
```

### Schritt 3: Installation ausführen

1. Öffnen Sie im Browser: `https://winter.wien/terminkalender/install.php`
2. Die Datenbanktabellen werden automatisch erstellt
3. Der Admin-User wird angelegt
4. **WICHTIG:** Notieren Sie sich die Zugangsdaten!

### Schritt 4: Installation absichern

Nach erfolgreicher Installation:

1. **Löschen oder umbenennen Sie `install.php`**
   ```bash
   # Via FTP oder SSH:
   mv install.php install.php.disabled
   ```

2. **Aktivieren Sie den .htaccess-Schutz**
   Bearbeiten Sie `.htaccess` und entfernen Sie die `#` vor:
   ```apache
   <Files "install.php">
       Require all denied
   </Files>
   ```

### Schritt 5: Grundkonfiguration

1. Melden Sie sich im Admin-Bereich an: `https://winter.wien/terminkalender/admin/`
2. Gehen Sie zu **"Standorte"**:
   - Bearbeiten Sie die beiden Standorte
   - Tragen Sie die echten Adressen ein
   - Aktivieren/Deaktivieren Sie Standorte nach Bedarf

3. Gehen Sie zu **"Verfügbarkeit"**:
   - Legen Sie Ihre wöchentlichen Sprechzeiten fest
   - Wählen Sie Standort, Wochentag, Zeitraum und Termindauer
   - Wiederholen Sie dies für alle Sprechzeiten

4. Gehen Sie zu **"Einstellungen"**:
   - Ändern Sie Ihr Passwort!

## Verwendung

### Für Patienten

1. Besuchen Sie: `https://winter.wien/terminkalender/`
2. Wählen Sie einen Standort
3. Klicken Sie auf einen verfügbaren Termin (blau)
4. Geben Sie Ihre Daten ein
5. Klicken Sie auf "Termin bestätigen"
6. Sie erhalten eine Bestätigungsmeldung

### Für den Arzt

**Dashboard:**
- Übersicht aller gebuchten Termine
- Termine können storniert werden

**Verfügbarkeit:**
- Wöchentliche Sprechzeiten festlegen
- Einzelne Tage sperren
- Spezielle Verfügbarkeiten für bestimmte Tage

**Standorte:**
- Ordinationsstandorte verwalten
- Adressen bearbeiten
- Standorte aktivieren/deaktivieren

## E-Mail-Benachrichtigungen

Bei jeder Terminbuchung wird automatisch eine E-Mail an `ordination@winter.wien` gesendet mit:
- Name und Telefonnummer des Patienten
- Datum und Uhrzeit
- Standort
- Optional: Kommentar

## Datenschutz & DSGVO

- **Datenminimierung:** Nur Name, Telefon und optionaler Kommentar werden gespeichert
- **Automatische Löschung:** Termine älter als 30 Tage werden automatisch gelöscht
- **Sichere Speicherung:** Alle Daten liegen auf österreichischen Servern (world4you)
- **Zugriffskontrolle:** Nur der Arzt kann nach Login auf Patientendaten zugreifen

## Fehlerbehebung

### Problem: "Database connection failed"
**Lösung:** Überprüfen Sie die Datenbankdaten in `includes/config.php`

### Problem: Keine E-Mails werden versendet
**Lösung:**
1. Überprüfen Sie SMTP-Passwort in `includes/config.php`
2. Testen Sie, ob Ihr Hosting E-Mail-Versand erlaubt
3. Kontaktieren Sie world4you Support für SMTP-Einstellungen

### Problem: "Session expired" im Admin
**Lösung:** Sie waren 30 Minuten inaktiv. Melden Sie sich erneut an.

### Problem: Termine werden nicht angezeigt
**Lösung:**
1. Überprüfen Sie, ob Sie Verfügbarkeiten definiert haben
2. Prüfen Sie, ob der gewählte Standort aktiv ist
3. Prüfen Sie, ob das Datum nicht in der Vergangenheit liegt

## Technische Details

**System-Anforderungen:**
- PHP 8.3+ (bereits auf Ihrem Server vorhanden)
- MySQL 8.0+ (bereits vorhanden)
- Apache mit mod_rewrite
- SSL/HTTPS empfohlen (für Datensicherheit)

**Verwendete Technologien:**
- Backend: PHP 8.3, MySQL 8.0
- Frontend: Vanilla JavaScript (kein Framework erforderlich)
- Styling: Pure CSS mit CSS-Variablen
- Sicherheit: Prepared Statements, CSRF-Tokens, Password-Hashing

**Datenbankstruktur:**
- `wp_terminkalender_admin` - Admin-Benutzer
- `wp_terminkalender_locations` - Standorte
- `wp_terminkalender_availability` - Wöchentliche Verfügbarkeit
- `wp_terminkalender_exceptions` - Ausnahmen/Sperrtage
- `wp_terminkalender_appointments` - Gebuchte Termine

## Wichtige Dateien

```
Terminkalender/
├── index.php                    # Patient calendar
├── install.php                  # Installation (nach Setup löschen!)
├── .htaccess                    # Security rules
│
├── admin/                       # Admin panel
│   ├── index.php               # Login
│   ├── dashboard.php           # Termine overview
│   ├── availability.php        # Manage availability
│   ├── locations.php           # Manage locations
│   ├── settings.php            # Change password
│   └── logout.php              # Logout
│
├── includes/                    # Backend core
│   ├── config.php              # Configuration (SMTP, DB)
│   ├── db.php                  # Database class
│   ├── auth.php                # Authentication
│   └── functions.php           # Helper functions
│
├── api/                         # AJAX endpoints
│   ├── get_slots.php           # Get available slots
│   ├── book_appointment.php    # Book appointment
│   ├── cancel_appointment.php  # Cancel (admin)
│   ├── save_availability.php   # Save availability (admin)
│   ├── save_exception.php      # Save exceptions (admin)
│   └── save_location.php       # Save locations (admin)
│
└── assets/
    ├── css/
    │   └── style.css           # All styles
    └── js/
        ├── calendar.js         # Patient calendar logic
        ├── admin.js            # Admin general functions
        ├── availability.js     # Availability management
        └── locations.js        # Location management
```

## Support & Wartung

**Regelmäßige Aufgaben:**
1. Backup der Datenbank (wöchentlich)
2. Überprüfung der Verfügbarkeiten
3. Passwort regelmäßig ändern (alle 3 Monate)

**Bei Problemen:**
1. Überprüfen Sie die PHP-Fehlerprotokolle
2. Überprüfen Sie die Browser-Konsole (F12)
3. Stellen Sie sicher, dass alle Dateien korrekt hochgeladen wurden

## Sicherheitshinweise

⚠️ **WICHTIG:**
- Ändern Sie SOFORT nach Installation das Admin-Passwort!
- Löschen Sie `install.php` nach der Installation!
- Verwenden Sie HTTPS für die gesamte Website
- Erstellen Sie regelmäßig Backups
- Halten Sie PHP auf dem neuesten Stand
- Geben Sie Ihre Zugangsdaten niemals weiter

## Erweiterungsmöglichkeiten

Das System kann einfach erweitert werden um:
- SMS-Benachrichtigungen
- Erinnerungs-E-Mails
- iCalendar-Export
- Mehr Standorte
- Mehrere Admin-Benutzer
- Termin-Historie

## Lizenz & Copyright

© 2025 Terminkalender für Dr. Winter
Entwickelt speziell für winter.wien

---

**Version:** 1.0
**Letztes Update:** 2025-01-09
**Entwickelt für:** PHP 8.3 + MySQL 8.0 auf world4you Hosting
