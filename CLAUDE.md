# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Running the Project

Open `index.php` directly in a browser, or use a local server:

```bash
# Python 3
python -m http.server 8000

# Node.js
npx http-server

# PHP built-in server (recommended)
php -S localhost:8000
```

Then navigate to `http://localhost:8000`

### Testing Email

Use `test-email.php` to verify SMTP configuration is working.

## Architecture Overview

Terminkalender is a German-language online appointment booking system for medical practices. It separates patient-facing and administrator interfaces.

### Technology Stack

- **Backend:** PHP 8.3+ with MySQL 8.0+
- **Frontend:** Vanilla JavaScript (no frameworks)
- **Styling:** Pure CSS with CSS custom properties
- **Database:** PDO with singleton pattern, prepared statements

### Core Data Flow

**Patient Booking Flow:**
1. Patient selects location → `calendar.js` loads week view
2. For each date, `api/get_slots.php` returns available time slots
3. Slot availability is computed from weekly rules minus exceptions minus booked appointments
4. Patient books via `api/book_appointment.php` → confirmation email sent

**Admin Flow:**
1. Session-based auth with brute-force protection (5 attempts, 15-min lockout)
2. Weekly availability rules stored in `wp_terminkalender_availability`
3. Date exceptions (holidays, closures) stored in `wp_terminkalender_exceptions`
4. Appointments auto-deleted after 30 days (GDPR compliance)

### Key Components

| Directory | Purpose |
|-----------|---------|
| `includes/` | Core PHP: `config.php` (settings), `db.php` (singleton), `auth.php` (login), `functions.php` (helpers) |
| `admin/` | Admin pages: dashboard, availability, locations, settings |
| `api/` | AJAX endpoints: `get_slots`, `book_appointment`, `cancel_appointment`, `save_*` |
| `assets/js/` | Frontend modules: `calendar.js` (patient), `admin.js`, `availability.js`, `locations.js` |

### Database Tables

- `wp_terminkalender_admin` - Admin credentials (hashed passwords)
- `wp_terminkalender_locations` - Clinic locations with active flag
- `wp_terminkalender_availability` - Weekly rules (day, start/end time, duration)
- `wp_terminkalender_exceptions` - Date-specific overrides or blocks
- `wp_terminkalender_appointments` - Booked appointments with status

### Slot Generation Logic

The `getAvailableSlots($date, $locationId)` function in `functions.php`:
1. Gets weekly availability for that day-of-week
2. Checks for exceptions that override/block the day
3. Generates time slots based on duration (20-60 min)
4. Filters out already-booked slots
5. Returns available slots array

### Security Features

- CSRF tokens on all forms (`generateCSRFToken()`, `verifyCSRFToken()`)
- All includes check `defined('TERMINKALENDER')` to prevent direct access
- Session timeout: 30 minutes inactivity
- Austrian phone validation in `validatePhone()`

### Configuration

Key settings in `includes/config.php`:
- `MONTHS_IN_ADVANCE` - Booking window (default: 6)
- `DELETE_OLD_APPOINTMENTS_DAYS` - Auto-delete threshold (default: 30)
- `MAX_LOGIN_ATTEMPTS`, `LOGIN_LOCKOUT_TIME` - Brute-force protection
