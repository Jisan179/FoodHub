<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth_check.php';

$root_url = get_foodhub_root_path();
$css_path = $root_url . 'assets/css/style.css';
$cust_css_path = $root_url . 'assets/css/customer.css';
$mgr_css_path = $root_url . 'assets/css/manager.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FoodHub - Online Food Ordering'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
    <link rel="stylesheet" href="<?php echo $cust_css_path; ?>">
    <link rel="stylesheet" href="<?php echo $mgr_css_path; ?>">
</head>
<body>
