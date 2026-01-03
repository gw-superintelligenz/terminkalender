<?php
/**
 * Helper Functions
 */

if (!defined('TERMINKALENDER')) {
    die('Direct access not permitted');
}

/**
 * Send email via SMTP
 */
function sendEmail($to, $subject, $body) {
    try {
        // world4you compatible email headers
        $headers = [
            'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>',
            'Reply-To: ' . SMTP_FROM,
            'Return-Path: ' . SMTP_FROM,
            'X-Mailer: PHP/' . phpversion(),
            'MIME-Version: 1.0',
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit'
        ];

        // Additional parameters for world4you
        $additional_params = '-f' . SMTP_FROM;

        $success = mail($to, $subject, $body, implode("\r\n", $headers), $additional_params);

        if (!$success) {
            error_log("Failed to send email to: $to - Subject: $subject");
        }

        return $success;
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send appointment confirmation email
 */
function sendAppointmentEmail($appointmentData) {
    $subject = "Neue Terminbuchung - " . $appointmentData['date'] . " " . $appointmentData['time'];

    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background-color: " . BRAND_COLOR . "; color: white; padding: 20px; }
            .content { padding: 20px; }
            .info-table { border-collapse: collapse; width: 100%; }
            .info-table td { padding: 8px; border-bottom: 1px solid #ddd; }
            .info-table td:first-child { font-weight: bold; width: 150px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>Neue Terminbuchung</h2>
        </div>
        <div class='content'>
            <table class='info-table'>
                <tr>
                    <td>Name:</td>
                    <td>" . htmlspecialchars($appointmentData['name']) . "</td>
                </tr>
                <tr>
                    <td>Telefonnummer:</td>
                    <td>" . htmlspecialchars($appointmentData['phone']) . "</td>
                </tr>
                <tr>
                    <td>Datum:</td>
                    <td>" . htmlspecialchars($appointmentData['date']) . "</td>
                </tr>
                <tr>
                    <td>Uhrzeit:</td>
                    <td>" . htmlspecialchars($appointmentData['time']) . "</td>
                </tr>
                <tr>
                    <td>Dauer:</td>
                    <td>" . htmlspecialchars($appointmentData['duration']) . " Minuten</td>
                </tr>
                <tr>
                    <td>Standort:</td>
                    <td>" . htmlspecialchars($appointmentData['location']) . "</td>
                </tr>";

    if (!empty($appointmentData['comment'])) {
        $body .= "
                <tr>
                    <td>Kommentar:</td>
                    <td>" . nl2br(htmlspecialchars($appointmentData['comment'])) . "</td>
                </tr>";
    }

    $body .= "
            </table>
        </div>
    </body>
    </html>";

    return sendEmail(SMTP_TO, $subject, $body);
}

/**
 * Clean old appointments (older than 30 days)
 */
function cleanOldAppointments() {
    $db = Database::getInstance()->getConnection();

    $cutoffDate = date('Y-m-d', strtotime('-' . DELETE_OLD_APPOINTMENTS_DAYS . ' days'));

    $stmt = $db->prepare("DELETE FROM wp_terminkalender_appointments WHERE appointment_date < ?");
    $stmt->execute([$cutoffDate]);

    return $stmt->rowCount();
}

/**
 * Sanitize input
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate phone number (Austrian format)
 */
function validatePhone($phone) {
    // Remove spaces and common separators
    $phone = preg_replace('/[\s\-\(\)\/]/', '', $phone);

    // Check if it's a valid Austrian phone number
    // Accepts formats like: 0664..., +43664..., 01..., etc.
    if (preg_match('/^(\+43|0043|0)[1-9][0-9]{3,12}$/', $phone)) {
        return true;
    }

    return false;
}

/**
 * Format date for display (German format)
 */
function formatDateDE($date) {
    $timestamp = strtotime($date);
    $dayNames = ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'];
    $monthNames = ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni',
                   'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'];

    $dayName = $dayNames[date('w', $timestamp)];
    $day = date('d', $timestamp);
    $month = $monthNames[date('n', $timestamp) - 1];
    $year = date('Y', $timestamp);

    return "$dayName, $day. $month $year";
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get available time slots for a specific date
 */
function getAvailableSlots($date, $locationId) {
    $db = Database::getInstance()->getConnection();

    $dayOfWeek = date('N', strtotime($date)); // 1 = Monday, 7 = Sunday

    // Get ALL exceptions for this date
    $stmt = $db->prepare("
        SELECT * FROM wp_terminkalender_exceptions
        WHERE exception_date = ? AND location_id = ?
    ");
    $stmt->execute([$date, $locationId]);
    $exceptions = $stmt->fetchAll();

    // Check if entire day is blocked
    foreach ($exceptions as $exception) {
        if ($exception['is_blocked'] == 1 && empty($exception['start_time']) && empty($exception['end_time'])) {
            // Whole day is blocked
            return [];
        }
    }

    // Collect available time ranges
    $availabilities = [];

    // Check for special availability exceptions (is_blocked = 0)
    $hasSpecialAvailability = false;
    foreach ($exceptions as $exception) {
        if ($exception['is_blocked'] == 0 && !empty($exception['start_time']) && !empty($exception['end_time'])) {
            $availabilities[] = [
                'start_time' => $exception['start_time'],
                'end_time' => $exception['end_time'],
                'duration_minutes' => $exception['duration_minutes']
            ];
            $hasSpecialAvailability = true;
        }
    }

    // If no special availability, use regular weekly availability
    if (!$hasSpecialAvailability) {
        $stmt = $db->prepare("
            SELECT start_time, end_time, duration_minutes
            FROM wp_terminkalender_availability
            WHERE day_of_week = ? AND location_id = ? AND is_active = 1
        ");
        $stmt->execute([$dayOfWeek, $locationId]);
        $availabilities = $stmt->fetchAll();
    }

    if (empty($availabilities)) {
        return [];
    }

    // Generate time slots
    $slots = [];
    foreach ($availabilities as $avail) {
        $start = strtotime($avail['start_time']);
        $end = strtotime($avail['end_time']);
        $duration = $avail['duration_minutes'] * 60; // Convert to seconds

        $current = $start;
        while ($current < $end) {
            $slotTime = date('H:i:s', $current);
            $slotBlocked = false;

            // Check if this specific time slot is blocked by an exception
            foreach ($exceptions as $exception) {
                if ($exception['is_blocked'] == 1 && !empty($exception['start_time']) && !empty($exception['end_time'])) {
                    $blockStart = strtotime($exception['start_time']);
                    $blockEnd = strtotime($exception['end_time']);
                    $slotTimestamp = strtotime($slotTime);

                    if ($slotTimestamp >= $blockStart && $slotTimestamp < $blockEnd) {
                        $slotBlocked = true;
                        break;
                    }
                }
            }

            if (!$slotBlocked) {
                // Check if slot is already booked
                $stmt = $db->prepare("
                    SELECT id FROM wp_terminkalender_appointments
                    WHERE appointment_date = ?
                    AND appointment_time = ?
                    AND location_id = ?
                    AND status = 'confirmed'
                ");
                $stmt->execute([$date, $slotTime, $locationId]);

                if (!$stmt->fetch()) {
                    // Slot is available
                    $slots[] = [
                        'time' => $slotTime,
                        'display' => date('H:i', $current),
                        'duration' => $avail['duration_minutes']
                    ];
                }
            }

            $current += $duration;
        }
    }

    return $slots;
}
?>
