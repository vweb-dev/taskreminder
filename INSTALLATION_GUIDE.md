# Task Reminder - Installation Guide

Complete guide to install and run Task Reminder on any system.

## 📋 Requirements

### Server Requirements:
- **PHP**: 7.4 or higher
- **MySQL**: 5.7 or higher (or MariaDB 10.2+)
- **Web Server**: Apache/Nginx
- **Composer**: For PHP dependencies
- **Node.js**: Not required (Pure PHP application)

### Optional (for WebSocket):
- **PHP CLI**: For running WebSocket server
- **Ratchet Library**: Installed via Composer

## 🚀 Installation Steps

### Step 1: Download/Clone Application

```bash
# If using Git
git clone <repository-url> task-reminder
cd task-reminder

# Or download ZIP and extract
```

### Step 2: Install Dependencies

```bash
# Install PHP dependencies via Composer
composer install

# If composer.phar is included, use:
php composer.phar install
```

### Step 3: Database Setup

1. **Create Database:**
```sql
CREATE DATABASE task_reminder CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. **Import Schema:**
```bash
# Option 1: Using MySQL command line
mysql -u root -p task_reminder < database/schema.sql

# Option 2: Using phpMyAdmin
# - Open phpMyAdmin
# - Select 'task_reminder' database
# - Go to Import tab
# - Select database/schema.sql file
# - Click Go
```

3. **Run Alterations (if any):**
```bash
mysql -u root -p task_reminder < database/alter.sql
```

### Step 4: Configuration

1. **Edit `config/config.php`:**
```php
<?php
define('DB_HOST', 'localhost');        // Database host
define('DB_NAME', 'task_reminder');    // Database name
define('DB_USER', 'root');             // Database username
define('DB_PASS', '');                 // Database password
define('DB_CHARSET', 'utf8mb4');

// WebSocket Configuration
define('WS_HOST', 'localhost');        // WebSocket host
define('WS_PORT', 8080);               // WebSocket port
define('WS_URL', 'ws://localhost:8080'); // WebSocket URL
?>
```

2. **Update WebSocket URL in JavaScript:**
Edit `assets/js/websocket.js`:
```javascript
const WS_URL = 'ws://YOUR_DOMAIN:8080';
// Or for production:
const WS_URL = 'wss://YOUR_DOMAIN:8080';
```

### Step 5: Web Server Configuration

#### Apache (.htaccess)
Create `.htaccess` in root directory:
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>
```

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/task-reminder;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

### Step 6: Set Permissions

```bash
# Set proper permissions
chmod 755 -R .
chmod 777 -R database/  # If using SQLite
chmod 777 -R uploads/    # If using file uploads
```

### Step 7: Start WebSocket Server

#### Windows:
```powershell
# Using PowerShell
cd websocket
.\start_server.ps1

# Or manually
php server.php
```

#### Linux/Mac:
```bash
# Make script executable
chmod +x websocket/start_server.sh

# Run WebSocket server
cd websocket
php server.php

# Or run in background
nohup php server.php > websocket.log 2>&1 &
```

#### Using Screen (Linux):
```bash
screen -S websocket
cd websocket
php server.php
# Press Ctrl+A then D to detach
```

#### Using PM2 (if Node.js installed):
```bash
pm2 start websocket/server.php --interpreter php
pm2 save
pm2 startup
```

### Step 8: Access Application

1. Open browser: `http://localhost/task-reminder/`
2. Register a new account
3. Login and start using!

## 🔧 Common Issues & Solutions

### Issue 1: Database Connection Failed

**Error:** `Connection failed: Access denied`

**Solution:**
- Check `config/config.php` credentials
- Verify database exists: `SHOW DATABASES;`
- Check user permissions: `GRANT ALL ON task_reminder.* TO 'user'@'localhost';`

### Issue 2: Composer Dependencies Not Found

**Error:** `Class 'Ratchet\App' not found`

**Solution:**
```bash
# Reinstall dependencies
composer install --no-dev

# Or if using included composer.phar
php composer.phar install
```

### Issue 3: WebSocket Not Connecting

**Error:** `WebSocket connection failed`

**Solution:**
1. Check WebSocket server is running:
```bash
# Check if port is listening
netstat -an | grep 8080  # Linux/Mac
netstat -an | findstr 8080  # Windows
```

2. Check firewall:
```bash
# Allow port 8080
sudo ufw allow 8080  # Linux
```

3. Update WebSocket URL in `assets/js/websocket.js`

4. Check server logs in `websocket/` folder

### Issue 4: Session Errors

**Error:** `Session cannot be started`

**Solution:**
```bash
# Check session directory permissions
chmod 777 /var/lib/php/sessions  # Linux
# Or set custom session path in php.ini
```

### Issue 5: PWA Not Working

**Error:** Icons not loading, Service Worker not registering

**Solution:**
1. Ensure HTTPS (or localhost for development)
2. Check icon files exist: `assets/icons/icon-*.png`
3. Verify `manifest.json` paths are correct
4. Check browser console for errors

### Issue 6: 404 Errors on Routes

**Error:** `Page not found`

**Solution:**
- Enable Apache mod_rewrite: `a2enmod rewrite`
- Check `.htaccess` file exists
- Verify AllowOverride is set in Apache config

## 🌐 Production Deployment

### 1. Update Configuration

```php
// config/config.php
define('DB_HOST', 'production-db-host');
define('DB_NAME', 'production_db');
define('DB_USER', 'production_user');
define('DB_PASS', 'secure_password');

// Use HTTPS
define('WS_URL', 'wss://yourdomain.com:8080');
```

### 2. Enable HTTPS

```apache
# Apache Virtual Host
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /path/to/task-reminder
    
    SSLEngine on
    SSLCertificateFile /path/to/cert.pem
    SSLCertificateKeyFile /path/to/key.pem
</VirtualHost>
```

### 3. Set Up WebSocket as Service

#### Systemd Service (Linux):
Create `/etc/systemd/system/task-reminder-websocket.service`:
```ini
[Unit]
Description=Task Reminder WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/task-reminder/websocket
ExecStart=/usr/bin/php server.php
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable task-reminder-websocket
sudo systemctl start task-reminder-websocket
sudo systemctl status task-reminder-websocket
```

### 4. Set Up Cron Jobs (Optional)

For reminder checks (if not using WebSocket):
```bash
# Edit crontab
crontab -e

# Add this line (runs every minute)
* * * * * php /path/to/task-reminder/websocket/check_reminders.php
```

## 📱 Mobile/Remote Access

### For Local Network Access:

1. **Find your IP:**
```bash
# Linux/Mac
ifconfig | grep inet

# Windows
ipconfig
```

2. **Update WebSocket URL:**
```javascript
// assets/js/websocket.js
const WS_URL = 'ws://YOUR_IP:8080';
```

3. **Access from mobile:**
```
http://YOUR_IP/task-reminder/
```

### For Internet Access:

1. Use port forwarding on router
2. Use dynamic DNS service (No-IP, DuckDNS)
3. Or deploy on cloud server (AWS, DigitalOcean, etc.)

## 🔒 Security Checklist

- [ ] Change default database credentials
- [ ] Use strong passwords
- [ ] Enable HTTPS in production
- [ ] Set proper file permissions
- [ ] Update PHP to latest version
- [ ] Disable error display in production
- [ ] Use prepared statements (already implemented)
- [ ] Enable CSRF protection
- [ ] Regular backups

## 📞 Support

If you face any issues:

1. Check browser console (F12) for errors
2. Check PHP error logs
3. Check WebSocket server logs
4. Verify all requirements are met
5. Review this guide again

## 📝 Quick Checklist

- [ ] PHP 7.4+ installed
- [ ] MySQL/MariaDB installed
- [ ] Database created and schema imported
- [ ] `config/config.php` configured
- [ ] Composer dependencies installed
- [ ] Web server configured
- [ ] WebSocket server started
- [ ] Icons folder has all required files
- [ ] Permissions set correctly
- [ ] Application accessible in browser

---

**Note:** For detailed WebSocket setup, see `WEBSOCKET_SETUP.md`
**Note:** For PWA setup, see `PWA_SETUP.md`

