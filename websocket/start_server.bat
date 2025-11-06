@echo off
echo ========================================
echo   Task Reminder - WebSocket Server
echo ========================================
echo.
echo Starting WebSocket server on port 8080...
echo Press Ctrl+C to stop the server
echo.
cd /d "%~dp0\.."
php websocket/server.php
pause
