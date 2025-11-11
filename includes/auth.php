<?php
/**
 * Authentication Helper Functions
 * Use this file to check authentication status
 */

date_default_timezone_set('Asia/Karachi');

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Get current user ID
 * @return int|null
 */
function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user data
 * @return array|null
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'username' => $_SESSION['username'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'full_name' => $_SESSION['full_name'] ?? null
    ];
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Require logout - redirect if already logged in
 */
function requireLogout() {
    if (isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Logout user
 */
function logout() {
    $_SESSION = [];
    session_destroy();
    header('Location: login.php');
    exit;
}

