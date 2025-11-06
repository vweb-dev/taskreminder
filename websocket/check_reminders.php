<?php
/**
 * Reminder Checker Script
 * This script should be run via cron job every minute
 * Or use a task scheduler to check for due reminders
 * 
 * Usage: php websocket/check_reminders.php
 */

require __DIR__ . '/../config/config.php';

// WebSocket server URL
$wsServer = 'tcp://127.0.0.1:8080';

function checkReminders() {
    $db = getDBConnection();
    if (!$db) {
        echo "Database connection failed\n";
        return;
    }

    try {
        // Get reminders that are due and not sent
        $stmt = $db->prepare("
            SELECT r.*, t.title as task_title, t.user_id, t.description as task_description
            FROM reminders r
            INNER JOIN tasks t ON r.task_id = t.id
            WHERE r.reminder_time <= NOW() 
            AND r.is_sent = 0
            AND t.status != 'completed'
            AND t.status != 'cancelled'
            ORDER BY r.reminder_time ASC
        ");
        
        $stmt->execute();
        $reminders = $stmt->fetchAll();

        if (empty($reminders)) {
            echo "No reminders due\n";
            return;
        }

        echo "Found " . count($reminders) . " reminder(s) due\n";

        // Connect to WebSocket server and send reminders
        foreach ($reminders as $reminder) {
            sendReminderViaWebSocket($reminder);
            
            // Mark reminder as sent
            $updateStmt = $db->prepare("UPDATE reminders SET is_sent = 1 WHERE id = :id");
            $updateStmt->execute(['id' => $reminder['id']]);
            
            echo "Sent reminder #{$reminder['id']} to user {$reminder['user_id']}\n";
        }

    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

function sendReminderViaWebSocket($reminder) {
    // Use ZeroMQ or simple socket connection to send message to WebSocket server
    // For simplicity, we'll use a simple HTTP endpoint or direct socket
    
    $socket = @fsockopen('127.0.0.1', 8080, $errno, $errstr, 1);
    
    if (!$socket) {
        // Fallback: Use a message queue or HTTP endpoint
        // For now, we'll log it and the WebSocket server can poll the database
        error_log("Reminder due: User {$reminder['user_id']}, Task: {$reminder['task_title']}");
        return false;
    }
    
    // Send reminder data (this would need proper WebSocket protocol handling)
    fclose($socket);
    
    // Alternative: Use HTTP endpoint to notify WebSocket server
    $url = 'http://localhost/task-reminder/api/notify_reminder.php';
    $data = [
        'user_id' => $reminder['user_id'],
        'reminder' => $reminder
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
    
    return true;
}

// Run reminder checker
checkReminders();



