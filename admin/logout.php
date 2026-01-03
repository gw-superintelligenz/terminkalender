<?php
/**
 * Admin Logout
 */

define('TERMINKALENDER', true);
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';

$auth = new Auth();
$auth->logout();

header('Location: index.php');
exit;
?>
