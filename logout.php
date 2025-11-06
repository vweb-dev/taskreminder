<?php
/**
 * Logout Handler
 * Simple redirect to logout API
 */
session_start();
require_once 'includes/auth.php';
logout();

