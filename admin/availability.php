<?php
/**
 * Availability Management
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();

// Get all locations
$stmt = $db->query("SELECT id, name FROM wp_terminkalender_locations WHERE is_active = 1 ORDER BY name");
$locations = $stmt->fetchAll();

// Get current availability rules
$stmt = $db->query("
    SELECT a.*, l.name as location_name
    FROM wp_terminkalender_availability a
    JOIN wp_terminkalender_locations l ON a.location_id = l.id
    WHERE a.is_active = 1
    ORDER BY l.name, a.day_of_week, a.start_time
");
$availabilityRules = $stmt->fetchAll();

$dayNames = [
    1 => 'Montag',
    2 => 'Dienstag',
    3 => 'Mittwoch',
    4 => 'Donnerstag',
    5 => 'Freitag',
    6 => 'Samstag',
    7 => 'Sonntag'
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verfügbarkeit - Terminkalender Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-content">
            <h1>Terminkalender Admin</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="availability.php" class="active">Verfügbarkeit</a>
                <a href="locations.php">Standorte</a>
                <a href="settings.php">Einstellungen</a>
                <a href="logout.php" class="logout-link">Abmelden</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <header class="admin-header">
            <h2>Verfügbarkeit verwalten</h2>
            <p>Legen Sie Ihre wöchentliche Verfügbarkeit fest und definieren Sie Ausnahmen für bestimmte Tage.</p>
        </header>

        <!-- Add New Availability Rule -->
        <div class="admin-section">
            <h3>Neue Verfügbarkeit hinzufügen</h3>

            <form id="add-availability-form" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="location">Standort: *</label>
                        <select id="location" name="location_id" required class="form-control">
                            <option value="">Bitte wählen</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="day_of_week">Wochentag: *</label>
                        <select id="day_of_week" name="day_of_week" required class="form-control">
                            <option value="">Bitte wählen</option>
                            <?php foreach ($dayNames as $num => $name): ?>
                                <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Startzeit: *</label>
                        <input type="time" id="start_time" name="start_time" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="end_time">Endzeit: *</label>
                        <input type="time" id="end_time" name="end_time" required class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="duration">Termindauer: *</label>
                        <select id="duration" name="duration_minutes" required class="form-control">
                            <option value="20">20 Minuten</option>
                            <option value="30" selected>30 Minuten</option>
                            <option value="40">40 Minuten</option>
                            <option value="50">50 Minuten</option>
                            <option value="60">60 Minuten</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Verfügbarkeit hinzufügen</button>
            </form>

            <div id="availability-message" class="alert" style="display: none;"></div>
        </div>

        <!-- Current Availability Rules -->
        <div class="admin-section">
            <h3>Aktuelle Verfügbarkeiten</h3>

            <?php if (empty($availabilityRules)): ?>
                <p class="no-data">Noch keine Verfügbarkeiten definiert.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Standort</th>
                                <th>Wochentag</th>
                                <th>Zeitraum</th>
                                <th>Termindauer</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($availabilityRules as $rule): ?>
                                <tr data-rule-id="<?php echo $rule['id']; ?>">
                                    <td><?php echo htmlspecialchars($rule['location_name']); ?></td>
                                    <td><?php echo $dayNames[$rule['day_of_week']]; ?></td>
                                    <td>
                                        <?php echo date('H:i', strtotime($rule['start_time'])); ?> -
                                        <?php echo date('H:i', strtotime($rule['end_time'])); ?>
                                    </td>
                                    <td><?php echo $rule['duration_minutes']; ?> Min.</td>
                                    <td>
                                        <button class="btn btn-small btn-danger delete-rule"
                                                data-id="<?php echo $rule['id']; ?>">
                                            Löschen
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Exceptions (Block specific dates or add special availability) -->
        <div class="admin-section">
            <h3>Ausnahmen (Spezielle Tage)</h3>
            <p class="section-description">Hier können Sie einzelne Tage komplett sperren, spezielle Verfügbarkeiten festlegen oder bestimmte Zeitslots blockieren.</p>

            <form id="add-exception-form" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="exception-location">Standort: *</label>
                        <select id="exception-location" name="location_id" required class="form-control">
                            <option value="">Bitte wählen</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?php echo $loc['id']; ?>"><?php echo htmlspecialchars($loc['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="exception-date">Datum: *</label>
                        <input type="date" id="exception-date" name="exception_date" required class="form-control"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="exception-type">Typ: *</label>
                        <select id="exception-type" name="exception_type" required class="form-control">
                            <option value="block">Ganzen Tag sperren</option>
                            <option value="block_slots">Bestimmte Zeitslots sperren</option>
                            <option value="special">Spezielle Verfügbarkeit hinzufügen</option>
                        </select>
                    </div>
                </div>

                <div id="special-times" style="display: none;">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="exception-start">Startzeit:</label>
                            <input type="time" id="exception-start" name="start_time" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="exception-end">Endzeit:</label>
                            <input type="time" id="exception-end" name="end_time" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="exception-duration">Termindauer:</label>
                            <select id="exception-duration" name="duration_minutes" class="form-control">
                                <option value="20">20 Minuten</option>
                                <option value="30" selected>30 Minuten</option>
                                <option value="40">40 Minuten</option>
                                <option value="50">50 Minuten</option>
                                <option value="60">60 Minuten</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Ausnahme hinzufügen</button>
            </form>

            <div id="exception-message" class="alert" style="display: none;"></div>

            <!-- List existing exceptions -->
            <div id="exceptions-list" style="margin-top: 20px;">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/availability.js?v=3"></script>
</body>
</html>
