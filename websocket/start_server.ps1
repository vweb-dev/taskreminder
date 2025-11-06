# PowerShell script to start WebSocket server
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "  Task Reminder - WebSocket Server" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Starting WebSocket server on port 8080..." -ForegroundColor Yellow
Write-Host "Press Ctrl+C to stop the server" -ForegroundColor Yellow
Write-Host ""

# Change to project directory
Set-Location $PSScriptRoot\..

# Start server
php websocket/server.php


