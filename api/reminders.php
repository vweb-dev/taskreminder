<?php
/**
 * Reminders API
 * Handles reminder operations and due reminders check
 */

session_start();
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../includes/auth.php';

date_default_timezone_set('Asia/Karachi');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

$userId = getUserId();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'check_due':
        checkDueReminders($userId);
        break;
    
    case 'list':
        listReminders($userId);
        break;
    
    case 'create':
        createReminder($userId);
        break;
    
    case 'delete':
        deleteReminder($userId);
        break;
    
    case 'mark_read':
        markReminderAsRead($userId);
        break;
    
    case 'mark_all_read':
        markAllRemindersAsRead($userId);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

/**
 * Check for due reminders
 */
function checkDueReminders($userId) {
    $db = getDBConnection();
    if (!$db) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    try {
        $stmt = $db->prepare("
            SELECT r.*, t.title as task_title, t.description as task_description, t.priority
            FROM reminders r
            INNER JOIN tasks t ON r.task_id = t.id
            WHERE t.user_id = :user_id
            AND r.reminder_time <= NOW()
            AND (r.is_sent = 0 OR r.is_sent IS NULL)
            AND t.status != 'completed'
            AND t.status != 'cancelled'
            ORDER BY r.reminder_time ASC
            LIMIT 10
        ");
        
        $stmt->execute(['user_id' => $userId]);
        $reminders = $stmt->fetchAll();
        
        // Don't mark as sent immediately - let user see them first
        // Mark as sent only after user interacts
        
        echo json_encode([
            'success' => true,
            'reminders' => $reminders,
            'count' => count($reminders)
        ]);
        
    } catch (PDOException $e) {
        error_log("Check reminders error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to check reminders'
        ]);
    }
}

/**
 * List reminders for user
 */
function listReminders($userId) {
    $taskId = $_GET['task_id'] ?? 0;
    
    $db = getDBConnection();
    if (!$db) {
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
        return;
    }
    
    try {
        if ($taskId > 0) {
            // Get reminders for specific task
            $stmt = $db->prepare("
                SELECT r.* FROM reminders r
                INNER JOIN tasks t ON r.task_id = t.id
                WHERE r.task_id = :task_id AND t.user_id = :user_id
                ORDER BY r.reminder_time ASC
            ");
            $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        } else {
            // Get all reminders for user
            $stmt = $db->prepare("
                SELECT r.*, t.title as task_title
                FROM reminders r
                INNER JOIN tasks t ON r.task_id = t.id
                WHERE t.user_id = :user_id
                ORDER BY r.reminder_time ASC
            ");
            $stmt->execute(['user_id' => $userId]);
        }
        
        $reminders = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'reminders' => $reminders
        ]);
        
    } catch (PDOException $e) {
        error_log("List reminders error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch reminders'
        ]);
    }
}

/**
 * Create reminder
 */
function createReminder($userId) {
    $taskId = $_POST['task_id'] ?? 0;
    $reminderTime = $_POST['reminder_time'] ?? '';
    $reminderType = $_POST['reminder_type'] ?? 'notification';
    
    if (!$taskId || empty($reminderTime)) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID and reminder time are required'
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
        // Verify task belongs to user
        $checkStmt = $db->prepare("SELECT id FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $checkStmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]);
            return;
        }
        
        $reminderTime = date('Y-m-d H:i:s', strtotime($reminderTime));
        
        $stmt = $db->prepare("
            INSERT INTO reminders (task_id, reminder_time, reminder_type, is_sent) 
            VALUES (:task_id, :reminder_time, :reminder_type, 0)
        ");
        
        $stmt->execute([
            'task_id' => $taskId,
            'reminder_time' => $reminderTime,
            'reminder_type' => $reminderType
        ]);
        
        $reminderId = $db->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => 'Reminder created successfully',
            'reminder_id' => $reminderId
        ]);
        
    } catch (PDOException $e) {
        error_log("Create reminder error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create reminder'
        ]);
    }
}

/**
 * Delete reminder
 */
function deleteReminder($userId) {
    $reminderId = $_POST['id'] ?? $_GET['id'] ?? 0;
    
    if (!$reminderId) {
        echo json_encode([
            'success' => false,
            'message' => 'Reminder ID is required'
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
        // Verify reminder belongs to user's task
        $checkStmt = $db->prepare("
            SELECT r.id FROM reminders r
            INNER JOIN tasks t ON r.task_id = t.id
            WHERE r.id = :reminder_id AND t.user_id = :user_id
        ");
        $checkStmt->execute(['reminder_id' => $reminderId, 'user_id' => $userId]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Reminder not found'
            ]);
            return;
        }
        
        $stmt = $db->prepare("DELETE FROM reminders WHERE id = :reminder_id");
        $stmt->execute(['reminder_id' => $reminderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reminder deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete reminder error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete reminder'
        ]);
    }
}

/**
 * Mark reminder as read
 */
function markReminderAsRead($userId) {
    $reminderId = $_POST['id'] ?? $_GET['id'] ?? 0;
    
    if (!$reminderId) {
        echo json_encode([
            'success' => false,
            'message' => 'Reminder ID is required'
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
        // Verify reminder belongs to user's task
        $checkStmt = $db->prepare("
            SELECT r.id FROM reminders r
            INNER JOIN tasks t ON r.task_id = t.id
            WHERE r.id = :reminder_id AND t.user_id = :user_id
        ");
        $checkStmt->execute(['reminder_id' => $reminderId, 'user_id' => $userId]);
        
        if (!$checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Reminder not found'
            ]);
            return;
        }
        
        $stmt = $db->prepare("UPDATE reminders SET is_sent = 1 WHERE id = :reminder_id");
        $stmt->execute(['reminder_id' => $reminderId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Reminder marked as read'
        ]);
        
    } catch (PDOException $e) {
        error_log("Mark read error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark reminder as read'
        ]);
    }
}

/**
 * Mark all reminders as read
 */
function markAllRemindersAsRead($userId) {
    $ids = $_POST['ids'] ?? [];
    
    if (empty($ids) || !is_array($ids)) {
        echo json_encode([
            'success' => false,
            'message' => 'No reminder IDs provided'
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
        // Verify all reminders belong to user
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $checkStmt = $db->prepare("
            SELECT r.id FROM reminders r
            INNER JOIN tasks t ON r.task_id = t.id
            WHERE r.id IN ($placeholders) AND t.user_id = ?
        ");
        $checkStmt->execute(array_merge($ids, [$userId]));
        
        $validIds = array_column($checkStmt->fetchAll(), 'id');
        
        if (empty($validIds)) {
            echo json_encode([
                'success' => false,
                'message' => 'No valid reminders found'
            ]);
            return;
        }
        
        $updatePlaceholders = implode(',', array_fill(0, count($validIds), '?'));
        $stmt = $db->prepare("UPDATE reminders SET is_sent = 1 WHERE id IN ($updatePlaceholders)");
        $stmt->execute($validIds);
        
        echo json_encode([
            'success' => true,
            'message' => 'All reminders marked as read',
            'count' => count($validIds)
        ]);
        
    } catch (PDOException $e) {
        error_log("Mark all read error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to mark reminders as read'
        ]);
    }
}


