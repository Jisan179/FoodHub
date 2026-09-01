<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$script_name = $_SERVER['PHP_SELF'] ?? '';
$in_actions_folder = (strpos($script_name, '/actions/') !== false);
$in_subfolder = (strpos($script_name, '/admin/') !== false || strpos($script_name, '/customer/') !== false || strpos($script_name, '/views/') !== false);

if ($in_actions_folder) {
    $base_prefix = '../../';
} elseif ($in_subfolder) {
    $base_prefix = '../';
} else {
    $base_prefix = '';
}

$css_path = $base_prefix . 'assets/css/style.css';
$cust_css_path = $base_prefix . 'assets/css/customer.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FoodHub - Online Food Ordering'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
    <link rel="stylesheet" href="<?php echo $cust_css_path; ?>">
</head>
<body>
