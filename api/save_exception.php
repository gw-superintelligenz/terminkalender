<?php
/**
 * API: Save Exception (Admin Only)
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth = new Auth();
$auth->requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Verify CSRF token
if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'] ?? '';

try {
    $db = Database::getInstance()->getConnection();

    if ($action === 'add') {
        $locationId = $_POST['location_id'] ?? '';
        $exceptionDate = $_POST['exception_date'] ?? '';
        $exceptionType = $_POST['exception_type'] ?? '';

        if (empty($locationId) || empty($exceptionDate)) {
            echo json_encode(['success' => false, 'message' => 'Standort und Datum sind erforderlich.']);
            exit;
        }

        if ($exceptionType === 'block') {
            // Block the entire day
            $stmt = $db->prepare("
                INSERT INTO wp_terminkalender_exceptions
                (location_id, exception_date, is_blocked)
                VALUES (?, ?, 1)
                ON DUPLICATE KEY UPDATE is_blocked = 1
            ");
            $stmt->execute([$locationId, $exceptionDate]);

            echo json_encode(['success' => true, 'message' => 'Tag erfolgreich gesperrt.']);

        } elseif ($exceptionType === 'block_slots') {
            // Block specific time slots
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $duration = $_POST['duration_minutes'] ?? '';

            if (empty($startTime) || empty($endTime) || empty($duration)) {
                echo json_encode(['success' => false, 'message' => 'Alle Zeitfelder sind erforderlich.']);
                exit;
            }

            if (strtotime($startTime) >= strtotime($endTime)) {
                echo json_encode(['success' => false, 'message' => 'Die Startzeit muss vor der Endzeit liegen.']);
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO wp_terminkalender_exceptions
                (location_id, exception_date, start_time, end_time, duration_minutes, is_blocked)
                VALUES (?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE start_time = ?, end_time = ?, duration_minutes = ?, is_blocked = 1
            ");
            $stmt->execute([$locationId, $exceptionDate, $startTime, $endTime, $duration, $startTime, $endTime, $duration]);

            echo json_encode(['success' => true, 'message' => 'Zeitslots erfolgreich gesperrt.']);

        } elseif ($exceptionType === 'special') {
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $duration = $_POST['duration_minutes'] ?? '';

            if (empty($startTime) || empty($endTime) || empty($duration)) {
                echo json_encode(['success' => false, 'message' => 'Alle Zeitfelder sind erforderlich.']);
                exit;
            }

            if (strtotime($startTime) >= strtotime($endTime)) {
                echo json_encode(['success' => false, 'message' => 'Die Startzeit muss vor der Endzeit liegen.']);
                exit;
            }

            $stmt = $db->prepare("
                INSERT INTO wp_terminkalender_exceptions
                (location_id, exception_date, start_time, end_time, duration_minutes, is_blocked)
                VALUES (?, ?, ?, ?, ?, 0)
                ON DUPLICATE KEY UPDATE start_time = ?, end_time = ?, duration_minutes = ?, is_blocked = 0
            ");
            $stmt->execute([$locationId, $exceptionDate, $startTime, $endTime, $duration, $startTime, $endTime, $duration]);

            echo json_encode(['success' => true, 'message' => 'Spezielle Verfügbarkeit erfolgreich hinzugefügt.']);

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid exception type']);
        }

    } elseif ($action === 'delete') {
        $exceptionId = $_POST['exception_id'] ?? '';

        if (empty($exceptionId)) {
            echo json_encode(['success' => false, 'message' => 'Missing exception ID']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM wp_terminkalender_exceptions WHERE id = ?");
        $stmt->execute([$exceptionId]);

        echo json_encode(['success' => true, 'message' => 'Ausnahme erfolgreich gelöscht.']);

    } elseif ($action === 'list') {
        $locationId = $_POST['location_id'] ?? '';

        $sql = "SELECT * FROM wp_terminkalender_exceptions WHERE 1=1";
        $params = [];

        if (!empty($locationId)) {
            $sql .= " AND location_id = ?";
            $params[] = $locationId;
        }

        $sql .= " ORDER BY exception_date DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $exceptions = $stmt->fetchAll();

        echo json_encode(['success' => true, 'exceptions' => $exceptions]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('Error saving exception: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
