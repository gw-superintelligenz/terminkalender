<?php
/**
 * Locations Management
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
$stmt = $db->query("SELECT * FROM wp_terminkalender_locations ORDER BY name");
$locations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Standorte - Terminkalender Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-content">
            <h1>Terminkalender Admin</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="availability.php">Verfügbarkeit</a>
                <a href="locations.php" class="active">Standorte</a>
                <a href="settings.php">Einstellungen</a>
                <a href="logout.php" class="logout-link">Abmelden</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <header class="admin-header">
            <h2>Standorte verwalten</h2>
            <p>Verwalten Sie die Standorte Ihrer Ordinationen.</p>
        </header>

        <!-- Current Locations -->
        <div class="admin-section">
            <h3>Ihre Standorte</h3>

            <?php if (empty($locations)): ?>
                <p class="no-data">Noch keine Standorte definiert.</p>
            <?php else: ?>
                <div class="locations-grid">
                    <?php foreach ($locations as $loc): ?>
                        <div class="location-card" data-location-id="<?php echo $loc['id']; ?>">
                            <div class="location-header">
                                <h4><?php echo htmlspecialchars($loc['name']); ?></h4>
                                <span class="location-status <?php echo $loc['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $loc['is_active'] ? 'Aktiv' : 'Inaktiv'; ?>
                                </span>
                            </div>
                            <div class="location-body">
                                <p><strong>Adresse:</strong><br><?php echo nl2br(htmlspecialchars($loc['address'])); ?></p>
                            </div>
                            <div class="location-actions">
                                <button class="btn btn-small btn-secondary edit-location"
                                        data-id="<?php echo $loc['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($loc['name']); ?>"
                                        data-address="<?php echo htmlspecialchars($loc['address']); ?>"
                                        data-active="<?php echo $loc['is_active']; ?>">
                                    Bearbeiten
                                </button>
                                <button class="btn btn-small <?php echo $loc['is_active'] ? 'btn-warning' : 'btn-success'; ?> toggle-location"
                                        data-id="<?php echo $loc['id']; ?>"
                                        data-active="<?php echo $loc['is_active']; ?>">
                                    <?php echo $loc['is_active'] ? 'Deaktivieren' : 'Aktivieren'; ?>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Add New Location -->
        <div class="admin-section">
            <h3>Neuen Standort hinzufügen</h3>

            <form id="add-location-form" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="location-name">Name: *</label>
                    <input type="text" id="location-name" name="name" required maxlength="100" class="form-control" placeholder="z.B. Ordination Innere Stadt">
                </div>

                <div class="form-group">
                    <label for="location-address">Adresse: *</label>
                    <textarea id="location-address" name="address" required rows="3" class="form-control" placeholder="Vollständige Adresse eingeben"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Standort hinzufügen</button>
            </form>

            <div id="location-message" class="alert" style="display: none;"></div>
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div id="edit-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Standort bearbeiten</h3>

            <form id="edit-location-form" class="admin-form">
                <input type="hidden" id="edit-location-id" name="location_id">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="edit-name">Name: *</label>
                    <input type="text" id="edit-name" name="name" required maxlength="100" class="form-control">
                </div>

                <div class="form-group">
                    <label for="edit-address">Adresse: *</label>
                    <textarea id="edit-address" name="address" required rows="3" class="form-control"></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary close-modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Speichern</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/locations.js"></script>
</body>
</html>
