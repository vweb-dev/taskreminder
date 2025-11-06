<?php
/**
 * WebSocket Server for Task Reminders
 * Uses Ratchet WebSocket library
 * 
 * Install Ratchet: composer require cboden/ratchet
 * Run: php websocket/server.php
 */

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\LoopInterface;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/config.php';

class ReminderServer implements MessageComponentInterface {
    protected $clients;
    protected $users;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->users = [];
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);
        
        if (!$data) {
            return;
        }

        $action = $data['action'] ?? '';

        switch ($action) {
            case 'subscribe':
                // Subscribe user to reminders
                $userId = $data['user_id'] ?? 0;
                if ($userId > 0) {
                    $this->users[$from->resourceId] = $userId;
                    echo "User {$userId} subscribed ({$from->resourceId})\n";
                    
                    // Check for due reminders immediately after subscription
                    $this->checkAndSendDueReminders($userId, $from);
                    
                    // Send confirmation
                    $from->send(json_encode([
                        'type' => 'subscribed',
                        'user_id' => $userId,
                        'message' => 'Subscribed to reminders'
                    ]));
                }
                break;

            case 'ping':
                // Keep connection alive
                $from->send(json_encode([
                    'type' => 'pong',
                    'timestamp' => time()
                ]));
                break;

            default:
                echo "Unknown action: {$action}\n";
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        if (isset($this->users[$conn->resourceId])) {
            unset($this->users[$conn->resourceId]);
        }
        echo "Connection closed! ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }

    /**
     * Send reminder to specific user
     */
    public function sendReminder($userId, $reminderData) {
        $message = json_encode([
            'type' => 'reminder',
            'data' => $reminderData
        ]);

        foreach ($this->clients as $client) {
            if (isset($this->users[$client->resourceId]) && 
                $this->users[$client->resourceId] == $userId) {
                $client->send($message);
            }
        }
    }

    /**
     * Broadcast to all connected clients
     */
    public function broadcast($message) {
        foreach ($this->clients as $client) {
            $client->send($message);
        }
    }

    /**
     * Check and send due reminders for a user
     */
    private function checkAndSendDueReminders($userId, $client) {
        try {
            $db = getDBConnection();
            
            if (!$db) {
                return;
            }
            
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
            
            foreach ($reminders as $reminder) {
                $message = json_encode([
                    'type' => 'reminder',
                    'data' => $reminder
                ]);
                
                $client->send($message);
                
                // Mark as sent
                $updateStmt = $db->prepare("UPDATE reminders SET is_sent = 1 WHERE id = :id");
                $updateStmt->execute(['id' => $reminder['id']]);
                
                echo "Sent reminder #{$reminder['id']} to user {$userId}\n";
            }
            
        } catch (Exception $e) {
            echo "Error checking reminders: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Check all due reminders for all connected users
     */
    public function checkAllDueReminders() {
        try {
            $db = getDBConnection();
            
            if (!$db) {
                return;
            }
            
            // Get all due reminders for all connected users
            $userIds = array_unique(array_values($this->users));
            
            if (empty($userIds)) {
                return;
            }
            
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            $stmt = $db->prepare("
                SELECT r.*, t.title as task_title, t.description as task_description, t.priority, t.user_id
                FROM reminders r
                INNER JOIN tasks t ON r.task_id = t.id
                WHERE t.user_id IN ($placeholders)
                AND r.reminder_time <= NOW()
                AND (r.is_sent = 0 OR r.is_sent IS NULL)
                AND t.status != 'completed'
                AND t.status != 'cancelled'
                ORDER BY r.reminder_time ASC
            ");
            
            $stmt->execute($userIds);
            $reminders = $stmt->fetchAll();
            
            foreach ($reminders as $reminder) {
                $this->sendReminder($reminder['user_id'], $reminder);
                
                // Mark as sent
                $updateStmt = $db->prepare("UPDATE reminders SET is_sent = 1 WHERE id = :id");
                $updateStmt->execute(['id' => $reminder['id']]);
            }
            
            if (!empty($reminders)) {
                echo "Checked and sent " . count($reminders) . " reminder(s)\n";
            }
            
        } catch (Exception $e) {
            echo "Error checking all reminders: " . $e->getMessage() . "\n";
        }
    }
}

// Create WebSocket server
$reminderServer = new ReminderServer();

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            $reminderServer
        )
    ),
    8080,
    '0.0.0.0'
);

// Get the event loop and add periodic timer to check reminders
$loop = $server->loop;

// Check for due reminders every 30 seconds for faster notifications
$loop->addPeriodicTimer(30, function () use ($reminderServer) {
    $reminderServer->checkAllDueReminders();
});

echo "WebSocket server started on port 8080\n";
echo "Server will check for reminders every 30 seconds\n";
echo "Waiting for connections...\n";
$server->run();


