<?php
$in_subfolder = (strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/views/') !== false);
$css_path = $in_subfolder ? '../assets/css/style.css' : 'assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FoodHub - Admin Portal'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
</head>
<body>
