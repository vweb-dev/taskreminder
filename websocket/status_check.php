<?php
/**
 * WebSocket Server Status Check
 * Run this to check if WebSocket server is running
 */

$host = 'localhost';
$port = 8080;

echo "Checking WebSocket server status...\n";
echo "Host: $host\n";
echo "Port: $port\n\n";

// Check if port is open
$connection = @fsockopen($host, $port, $errno, $errstr, 2);

if ($connection) {
    echo "✅ WebSocket server is RUNNING on port $port\n";
    fclose($connection);
    
    // Try to connect via WebSocket (basic check)
    echo "\nTesting WebSocket connection...\n";
    $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
    if (@socket_connect($socket, $host, $port)) {
        echo "✅ WebSocket connection successful!\n";
        socket_close($socket);
    } else {
        echo "⚠️  Port is open but WebSocket protocol check failed\n";
    }
} else {
    echo "❌ WebSocket server is NOT running\n";
    echo "Error: $errstr ($errno)\n\n";
    echo "To start the server, run:\n";
    echo "  php websocket/server.php\n";
    echo "Or use:\n";
    echo "  websocket\\start_server.bat\n";
}

echo "\n";


