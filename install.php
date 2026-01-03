<?php
/**
 * Terminkalender Installation Script
 * Run this ONCE after uploading files to create database tables
 */

// Database configuration - EDIT THESE VALUES
define('DB_HOST', 'mysqlsvr75.world4you.com');
define('DB_NAME', '5355254db2');
define('DB_USER', 'sql3456525');
define('DB_PASS', 'vyh2r+*5');

// Admin user configuration - EDIT THESE VALUES
define('ADMIN_USERNAME', 'doctor');
define('ADMIN_PASSWORD', 'term01$Doctor');

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Installation</title>";
    echo "<style>body{font-family:Arial,sans-serif;max-width:800px;margin:50px auto;padding:20px;}";
    echo ".success{color:green;}.error{color:red;}.info{color:#227ac1;}</style></head><body>";
    echo "<h1>Terminkalender Installation</h1>";

    // Create admin table
    $sql = "CREATE TABLE IF NOT EXISTS `wp_terminkalender_admin` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX `idx_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

    $pdo->exec($sql);
    echo "<p class='success'>✓ Table 'wp_terminkalender_admin' created</p>";

    // Create locations table
    $sql = "CREATE TABLE IF NOT EXISTS `wp_terminkalender_locations` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `address` TEXT NOT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

    $pdo->exec($sql);
    echo "<p class='success'>✓ Table 'wp_terminkalender_locations' created</p>";

    // Create availability table
    $sql = "CREATE TABLE IF NOT EXISTS `wp_terminkalender_availability` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `location_id` INT UNSIGNED NOT NULL,
        `day_of_week` TINYINT(1) NOT NULL COMMENT '1=Monday, 7=Sunday',
        `start_time` TIME NOT NULL,
        `end_time` TIME NOT NULL,
        `duration_minutes` INT NOT NULL COMMENT '20,30,40,50,60',
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`location_id`) REFERENCES `wp_terminkalender_locations`(`id`) ON DELETE CASCADE,
        INDEX `idx_day_location` (`day_of_week`, `location_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

    $pdo->exec($sql);
    echo "<p class='success'>✓ Table 'wp_terminkalender_availability' created</p>";

    // Create exceptions table (manual overrides)
    $sql = "CREATE TABLE IF NOT EXISTS `wp_terminkalender_exceptions` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `location_id` INT UNSIGNED NOT NULL,
        `exception_date` DATE NOT NULL,
        `start_time` TIME DEFAULT NULL,
        `end_time` TIME DEFAULT NULL,
        `duration_minutes` INT DEFAULT NULL,
        `is_blocked` TINYINT(1) DEFAULT 0 COMMENT '1=unavailable, 0=available',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`location_id`) REFERENCES `wp_terminkalender_locations`(`id`) ON DELETE CASCADE,
        INDEX `idx_date_location` (`exception_date`, `location_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

    $pdo->exec($sql);
    echo "<p class='success'>✓ Table 'wp_terminkalender_exceptions' created</p>";

    // Create appointments table
    $sql = "CREATE TABLE IF NOT EXISTS `wp_terminkalender_appointments` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `location_id` INT UNSIGNED NOT NULL,
        `appointment_date` DATE NOT NULL,
        `appointment_time` TIME NOT NULL,
        `duration_minutes` INT NOT NULL,
        `patient_name` VARCHAR(100) NOT NULL,
        `patient_phone` VARCHAR(50) NOT NULL,
        `patient_comment` TEXT,
        `status` ENUM('confirmed', 'cancelled') DEFAULT 'confirmed',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`location_id`) REFERENCES `wp_terminkalender_locations`(`id`) ON DELETE CASCADE,
        INDEX `idx_datetime` (`appointment_date`, `appointment_time`),
        INDEX `idx_status` (`status`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci";

    $pdo->exec($sql);
    echo "<p class='success'>✓ Table 'wp_terminkalender_appointments' created</p>";

    // Insert admin user
    $hashedPassword = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO `wp_terminkalender_admin` (`username`, `password`) VALUES (?, ?)");
    $stmt->execute([ADMIN_USERNAME, $hashedPassword]);

    if ($stmt->rowCount() > 0) {
        echo "<p class='success'>✓ Admin user created</p>";
        echo "<p class='info'><strong>Username:</strong> " . htmlspecialchars(ADMIN_USERNAME) . "</p>";
        echo "<p class='info'><strong>Password:</strong> " . htmlspecialchars(ADMIN_PASSWORD) . "</p>";
        echo "<p class='error'><strong>⚠ IMPORTANT: Change your password after first login!</strong></p>";
    } else {
        echo "<p class='info'>ℹ Admin user already exists</p>";
    }

    // Insert default locations (you can modify these)
    $locations = [
        ['name' => 'Ordination 1', 'address' => 'Adresse 1 hier eingeben'],
        ['name' => 'Ordination 2', 'address' => 'Adresse 2 hier eingeben']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO `wp_terminkalender_locations` (`name`, `address`) VALUES (?, ?)");
    foreach ($locations as $loc) {
        $stmt->execute([$loc['name'], $loc['address']]);
    }
    echo "<p class='success'>✓ Default locations inserted (edit them in admin panel)</p>";

    echo "<hr><h2 class='success'>Installation Complete!</h2>";
    echo "<p><strong>Next steps:</strong></p>";
    echo "<ol>";
    echo "<li>Edit <code>includes/config.php</code> and add your SMTP password</li>";
    echo "<li>Delete or rename this <code>install.php</code> file for security</li>";
    echo "<li>Login to admin panel: <a href='admin/'>admin/</a></li>";
    echo "<li>Update the locations with your actual addresses</li>";
    echo "<li>Set up your availability schedule</li>";
    echo "</ol>";
    echo "</body></html>";

} catch (PDOException $e) {
    echo "<p class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Please check your database credentials in this file.</p>";
}
?>
