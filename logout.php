<?php
/**
 * Logout Handler
 * Simple redirect to logout API
 */
date_default_timezone_set('Asia/Karachi');
session_start();
require_once 'includes/auth.php';
logout();

