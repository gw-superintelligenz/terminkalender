<?php
/**
 * Email Test Script
 * DELETE THIS FILE after testing!
 */

define('TERMINKALENDER', true);
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>Email Test</h1>";

$testEmail = 'ordination@winter.wien';
$subject = 'Test Email from Terminkalender';
$body = '<html><body><h2>Test Email</h2><p>If you receive this, email is working!</p><p>Time: ' . date('Y-m-d H:i:s') . '</p></body></html>';

echo "<p>Attempting to send test email to: <strong>$testEmail</strong></p>";

$result = sendEmail($testEmail, $subject, $body);

if ($result) {
    echo "<p style='color: green;'><strong>✓ Email sent successfully!</strong></p>";
    echo "<p>Check your inbox at: $testEmail</p>";
    echo "<p><em>Note: It may take a few minutes to arrive, or check your spam folder.</em></p>";
} else {
    echo "<p style='color: red;'><strong>✗ Email failed to send.</strong></p>";
    echo "<p>Possible issues:</p>";
    echo "<ul>";
    echo "<li>world4you mail() function may be disabled</li>";
    echo "<li>Email address (ordination@winter.wien) may not be configured properly</li>";
    echo "<li>Check world4you hosting settings for email configuration</li>";
    echo "</ul>";

    echo "<h3>Alternative: Contact world4you Support</h3>";
    echo "<p>Ask them:</p>";
    echo "<ol>";
    echo "<li>Is PHP mail() function enabled for my hosting?</li>";
    echo "<li>Do I need special SMTP settings?</li>";
    echo "<li>Is ordination@winter.wien properly configured?</li>";
    echo "</ol>";
}

echo "<hr>";
echo "<p><strong>⚠️ IMPORTANT: Delete this test-email.php file after testing!</strong></p>";
?>
