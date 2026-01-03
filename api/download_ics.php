<?php
/**
 * Generate ICS Calendar File for Appointment
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';

// Get appointment data from URL parameters
$date = $_GET['date'] ?? '';
$time = $_GET['time'] ?? '';
$duration = $_GET['duration'] ?? '';
$location = $_GET['location'] ?? '';
$name = $_GET['name'] ?? '';

if (empty($date) || empty($time) || empty($duration)) {
    die('Missing required parameters');
}

// Parse date and time
$datetime = new DateTime($date . ' ' . $time, new DateTimeZone('Europe/Vienna'));
$endtime = clone $datetime;
$endtime->modify('+' . $duration . ' minutes');

// Format for ICS (UTC)
$datetime->setTimezone(new DateTimeZone('UTC'));
$endtime->setTimezone(new DateTimeZone('UTC'));

$dtstart = $datetime->format('Ymd\THis\Z');
$dtend = $endtime->format('Ymd\THis\Z');
$dtstamp = gmdate('Ymd\THis\Z');

// Generate unique ID
$uid = md5($date . $time . $name) . '@winter.wien';

// Clean location text
$location = str_replace(["\r\n", "\n", "\r"], ' ', $location);
$location = preg_replace('/\s+/', ' ', $location);

// Create ICS content
$ics = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "PRODID:-//DDr. Fabian Winter//Terminkalender//DE\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "METHOD:PUBLISH\r\n";
$ics .= "X-WR-CALNAME:Arzttermin DDr. Winter\r\n";
$ics .= "X-WR-TIMEZONE:Europe/Vienna\r\n";
$ics .= "BEGIN:VEVENT\r\n";
$ics .= "UID:" . $uid . "\r\n";
$ics .= "DTSTAMP:" . $dtstamp . "\r\n";
$ics .= "DTSTART:" . $dtstart . "\r\n";
$ics .= "DTEND:" . $dtend . "\r\n";
$ics .= "SUMMARY:Arzttermin - DDr. Fabian Winter\r\n";
$ics .= "DESCRIPTION:Termin bei DDr. Fabian Winter\\n\\nBei Verhinderung bitte kontaktieren:\\nordination@winter.wien\\n0664 133 15 62\r\n";
$ics .= "LOCATION:" . $location . "\r\n";
$ics .= "STATUS:CONFIRMED\r\n";
$ics .= "SEQUENCE:0\r\n";
$ics .= "BEGIN:VALARM\r\n";
$ics .= "TRIGGER:-PT24H\r\n";
$ics .= "ACTION:DISPLAY\r\n";
$ics .= "DESCRIPTION:Erinnerung: Arzttermin morgen bei DDr. Winter\r\n";
$ics .= "END:VALARM\r\n";
$ics .= "END:VEVENT\r\n";
$ics .= "END:VCALENDAR\r\n";

// Set headers for download
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="Arzttermin_DDr_Winter.ics"');
header('Content-Length: ' . strlen($ics));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

echo $ics;
?>
