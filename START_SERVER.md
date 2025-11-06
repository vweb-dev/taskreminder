# WebSocket Server - Quick Start Guide

## ✅ Dependencies Installed
Composer dependencies successfully installed! Ratchet WebSocket library is ready.

## 🚀 Start WebSocket Server

### Method 1: Using Batch File (Easiest)
Double-click on:
```
websocket\start_server.bat
```

### Method 2: Using Command Line
```bash
php websocket/server.php
```

### Method 3: Background Process (Windows)
```powershell
Start-Process -NoNewWindow php -ArgumentList "websocket/server.php"
```

## ✅ Check Server Status

Run:
```bash
php websocket/status_check.php
```

Or check in browser console - you should see "WebSocket connected" message.

## 📋 Server Information

- **Port:** 8080
- **URL:** ws://localhost:8080
- **Status:** ✅ Running (check with status_check.php)

## 🔧 Troubleshooting

### Port 8080 Already in Use
If you get port error, change port in:
1. `websocket/server.php` (line with `8080`)
2. `assets/js/websocket.js` (line with `8080`)

### Server Not Starting
1. Check PHP version: `php -v` (needs PHP 7.4+)
2. Check if vendor folder exists
3. Check error logs

### Composer Commands
Since composer is not globally installed, use:
```bash
php composer.phar install
php composer.phar update
```

## 🎯 Current Status

✅ Composer installed locally
✅ Dependencies installed
✅ Server ready to start
✅ Status check script created

## 📝 Next Steps

1. Start server: `php websocket/server.php`
2. Keep server running (don't close terminal)
3. Open application in browser
4. Check console for "WebSocket connected" message

---

**Note:** Server must be running for real-time WebSocket notifications. WebSocket server is required for reminders to work.


