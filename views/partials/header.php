<?php
$in_subfolder = (
    strpos($_SERVER['PHP_SELF'], '/admin/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/customer/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/views/') !== false
);
$in_deep_subfolder = (
    strpos($_SERVER['PHP_SELF'], '/views/admin/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/views/customer/') !== false || 
    strpos($_SERVER['PHP_SELF'], '/customer/actions/') !== false
);

if ($in_deep_subfolder) {
    $css_path = '../../assets/css/style.css';
    $cust_css_path = '../../assets/css/customer.css';
} elseif ($in_subfolder) {
    $css_path = '../assets/css/style.css';
    $cust_css_path = '../assets/css/customer.css';
} else {
    $css_path = 'assets/css/style.css';
    $cust_css_path = 'assets/css/customer.css';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FoodHub'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
    <link rel="stylesheet" href="<?php echo $cust_css_path; ?>">
</head>
<body>
