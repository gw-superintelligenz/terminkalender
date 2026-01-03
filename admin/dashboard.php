<?php
/**
 * Admin Dashboard
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();

// Get upcoming appointments
$stmt = $db->prepare("
    SELECT a.*, l.name as location_name, l.address as location_address
    FROM wp_terminkalender_appointments a
    JOIN wp_terminkalender_locations l ON a.location_id = l.id
    WHERE a.appointment_date >= CURDATE() AND a.status = 'confirmed'
    ORDER BY a.appointment_date, a.appointment_time
");
$stmt->execute();
$appointments = $stmt->fetchAll();

// Get statistics
$stmt = $db->query("SELECT COUNT(*) as total FROM wp_terminkalender_appointments WHERE status = 'confirmed' AND appointment_date >= CURDATE()");
$totalUpcoming = $stmt->fetch()['total'];

$stmt = $db->query("SELECT COUNT(*) as total FROM wp_terminkalender_appointments WHERE status = 'confirmed' AND appointment_date = CURDATE()");
$totalToday = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Terminkalender Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-content">
            <h1>Terminkalender Admin</h1>
            <div class="nav-links">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="availability.php">Verfügbarkeit</a>
                <a href="locations.php">Standorte</a>
                <a href="settings.php">Einstellungen</a>
                <a href="logout.php" class="logout-link">Abmelden</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <header class="admin-header">
            <h2>Dashboard</h2>
            <p>Willkommen, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
        </header>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalToday; ?></div>
                <div class="stat-label">Termine heute</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUpcoming; ?></div>
                <div class="stat-label">Zukünftige Termine</div>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="admin-section">
            <h3>Gebuchte Termine</h3>

            <?php if (empty($appointments)): ?>
                <p class="no-data">Keine zukünftigen Termine vorhanden.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Datum</th>
                                <th>Uhrzeit</th>
                                <th>Dauer</th>
                                <th>Name</th>
                                <th>Telefon</th>
                                <th>Standort</th>
                                <th>Kommentar</th>
                                <th>Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $apt): ?>
                                <tr data-appointment-id="<?php echo $apt['id']; ?>">
                                    <td><?php echo date('d.m.Y', strtotime($apt['appointment_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($apt['appointment_time'])); ?></td>
                                    <td><?php echo $apt['duration_minutes']; ?> Min.</td>
                                    <td><?php echo htmlspecialchars($apt['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['patient_phone']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($apt['location_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($apt['location_address']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        if (!empty($apt['patient_comment'])) {
                                            echo nl2br(htmlspecialchars($apt['patient_comment']));
                                        } else {
                                            echo '<em>-</em>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-small btn-danger cancel-appointment"
                                                data-id="<?php echo $apt['id']; ?>"
                                                data-date="<?php echo date('d.m.Y', strtotime($apt['appointment_date'])); ?>"
                                                data-time="<?php echo date('H:i', strtotime($apt['appointment_time'])); ?>">
                                            Stornieren
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="cancel-modal" class="modal">
        <div class="modal-content">
            <h3>Termin stornieren</h3>
            <p id="cancel-confirm-text"></p>
            <div class="form-actions">
                <button class="btn btn-secondary" id="cancel-no">Abbrechen</button>
                <button class="btn btn-danger" id="cancel-yes">Ja, stornieren</button>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
