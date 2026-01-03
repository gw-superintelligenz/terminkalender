<?php
/**
 * API: Save Availability (Admin Only)
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
        $dayOfWeek = $_POST['day_of_week'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';
        $duration = $_POST['duration_minutes'] ?? '';

        if (empty($locationId) || empty($dayOfWeek) || empty($startTime) || empty($endTime) || empty($duration)) {
            echo json_encode(['success' => false, 'message' => 'Alle Felder sind erforderlich.']);
            exit;
        }

        // Validate times
        if (strtotime($startTime) >= strtotime($endTime)) {
            echo json_encode(['success' => false, 'message' => 'Die Startzeit muss vor der Endzeit liegen.']);
            exit;
        }

        $stmt = $db->prepare("
            INSERT INTO wp_terminkalender_availability
            (location_id, day_of_week, start_time, end_time, duration_minutes)
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([$locationId, $dayOfWeek, $startTime, $endTime, $duration]);

        echo json_encode(['success' => true, 'message' => 'Verfügbarkeit erfolgreich hinzugefügt.']);

    } elseif ($action === 'delete') {
        $ruleId = $_POST['rule_id'] ?? '';

        if (empty($ruleId)) {
            echo json_encode(['success' => false, 'message' => 'Missing rule ID']);
            exit;
        }

        $stmt = $db->prepare("DELETE FROM wp_terminkalender_availability WHERE id = ?");
        $stmt->execute([$ruleId]);

        echo json_encode(['success' => true, 'message' => 'Verfügbarkeit erfolgreich gelöscht.']);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('Error saving availability: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
