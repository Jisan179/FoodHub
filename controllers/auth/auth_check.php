<?php
/**
 * FoodHub - Procedural Auth Guard for Admin Sections
 */

require_once __DIR__ . '/../../includes/auth_check.php';

// Enforce Administrator role
check_auth(['Administrator', 'Admin']);
