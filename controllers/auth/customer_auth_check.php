<?php
/**
 * FoodHub - Procedural Auth Guard for Customer Module
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../includes/auth_check.php';

// Enforce Customer role
check_auth(['Customer']);
