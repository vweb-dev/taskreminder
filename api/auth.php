<?php
/**
 * Authentication API
 * Handles user login and registration
 */

date_default_timezone_set('Asia/Karachi');

session_start();
header('Content-Type: application/json');

require_once '../config/config.php';

// Get action from request
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Handle different actions
switch ($action) {
    case 'login':
        handleLogin();
        break;
    
    case 'register':
        handleRegister();
        break;
    
    case 'logout':
        handleLogout();
        break;
    
    case 'check':
        checkAuth();
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

/**
 * Handle user login
 */
function handleLogin() {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember_me']) && $_POST['remember_me'] == 1;
    
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        return;
    }
    
    $db = getDBConnection();
    if (!$db) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    try {
        // Check if username or email exists
        $stmt = $db->prepare("
            SELECT id, username, email, password, full_name, is_active 
            FROM users 
            WHERE (username = :username OR email = :email_check) AND is_active = 1
        ");
        $stmt->execute([
            'username' => $username,
            'email_check' => $username
        ]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
            return;
        }
        
        // Debug: Log password hash info (remove in production)
        error_log("Login attempt - User found: " . $user['username']);
        error_log("Password hash length: " . strlen($user['password']));
        error_log("Password hash starts with: " . substr($user['password'], 0, 10));
        
        // Verify password
        $passwordVerified = password_verify($password, $user['password']);
        error_log("Password verification result: " . ($passwordVerified ? 'true' : 'false'));
        
        if (!$passwordVerified) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid username or password'
            ]);
            return;
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['logged_in'] = true;
        
        // Update last login
        $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
        $updateStmt->execute(['id' => $user['id']]);
        
        // Set remember me cookie (optional - for future use)
        if ($remember) {
            // Could implement remember me token here
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'full_name' => $user['full_name']
            ]
        ]);
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        error_log("Login error code: " . $e->getCode());
        error_log("Login error trace: " . $e->getTraceAsString());
        
        // Check for specific database errors
        $errorCode = $e->getCode();
        $errorMessage = $e->getMessage();
        
        if (strpos($errorMessage, "Unknown database") !== false) {
            $message = 'Database not found. Please run database/schema.sql to create the database.';
        } elseif (strpos($errorMessage, "Table") !== false && strpos($errorMessage, "doesn't exist") !== false) {
            $message = 'Database tables not found. Please import database/schema.sql file.';
        } elseif (strpos($errorMessage, "SQLSTATE[HY000]") !== false) {
            $message = 'Database connection error. Please check MySQL is running and database exists.';
        } else {
            // For debugging - show actual error (remove in production)
            $message = 'Database error: ' . $errorMessage;
        }
        
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
}

/**
 * Handle user registration
 */
function handleRegister() {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username, email, and password are required'
        ]);
        return;
    }
    
    if (strlen($username) < 3 || strlen($username) > 50) {
        echo json_encode([
            'success' => false,
            'message' => 'Username must be between 3 and 50 characters'
        ]);
        return;
    }
    
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username can only contain letters, numbers, and underscores'
        ]);
        return;
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address'
        ]);
        return;
    }
    
    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters'
        ]);
        return;
    }
    
    $db = getDBConnection();
    if (!$db) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    try {
        // Check if username already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Username already exists'
            ]);
            return;
        }
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Email already registered'
            ]);
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password, full_name, is_active) 
            VALUES (:username, :email, :password, :full_name, 1)
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'full_name' => $full_name ?: null
        ]);
        
        $userId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Account created successfully! Please login.',
            'user_id' => $userId
        ]);
        
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        
        // Check for duplicate entry
        if ($e->getCode() == 23000) {
            echo json_encode([
                'success' => false,
                'message' => 'Username or email already exists'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'An error occurred. Please try again.'
            ]);
        }
    }
}

/**
 * Handle user logout
 */
function handleLogout() {
    session_destroy();
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}

/**
 * Check if user is authenticated
 */
function checkAuth() {
    if (isset($_SESSION['user_id']) && $_SESSION['logged_in']) {
        echo json_encode([
            'success' => true,
            'authenticated' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'username' => $_SESSION['username'] ?? '',
                'email' => $_SESSION['email'] ?? '',
                'full_name' => $_SESSION['full_name'] ?? ''
            ]
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'authenticated' => false
        ]);
    }
}

