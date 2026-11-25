<?php
/**
 * Legacy manager dashboard URL: the maintained one lives in manager/views/
 */
require_once __DIR__ . '/../../includes/auth_check.php';

check_auth(['Restaurant Manager']);
header("Location: " . get_user_dashboard_url());
exit();
