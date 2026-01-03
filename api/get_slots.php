<?php
/**
 * API: Get Available Slots
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$date = $_GET['date'] ?? '';
$locationId = $_GET['location_id'] ?? '';

// Validate inputs
if (empty($date) || empty($locationId)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

// Check if date is in the past
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Date cannot be in the past']);
    exit;
}

// Check if date is too far in the future
$maxDate = date('Y-m-d', strtotime('+' . MONTHS_IN_ADVANCE . ' months'));
if (strtotime($date) > strtotime($maxDate)) {
    echo json_encode(['success' => false, 'message' => 'Date too far in the future']);
    exit;
}

try {
    $slots = getAvailableSlots($date, $locationId);

    echo json_encode([
        'success' => true,
        'date' => $date,
        'slots' => $slots
    ]);
} catch (Exception $e) {
    error_log('Error getting slots: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>
