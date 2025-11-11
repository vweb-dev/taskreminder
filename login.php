<?php
date_default_timezone_set('Asia/Karachi');
session_start();
// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="Login to Task Reminder - Manage your tasks and reminders">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Task Reminder">
    
    <title>Login - Task Reminder</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/icons/icon-96x96.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-icon">
                    <i class="bi bi-list-check"></i>
                </div>
                <h1>Task Reminder</h1>
                <p>Sign in to your account</p>
            </div>
            
            <form id="loginForm" class="auth-form">
                <div id="loginError" class="error-message" style="display: none;"></div>
                
                <div class="form-group">
                    <label for="loginUsername" class="form-label">
                        <i class="bi bi-person"></i> Username or Email
                    </label>
                    <input type="text" class="form-input" id="loginUsername" name="username" required autocomplete="username" placeholder="Enter username or email">
                </div>
                
                <div class="form-group">
                    <label for="loginPassword" class="form-label">
                        <i class="bi bi-lock"></i> Password
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-input" id="loginPassword" name="password" required autocomplete="current-password" placeholder="Enter password">
                        <button type="button" class="btn-toggle-password" id="toggleLoginPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="rememberMe" name="remember_me">
                        <span>Remember me</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-auth-submit" id="btnLogin">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
                
                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php">Sign up</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>

