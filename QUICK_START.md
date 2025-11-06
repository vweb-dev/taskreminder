# 🚀 Quick Start - WebSocket Server

## ✅ Setup Complete!

1. ✅ Composer installed locally (`composer.phar`)
2. ✅ Dependencies installed (Ratchet WebSocket library)
3. ✅ Server files ready

## 🎯 Start Server (Choose One Method)

### Method 1: Batch File (Easiest) ⭐
```
Double-click: websocket\start_server.bat
```

### Method 2: PowerShell
```powershell
.\websocket\start_server.ps1
```

### Method 3: Command Line
```bash
php websocket/server.php
```

## ✅ Check Server Status

```bash
php websocket/status_check.php
```

Should show: ✅ WebSocket server is RUNNING

## 📝 Important Notes

1. **Keep Server Running:** Server terminal window open rakhni hai (required)
2. **Port 8080:** Server port 8080 par chalega
3. **WebSocket Required:** Reminders ke liye WebSocket server zaroori hai

## 🔍 Verify in Browser

1. Open application: `http://localhost/task-reminder/`
2. Open browser console (F12)
3. Look for: "WebSocket connected" ✅
   - Agar "WebSocket connection failed" dikhe, to server start karo

## 🛠️ Troubleshooting

### Server Not Starting?
- Check: `php -v` (needs PHP 7.4+)
- Check: `vendor` folder exists
- Check: Port 8080 available

### Composer Commands
Since composer is local, use:
```bash
php composer.phar install
php composer.phar update
```

---

**Current Status:** ✅ Ready to start!
**Next Step:** Run `websocket\start_server.bat` or `php websocket/server.php`


