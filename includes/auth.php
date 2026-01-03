<?php
/**
 * Authentication Class
 */

if (!defined('TERMINKALENDER')) {
    die('Direct access not permitted');
}

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Login user
     */
    public function login($username, $password) {
        // Check for too many failed attempts
        if ($this->isLockedOut()) {
            return ['success' => false, 'message' => 'Zu viele fehlgeschlagene Versuche. Bitte warten Sie 15 Minuten.'];
        }

        $stmt = $this->db->prepare("SELECT id, username, password FROM wp_terminkalender_admin WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['last_activity'] = time();

            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            // Clear failed attempts
            unset($_SESSION['login_attempts']);
            unset($_SESSION['last_attempt_time']);

            return ['success' => true];
        } else {
            // Failed login
            $this->recordFailedAttempt();
            return ['success' => false, 'message' => 'Ungültiger Benutzername oder Passwort.'];
        }
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            return false;
        }

        // Check session timeout (30 minutes)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    /**
     * Logout user
     */
    public function logout() {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();
    }

    /**
     * Record failed login attempt
     */
    private function recordFailedAttempt() {
        if (!isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts'] = 0;
        }
        $_SESSION['login_attempts']++;
        $_SESSION['last_attempt_time'] = time();
    }

    /**
     * Check if user is locked out
     */
    private function isLockedOut() {
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= MAX_LOGIN_ATTEMPTS) {
            if (time() - $_SESSION['last_attempt_time'] < LOGIN_LOCKOUT_TIME) {
                return true;
            } else {
                // Lockout period expired, reset
                unset($_SESSION['login_attempts']);
                unset($_SESSION['last_attempt_time']);
            }
        }
        return false;
    }

    /**
     * Require login (redirect if not logged in)
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: index.php?expired=1');
            exit;
        }
    }
}
?>
