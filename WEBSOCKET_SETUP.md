# WebSocket Setup Guide

## Option 1: Full WebSocket Setup (Recommended)

### Prerequisites
1. PHP 7.4 or higher
2. Composer installed
3. PHP extensions: `ext-sockets`, `ext-pcntl`

### Installation Steps

1. **Install Composer Dependencies:**
```bash
composer install
```

2. **Start WebSocket Server:**
```bash
php websocket/server.php
```

Or on Windows:
```bash
websocket\start_server.bat
```

Server will run on `ws://localhost:8080`

3. **Setup Reminder Checker (Cron/Task Scheduler):**

**Windows Task Scheduler:**
- Create a new task
- Trigger: Every 1 minute
- Action: Start a program
- Program: `php`
- Arguments: `D:\xampp\htdocs\task-reminder\websocket\check_reminders.php`

**Linux/Mac Cron:**
```bash
* * * * * php /path/to/task-reminder/websocket/check_reminders.php
```

## Testing WebSocket Connection

1. Open browser console
2. Check for "WebSocket connected" message
3. If you see "WebSocket connection failed", server is not running
4. **Important:** WebSocket server must be running to receive reminders

## Troubleshooting

### WebSocket Connection Failed
- **Solution:** Server is not running. Start it with `php websocket/server.php`
- **Required:** WebSocket server must be running for real-time reminders

### Port 8080 Already in Use
- Change port in `websocket/server.php` (line with `8080`)
- Update `assets/js/websocket.js` (line with `8080`)

### Composer Not Installed
- Install Composer from https://getcomposer.org/
- Required for WebSocket server functionality

### Reminders Not Working
- **Ensure WebSocket server is running:** `php websocket/server.php`
- Server automatically checks for reminders every 30 seconds
- Check database for reminders with `is_sent = 0`
- Verify WebSocket connection in browser console

## Current Status

The application uses **WebSocket only** for real-time notifications:
- ✅ **WebSocket Required:** Real-time instant notifications via WebSocket
- ⚠️ **Server Must Run:** WebSocket server must be running for reminders to work



