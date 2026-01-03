<?php
/**
 * API: Cancel Appointment (Admin Only)
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

$appointmentId = $_POST['appointment_id'] ?? '';

if (empty($appointmentId)) {
    echo json_encode(['success' => false, 'message' => 'Missing appointment ID']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Update appointment status to cancelled
    $stmt = $db->prepare("UPDATE wp_terminkalender_appointments SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$appointmentId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Termin erfolgreich storniert.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Termin nicht gefunden.']);
    }

} catch (Exception $e) {
    error_log('Error cancelling appointment: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
