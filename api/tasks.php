<?php
/**
 * Tasks API
 * Handles task CRUD operations
 */

session_start();
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../includes/auth.php';

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

// Handle different actions
switch ($action) {
    case 'list':
        listTasks($userId);
        break;
    
    case 'get':
        getTask($userId);
        break;
    
    case 'create':
        createTask($userId);
        break;
    
    case 'update':
        updateTask($userId);
        break;
    
    case 'delete':
        deleteTask($userId);
        break;
    
    case 'update_status':
        updateTaskStatus($userId);
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        break;
}

/**
 * List all tasks for user
 */
function listTasks($userId) {
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
            SELECT t.*, 
                   COUNT(r.id) as reminder_count,
                   SUM(CASE WHEN r.is_sent = 0 AND r.reminder_time <= NOW() THEN 1 ELSE 0 END) as pending_reminders
            FROM tasks t
            LEFT JOIN reminders r ON t.id = r.task_id
            WHERE t.user_id = :user_id
            GROUP BY t.id
            ORDER BY t.created_at DESC
        ");
        $stmt->execute(['user_id' => $userId]);
        $tasks = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'tasks' => $tasks,
            'count' => count($tasks)
        ]);
        
    } catch (PDOException $e) {
        error_log("List tasks error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch tasks'
        ]);
    }
}

/**
 * Get single task
 */
function getTask($userId) {
    $taskId = $_GET['id'] ?? $_POST['id'] ?? 0;
    
    if (!$taskId) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID is required'
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
        $stmt = $db->prepare("
            SELECT t.*, 
                   COUNT(r.id) as reminder_count
            FROM tasks t
            LEFT JOIN reminders r ON t.id = r.task_id
            WHERE t.id = :task_id AND t.user_id = :user_id
            GROUP BY t.id
        ");
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId
        ]);
        $task = $stmt->fetch();
        
        if (!$task) {
            echo json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]);
            return;
        }
        
        // Get reminders for this task
        $reminderStmt = $db->prepare("
            SELECT * FROM reminders 
            WHERE task_id = :task_id 
            ORDER BY reminder_time ASC
        ");
        $reminderStmt->execute(['task_id' => $taskId]);
        $task['reminders'] = $reminderStmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'task' => $task
        ]);
        
    } catch (PDOException $e) {
        error_log("Get task error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch task'
        ]);
    }
}

/**
 * Create new task
 */
function createTask($userId) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'pending';
    $dueDate = $_POST['due_date'] ?? null;
    
    // Validation
    if (empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'Task title is required'
        ]);
        return;
    }
    
    if (!in_array($priority, ['low', 'medium', 'high', 'urgent'])) {
        $priority = 'medium';
    }
    
    if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
        $status = 'pending';
    }
    
    // Format due date
    if (!empty($dueDate)) {
        $dueDate = date('Y-m-d H:i:s', strtotime($dueDate));
    } else {
        $dueDate = null;
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
        // Insert task
        $stmt = $db->prepare("
            INSERT INTO tasks (user_id, title, description, priority, status, due_date) 
            VALUES (:user_id, :title, :description, :priority, :status, :due_date)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description ?: null,
            'priority' => $priority,
            'status' => $status,
            'due_date' => $dueDate
        ]);
        
        $taskId = $db->lastInsertId();
        
        // Add to history
        addTaskHistory($db, $taskId, 'created', null, json_encode([
            'title' => $title,
            'status' => $status,
            'priority' => $priority
        ]));
        
        // Handle reminders if provided
        $reminders = $_POST['reminders'] ?? [];
        if (is_array($reminders) && !empty($reminders)) {
            foreach ($reminders as $reminderTime) {
                if (!empty($reminderTime)) {
                    // Handle datetime-local format (YYYY-MM-DDTHH:mm)
                    // Convert to MySQL datetime format
                    $reminderTime = str_replace('T', ' ', $reminderTime) . ':00';
                    $reminderTime = date('Y-m-d H:i:s', strtotime($reminderTime));
                    
                    // Validate time is in future
                    if (strtotime($reminderTime) <= time()) {
                        // If time is in past, add 1 minute to current time
                        $reminderTime = date('Y-m-d H:i:s', strtotime('+1 minute'));
                    }
                    
                    $reminderStmt = $db->prepare("
                        INSERT INTO reminders (task_id, reminder_time, reminder_type, is_sent) 
                        VALUES (:task_id, :reminder_time, 'notification', 0)
                    ");
                    $reminderStmt->execute([
                        'task_id' => $taskId,
                        'reminder_time' => $reminderTime
                    ]);
                }
            }
        }
        
        // Get created task
        $getStmt = $db->prepare("SELECT * FROM tasks WHERE id = :task_id");
        $getStmt->execute(['task_id' => $taskId]);
        $task = $getStmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task
        ]);
        
    } catch (PDOException $e) {
        error_log("Create task error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create task'
        ]);
    }
}

/**
 * Update task
 */
function updateTask($userId) {
    $taskId = $_POST['task_id'] ?? $_POST['id'] ?? 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority = $_POST['priority'] ?? 'medium';
    $status = $_POST['status'] ?? 'pending';
    $dueDate = $_POST['due_date'] ?? null;
    
    if (!$taskId) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID is required'
        ]);
        return;
    }
    
    if (empty($title)) {
        echo json_encode([
            'success' => false,
            'message' => 'Task title is required'
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
        // Get old task data
        $oldStmt = $db->prepare("SELECT * FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $oldStmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        $oldTask = $oldStmt->fetch();
        
        if (!$oldTask) {
            echo json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]);
            return;
        }
        
        // Format due date
        if (!empty($dueDate)) {
            $dueDate = date('Y-m-d H:i:s', strtotime($dueDate));
        } else {
            $dueDate = null;
        }
        
        // Update task
        $stmt = $db->prepare("
            UPDATE tasks 
            SET title = :title, 
                description = :description, 
                priority = :priority, 
                status = :status, 
                due_date = :due_date,
                updated_at = NOW()
            WHERE id = :task_id AND user_id = :user_id
        ");
        
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
            'title' => $title,
            'description' => $description ?: null,
            'priority' => $priority,
            'status' => $status,
            'due_date' => $dueDate
        ]);
        
        // Add to history
        addTaskHistory($db, $taskId, 'updated', json_encode($oldTask), json_encode([
            'title' => $title,
            'status' => $status,
            'priority' => $priority,
            'due_date' => $dueDate
        ]));
        
        // Handle reminders update
        $reminders = $_POST['reminders'] ?? [];
        if (is_array($reminders)) {
            // Delete existing reminders
            $deleteRemindersStmt = $db->prepare("DELETE FROM reminders WHERE task_id = :task_id");
            $deleteRemindersStmt->execute(['task_id' => $taskId]);
            
            // Add new reminders
            foreach ($reminders as $reminderTime) {
                if (!empty($reminderTime)) {
                    // Handle datetime-local format (YYYY-MM-DDTHH:mm)
                    $reminderTime = str_replace('T', ' ', $reminderTime) . ':00';
                    $reminderTime = date('Y-m-d H:i:s', strtotime($reminderTime));
                    
                    // Validate time is in future
                    if (strtotime($reminderTime) <= time()) {
                        $reminderTime = date('Y-m-d H:i:s', strtotime('+1 minute'));
                    }
                    
                    $reminderStmt = $db->prepare("
                        INSERT INTO reminders (task_id, reminder_time, reminder_type, is_sent) 
                        VALUES (:task_id, :reminder_time, 'notification', 0)
                    ");
                    $reminderStmt->execute([
                        'task_id' => $taskId,
                        'reminder_time' => $reminderTime
                    ]);
                }
            }
        }
        
        // Get updated task
        $getStmt = $db->prepare("SELECT * FROM tasks WHERE id = :task_id");
        $getStmt->execute(['task_id' => $taskId]);
        $task = $getStmt->fetch();
        
        echo json_encode([
            'success' => true,
            'message' => 'Task updated successfully',
            'task' => $task
        ]);
        
    } catch (PDOException $e) {
        error_log("Update task error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update task'
        ]);
    }
}

/**
 * Delete task
 */
function deleteTask($userId) {
    $taskId = $_POST['id'] ?? $_GET['id'] ?? 0;
    
    if (!$taskId) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID is required'
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
        
        // Add to history before deletion
        $taskStmt = $db->prepare("SELECT * FROM tasks WHERE id = :task_id");
        $taskStmt->execute(['task_id' => $taskId]);
        $task = $taskStmt->fetch();
        
        addTaskHistory($db, $taskId, 'deleted', json_encode($task), null);
        
        // Delete task (reminders will be deleted via CASCADE)
        $stmt = $db->prepare("DELETE FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $stmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        
        echo json_encode([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Delete task error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete task'
        ]);
    }
}

/**
 * Update task status
 */
function updateTaskStatus($userId) {
    $taskId = $_POST['id'] ?? $_GET['id'] ?? 0;
    $status = $_POST['status'] ?? '';
    
    if (!$taskId || empty($status)) {
        echo json_encode([
            'success' => false,
            'message' => 'Task ID and status are required'
        ]);
        return;
    }
    
    if (!in_array($status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid status'
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
        // Get old status
        $oldStmt = $db->prepare("SELECT status FROM tasks WHERE id = :task_id AND user_id = :user_id");
        $oldStmt->execute(['task_id' => $taskId, 'user_id' => $userId]);
        $oldTask = $oldStmt->fetch();
        
        if (!$oldTask) {
            echo json_encode([
                'success' => false,
                'message' => 'Task not found'
            ]);
            return;
        }
        
        // Update status
        $stmt = $db->prepare("
            UPDATE tasks 
            SET status = :status, updated_at = NOW() 
            WHERE id = :task_id AND user_id = :user_id
        ");
        $stmt->execute([
            'task_id' => $taskId,
            'user_id' => $userId,
            'status' => $status
        ]);
        
        // Add to history
        addTaskHistory($db, $taskId, 'status_changed', $oldTask['status'], $status);
        
        echo json_encode([
            'success' => true,
            'message' => 'Task status updated successfully'
        ]);
        
    } catch (PDOException $e) {
        error_log("Update status error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to update task status'
        ]);
    }
}

/**
 * Add task history entry
 */
function addTaskHistory($db, $taskId, $action, $oldValue, $newValue) {
    try {
        $stmt = $db->prepare("
            INSERT INTO task_history (task_id, action, old_value, new_value) 
            VALUES (:task_id, :action, :old_value, :new_value)
        ");
        $stmt->execute([
            'task_id' => $taskId,
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue
        ]);
    } catch (PDOException $e) {
        error_log("Task history error: " . $e->getMessage());
        // Don't fail the main operation if history fails
    }
}


