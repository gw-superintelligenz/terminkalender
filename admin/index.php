<?php
/**
 * Admin Login Page
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();

// If already logged in, redirect to dashboard
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$sessionExpired = isset($_GET['expired']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $auth->login($username, $password);

    if ($result['success']) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Terminkalender</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Admin Login</h1>
            <p class="subtitle">Terminkalender Verwaltung</p>

            <?php if ($sessionExpired): ?>
                <div class="alert alert-info">
                    Ihre Sitzung ist abgelaufen. Bitte melden Sie sich erneut an.
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Benutzername:</label>
                    <input type="text" id="username" name="username" required class="form-control" autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" required class="form-control">
                </div>

                <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
            </form>

            <div class="login-footer">
                <a href="../index.php">&larr; Zurück zur Terminbuchung</a>
            </div>
        </div>
    </div>
</body>
</html>
