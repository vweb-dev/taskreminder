# WebSocket Server Setup

## Installation

1. Install Composer dependencies:
```bash
composer install
```

2. Start WebSocket server:
```bash
php websocket/server.php
```

Server will run on port 8080.

## Setup Reminder Checker

### Option 1: Cron Job (Recommended)
Add to crontab to run every minute:
```bash
* * * * * php /path/to/task-reminder/websocket/check_reminders.php
```

### Option 2: Task Scheduler (Windows)
Create a scheduled task that runs `check_reminders.php` every minute.

### Option 3: Background Process
Use a process manager like Supervisor or PM2 to keep the checker running.

## Testing

1. Start WebSocket server
2. Open application in browser
3. Create a task with reminder set to current time + 1 minute
4. Wait for reminder to trigger
5. Check browser console for WebSocket messages

## Troubleshooting

- Ensure port 8080 is not blocked by firewall
- Check PHP error logs
- Verify database connection
- Test WebSocket connection using browser console



