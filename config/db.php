<?php
/**
 * FoodHub Database Configuration
 * Pure Procedural MySQLi Connection
 */

$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "foodhub_db";
$db_port = 3306;

// Establish database connection
$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    // If foodhub_db is not yet created or connection failed, display a helpful styled error
    $error_msg = mysqli_connect_error();
    die("
        <div style='font-family: system-ui, sans-serif; max-width: 600px; margin: 50px auto; padding: 24px; border: 1px solid #fed7aa; background: #fff7ed; border-radius: 8px; color: #9a3412;'>
            <h2 style='margin-top: 0; color: #c2410c;'>⚠️ FoodHub Database Connection Error</h2>
            <p>Could not connect to MySQL server at <strong>{$db_host}:{$db_port}</strong> (Database: <em>{$db_name}</em>).</p>
            <p style='background: #ffedd5; padding: 10px; border-radius: 4px; font-family: monospace;'>{$error_msg}</p>
            <p><strong>Next steps:</strong> Please ensure your MySQL server (e.g. XAMPP Apache & MySQL) is running and import <code>database.sql</code> into phpMyAdmin or MySQL CLI.</p>
        </div>
    ");
}

mysqli_set_charset($conn, "utf8mb4");
