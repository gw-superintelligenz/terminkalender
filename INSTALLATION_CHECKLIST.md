# Installation Checklist - Terminkalender

Folgen Sie dieser Checkliste Schritt für Schritt:

## ☐ Vor dem Upload

### 1. Dateien bearbeiten

**install.php** (Zeilen 8-15):
```php
define('DB_PASS', 'IHR_DATENBANK_PASSWORT'); // ← EINTRAGEN!
define('ADMIN_USERNAME', 'doctor'); // ← ÄNDERN (optional)
define('ADMIN_PASSWORD', 'SicheresPasswort!'); // ← ÄNDERN!
```

**includes/config.php** (Zeile 21):
```php
define('SMTP_PASSWORD', 'IHR_EMAIL_PASSWORT'); // ← EINTRAGEN!
```

### 2. Optionale Anpassungen in includes/config.php
- Zeile 14-16: SMTP-Port ändern (falls nötig)
- Zeile 32: Session-Security für HTTPS (wenn SSL aktiv)

## ☐ Upload via FTP

1. Verbinden Sie sich mit Ihrem FTP-Client zu winter.wien
2. Navigieren Sie zu `public_html/` (oder Ihr Webroot)
3. Laden Sie den gesamten Ordner "Terminkalender" hoch
4. Warten Sie bis alle Dateien übertragen sind (ca. 30 Dateien)

## ☐ Installation durchführen

1. Öffnen Sie im Browser: `https://winter.wien/terminkalender/install.php`
2. ✓ Grüne Häkchen bei allen Tabellen?
3. ✓ Admin-User erstellt?
4. **WICHTIG:** Notieren Sie Username und Passwort!

## ☐ Nach der Installation

### Sofort durchführen:

1. **install.php löschen oder umbenennen**
   - Via FTP: `install.php` → `install.php.disabled`
   - ODER über SSH: `mv install.php install.php.disabled`

2. **.htaccess aktivieren**
   - Öffnen Sie `.htaccess`
   - Zeile 13-15: Entfernen Sie die `#` am Anfang

3. **Admin-Login testen**
   - Gehen Sie zu: `https://winter.wien/terminkalender/admin/`
   - Melden Sie sich an
   - ✓ Login erfolgreich?

4. **Passwort ändern**
   - Im Admin: Klicken Sie auf "Einstellungen"
   - Ändern Sie Ihr Passwort zu etwas Sicherem
   - ✓ Passwort geändert?

## ☐ Grundkonfiguration

### 1. Standorte bearbeiten

- Admin → "Standorte"
- Bearbeiten Sie "Ordination 1":
  - Name: `Ihre echte Bezeichnung`
  - Adresse: `Ihre echte Adresse`
- Bearbeiten Sie "Ordination 2" (oder deaktivieren Sie ihn)
- ✓ Adressen korrekt?

### 2. Verfügbarkeit festlegen

- Admin → "Verfügbarkeit"
- Fügen Sie Ihre erste Sprechzeit hinzu:
  - Standort: `wählen`
  - Wochentag: `z.B. Montag`
  - Startzeit: `z.B. 09:00`
  - Endzeit: `z.B. 12:00`
  - Termindauer: `z.B. 30 Minuten`
- Klicken Sie "Verfügbarkeit hinzufügen"
- Wiederholen Sie für alle Sprechzeiten
- ✓ Alle Sprechzeiten eingetragen?

### 3. Test-Buchung durchführen

1. Öffnen Sie: `https://winter.wien/terminkalender/`
2. Wählen Sie einen Standort
3. ✓ Werden verfügbare Termine angezeigt?
4. Klicken Sie auf einen Termin
5. Geben Sie Testdaten ein:
   - Name: `Test Patient`
   - Telefon: `0664 123 45 67`
   - Kommentar: `Test`
6. Klicken Sie "Termin bestätigen"
7. ✓ Erfolgreiche Buchung?
8. ✓ E-Mail erhalten an ordination@winter.wien?

### 4. Test-Termin im Admin prüfen

- Gehen Sie zu Admin → "Dashboard"
- ✓ Ist der Test-Termin sichtbar?
- Stornieren Sie den Test-Termin
- ✓ Termin erfolgreich storniert?

## ☐ Auf der Hauptseite verlinken

Fügen Sie auf winter.wien einen Link hinzu:
```html
<a href="terminkalender/">Termine online buchen</a>
```

## ☐ Finale Checks

- ✓ install.php gelöscht/deaktiviert?
- ✓ Admin-Passwort geändert?
- ✓ Echte Adressen eingetragen?
- ✓ Alle Sprechzeiten definiert?
- ✓ Test-Buchung funktioniert?
- ✓ E-Mail-Versand funktioniert?
- ✓ Link auf Hauptseite gesetzt?

## ☐ Empfohlene Sicherheitsmaßnahmen

1. **Backup erstellen**
   - Sichern Sie die Datenbank
   - Sichern Sie alle Dateien

2. **HTTPS aktivieren**
   - Falls noch nicht aktiv, bei world4you SSL-Zertifikat aktivieren
   - In `includes/config.php` Zeile 32: `1` setzen

3. **Regelmäßige Wartung**
   - Wöchentliches Datenbank-Backup
   - Monatliche Passwort-Änderung
   - Überprüfung der Verfügbarkeiten

---

## Probleme?

### E-Mails kommen nicht an
1. Überprüfen Sie SMTP-Passwort in `includes/config.php`
2. Testen Sie mit world4you Support: SMTP-Einstellungen korrekt?
3. Prüfen Sie Spam-Ordner

### Keine Termine sichtbar
1. Haben Sie Verfügbarkeiten definiert?
2. Ist der Standort aktiv?
3. Schauen Sie auf die richtige Woche?

### Kann mich nicht einloggen
1. Haben Sie das richtige Passwort?
2. Haben Sie Caps Lock aktiviert?
3. Zu viele Fehlversuche? Warten Sie 15 Minuten

---

**Nach erfolgreicher Installation:**
Lesen Sie das vollständige README.md für weitere Details und erweiterte Funktionen!

✅ **Installation abgeschlossen!**
