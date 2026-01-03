<?php
/**
 * API: Save Location (Admin Only)
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
        $name = sanitizeInput($_POST['name'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');

        if (empty($name) || empty($address)) {
            echo json_encode(['success' => false, 'message' => 'Name und Adresse sind erforderlich.']);
            exit;
        }

        $stmt = $db->prepare("INSERT INTO wp_terminkalender_locations (name, address) VALUES (?, ?)");
        $stmt->execute([$name, $address]);

        echo json_encode(['success' => true, 'message' => 'Standort erfolgreich hinzugefügt.']);

    } elseif ($action === 'edit') {
        $locationId = $_POST['location_id'] ?? '';
        $name = sanitizeInput($_POST['name'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');

        if (empty($locationId) || empty($name) || empty($address)) {
            echo json_encode(['success' => false, 'message' => 'Alle Felder sind erforderlich.']);
            exit;
        }

        $stmt = $db->prepare("UPDATE wp_terminkalender_locations SET name = ?, address = ? WHERE id = ?");
        $stmt->execute([$name, $address, $locationId]);

        echo json_encode(['success' => true, 'message' => 'Standort erfolgreich aktualisiert.']);

    } elseif ($action === 'toggle') {
        $locationId = $_POST['location_id'] ?? '';
        $isActive = $_POST['is_active'] ?? '';

        if (empty($locationId)) {
            echo json_encode(['success' => false, 'message' => 'Missing location ID']);
            exit;
        }

        // Toggle the current status
        $newStatus = ($isActive == '1') ? 0 : 1;

        $stmt = $db->prepare("UPDATE wp_terminkalender_locations SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $locationId]);

        echo json_encode([
            'success' => true,
            'message' => $newStatus ? 'Standort aktiviert.' : 'Standort deaktiviert.',
            'new_status' => $newStatus
        ]);

    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }

} catch (Exception $e) {
    error_log('Error saving location: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
