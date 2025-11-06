<?php
/**
 * Notification Endpoint for WebSocket Server
 * Used by reminder checker to notify WebSocket server
 */

header('Content-Type: application/json');

// Simple file-based message queue (in production, use Redis or proper message queue)
$messageQueueFile = __DIR__ . '/../websocket/message_queue.json';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data) {
        // Add to message queue
        $queue = [];
        if (file_exists($messageQueueFile)) {
            $queue = json_decode(file_get_contents($messageQueueFile), true) ?: [];
        }
        
        $queue[] = [
            'timestamp' => time(),
            'user_id' => $data['user_id'] ?? 0,
            'reminder' => $data['reminder'] ?? []
        ];
        
        file_put_contents($messageQueueFile, json_encode($queue));
        
        echo json_encode(['success' => true, 'message' => 'Notification queued']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
} else {
    // Get queued messages (for WebSocket server to poll)
    $queue = [];
    if (file_exists($messageQueueFile)) {
        $queue = json_decode(file_get_contents($messageQueueFile), true) ?: [];
        // Clear queue after reading
        file_put_contents($messageQueueFile, json_encode([]));
    }
    
    echo json_encode(['success' => true, 'queue' => $queue]);
}



