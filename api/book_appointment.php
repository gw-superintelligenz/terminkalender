<?php
/**
 * API: Book Appointment
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$locationId = $_POST['location_id'] ?? '';
$appointmentDate = $_POST['appointment_date'] ?? '';
$appointmentTime = $_POST['appointment_time'] ?? '';
$duration = $_POST['duration'] ?? '';
$patientName = sanitizeInput($_POST['patient_name'] ?? '');
$patientPhone = sanitizeInput($_POST['patient_phone'] ?? '');
$patientComment = sanitizeInput($_POST['patient_comment'] ?? '');

// Validate required fields
if (empty($locationId) || empty($appointmentDate) || empty($appointmentTime) || empty($duration) || empty($patientName) || empty($patientPhone)) {
    echo json_encode(['success' => false, 'message' => 'Alle Pflichtfelder müssen ausgefüllt werden.']);
    exit;
}

// Validate phone number
if (!validatePhone($patientPhone)) {
    echo json_encode(['success' => false, 'message' => 'Ungültige Telefonnummer. Bitte verwenden Sie ein gültiges österreichisches Format.']);
    exit;
}

// Validate date
if (strtotime($appointmentDate) < strtotime(date('Y-m-d'))) {
    echo json_encode(['success' => false, 'message' => 'Das Datum darf nicht in der Vergangenheit liegen.']);
    exit;
}

try {
    $db = Database::getInstance()->getConnection();

    // Double-check slot is still available
    $stmt = $db->prepare("
        SELECT id FROM wp_terminkalender_appointments
        WHERE appointment_date = ?
        AND appointment_time = ?
        AND location_id = ?
        AND status = 'confirmed'
    ");
    $stmt->execute([$appointmentDate, $appointmentTime, $locationId]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Dieser Termin wurde bereits gebucht. Bitte wählen Sie einen anderen Zeitpunkt.']);
        exit;
    }

    // Get location info for email
    $stmt = $db->prepare("SELECT name, address FROM wp_terminkalender_locations WHERE id = ?");
    $stmt->execute([$locationId]);
    $location = $stmt->fetch();

    if (!$location) {
        echo json_encode(['success' => false, 'message' => 'Ungültiger Standort.']);
        exit;
    }

    // Insert appointment
    $stmt = $db->prepare("
        INSERT INTO wp_terminkalender_appointments
        (location_id, appointment_date, appointment_time, duration_minutes, patient_name, patient_phone, patient_comment, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'confirmed')
    ");

    $stmt->execute([
        $locationId,
        $appointmentDate,
        $appointmentTime,
        $duration,
        $patientName,
        $patientPhone,
        $patientComment
    ]);

    // Send confirmation email
    $emailData = [
        'name' => $patientName,
        'phone' => $patientPhone,
        'date' => formatDateDE($appointmentDate),
        'time' => date('H:i', strtotime($appointmentTime)),
        'duration' => $duration,
        'location' => $location['name'] . "\n" . $location['address'],
        'comment' => $patientComment
    ];

    sendAppointmentEmail($emailData);

    echo json_encode([
        'success' => true,
        'message' => 'Termin erfolgreich gebucht.'
    ]);

} catch (Exception $e) {
    error_log('Error booking appointment: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.']);
}
?>
