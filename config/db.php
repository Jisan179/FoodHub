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

$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    die("Database Connection Error: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");
