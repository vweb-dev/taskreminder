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
    <meta name="description" content="Register for Task Reminder - Manage your tasks and reminders">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Task Reminder">
    
    <title>Register - Task Reminder</title>
    
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
                    <i class="bi bi-person-plus"></i>
                </div>
                <h1>Create Account</h1>
                <p>Sign up to get started</p>
            </div>
            
            <form id="registerForm" class="auth-form">
                <div id="registerError" class="error-message" style="display: none;"></div>
                <div id="registerSuccess" class="success-message" style="display: none;"></div>
                
                <div class="form-group">
                    <label for="registerUsername" class="form-label">
                        <i class="bi bi-person"></i> Username <span class="required">*</span>
                    </label>
                    <input type="text" class="form-input" id="registerUsername" name="username" required autocomplete="username" placeholder="Choose a username" minlength="3" maxlength="50">
                    <small class="form-hint">3-50 characters, letters, numbers, and underscores only</small>
                </div>
                
                <div class="form-group">
                    <label for="registerEmail" class="form-label">
                        <i class="bi bi-envelope"></i> Email <span class="required">*</span>
                    </label>
                    <input type="email" class="form-input" id="registerEmail" name="email" required autocomplete="email" placeholder="Enter your email">
                </div>
                
                <div class="form-group">
                    <label for="registerFullName" class="form-label">
                        <i class="bi bi-card-text"></i> Full Name
                    </label>
                    <input type="text" class="form-input" id="registerFullName" name="full_name" autocomplete="name" placeholder="Enter your full name">
                </div>
                
                <div class="form-group">
                    <label for="registerPassword" class="form-label">
                        <i class="bi bi-lock"></i> Password <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-input" id="registerPassword" name="password" required autocomplete="new-password" placeholder="Create a password" minlength="6">
                        <button type="button" class="btn-toggle-password" id="toggleRegisterPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <small class="form-hint">Minimum 6 characters</small>
                </div>
                
                <div class="form-group">
                    <label for="registerConfirmPassword" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirm Password <span class="required">*</span>
                    </label>
                    <div class="password-input-wrapper">
                        <input type="password" class="form-input" id="registerConfirmPassword" name="confirm_password" required autocomplete="new-password" placeholder="Confirm password">
                        <button type="button" class="btn-toggle-password" id="toggleConfirmPassword">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" id="agreeTerms" name="agree_terms" required>
                        <span>I agree to the <a href="#" target="_blank">Terms & Conditions</a></span>
                    </label>
                </div>
                
                <button type="submit" class="btn-auth-submit" id="btnRegister">
                    <i class="bi bi-person-plus"></i> Create Account
                </button>
                
                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php">Sign in</a></p>
                </div>
            </form>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="assets/js/auth.js"></script>
</body>
</html>

