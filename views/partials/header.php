<?php
$in_rider_folder = strpos($_SERVER['PHP_SELF'], '/rider/') !== false;
$css_path = $in_rider_folder ? '../style.css' : 'assets/css/style.css';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'FoodHub - Rider Portal'); ?></title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>">
</head>
<body>
