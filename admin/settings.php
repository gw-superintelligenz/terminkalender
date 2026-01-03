<?php
/**
 * Admin Settings (Change Password)
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

$db = Database::getInstance()->getConnection();
$message = '';
$messageType = '';

// Get current user info
$stmt = $db->prepare("SELECT username FROM wp_terminkalender_admin WHERE id = ?");
$stmt->execute([$_SESSION['admin_id']]);
$currentUser = $stmt->fetch();

// Handle username change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_username'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Ungültige Anfrage.';
        $messageType = 'error';
    } else {
        $newUsername = sanitizeInput($_POST['new_username'] ?? '');

        if (empty($newUsername)) {
            $message = 'Benutzername darf nicht leer sein.';
            $messageType = 'error';
        } elseif (strlen($newUsername) < 3) {
            $message = 'Benutzername muss mindestens 3 Zeichen lang sein.';
            $messageType = 'error';
        } else {
            // Check if username already exists (for another user)
            $stmt = $db->prepare("SELECT id FROM wp_terminkalender_admin WHERE username = ? AND id != ?");
            $stmt->execute([$newUsername, $_SESSION['admin_id']]);

            if ($stmt->fetch()) {
                $message = 'Dieser Benutzername ist bereits vergeben.';
                $messageType = 'error';
            } else {
                // Update username
                $stmt = $db->prepare("UPDATE wp_terminkalender_admin SET username = ? WHERE id = ?");
                $stmt->execute([$newUsername, $_SESSION['admin_id']]);

                $_SESSION['admin_username'] = $newUsername;
                $currentUser['username'] = $newUsername;

                $message = 'Benutzername erfolgreich geändert.';
                $messageType = 'success';
            }
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = 'Ungültige Anfrage.';
        $messageType = 'error';
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Get current user password
        $stmt = $db->prepare("SELECT password FROM wp_terminkalender_admin WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();

        if (!password_verify($currentPassword, $user['password'])) {
            $message = 'Das aktuelle Passwort ist falsch.';
            $messageType = 'error';
        } elseif ($newPassword !== $confirmPassword) {
            $message = 'Die neuen Passwörter stimmen nicht überein.';
            $messageType = 'error';
        } elseif (strlen($newPassword) < 8) {
            $message = 'Das neue Passwort muss mindestens 8 Zeichen lang sein.';
            $messageType = 'error';
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE wp_terminkalender_admin SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $_SESSION['admin_id']]);

            $message = 'Passwort erfolgreich geändert.';
            $messageType = 'success';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Einstellungen - Terminkalender Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="admin-body">
    <nav class="admin-nav">
        <div class="nav-content">
            <h1>Terminkalender Admin</h1>
            <div class="nav-links">
                <a href="dashboard.php">Dashboard</a>
                <a href="availability.php">Verfügbarkeit</a>
                <a href="locations.php">Standorte</a>
                <a href="settings.php" class="active">Einstellungen</a>
                <a href="logout.php" class="logout-link">Abmelden</a>
            </div>
        </div>
    </nav>

    <div class="admin-container">
        <header class="admin-header">
            <h2>Einstellungen</h2>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Change Username -->
        <div class="admin-section">
            <h3>Benutzername ändern</h3>

            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="change_username" value="1">

                <div class="form-group">
                    <label for="current_username">Aktueller Benutzername:</label>
                    <input type="text" id="current_username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" disabled class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_username">Neuer Benutzername: *</label>
                    <input type="text" id="new_username" name="new_username" required minlength="3" maxlength="50" class="form-control" value="<?php echo htmlspecialchars($currentUser['username']); ?>">
                    <small>Mindestens 3 Zeichen</small>
                </div>

                <button type="submit" class="btn btn-primary">Benutzername ändern</button>
            </form>
        </div>

        <!-- Change Password -->
        <div class="admin-section">
            <h3>Passwort ändern</h3>

            <form method="POST" action="" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="change_password" value="1">

                <div class="form-group">
                    <label for="current_password">Aktuelles Passwort: *</label>
                    <input type="password" id="current_password" name="current_password" required class="form-control">
                </div>

                <div class="form-group">
                    <label for="new_password">Neues Passwort: *</label>
                    <input type="password" id="new_password" name="new_password" required minlength="8" class="form-control">
                    <small>Mindestens 8 Zeichen</small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Neues Passwort bestätigen: *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">Passwort ändern</button>
            </form>
        </div>

        <!-- System Information -->
        <div class="admin-section">
            <h3>Systeminformationen</h3>
            <table class="info-table">
                <tr>
                    <td><strong>Benutzername:</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['admin_username']); ?></td>
                </tr>
                <tr>
                    <td><strong>PHP Version:</strong></td>
                    <td><?php echo phpversion(); ?></td>
                </tr>
                <tr>
                    <td><strong>Zeitzone:</strong></td>
                    <td><?php echo date_default_timezone_get(); ?></td>
                </tr>
                <tr>
                    <td><strong>Aktuelle Zeit:</strong></td>
                    <td><?php echo date('d.m.Y H:i:s'); ?></td>
                </tr>
            </table>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
