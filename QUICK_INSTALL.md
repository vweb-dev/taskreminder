# Quick Installation Guide (Urdu/Hindi)

## ⚡ Tezi Se Install Karein

### 1. Requirements Check Karein

```bash
# PHP version check
php -v  # PHP 7.4 ya higher chahiye

# MySQL check
mysql --version  # MySQL 5.7+ ya MariaDB 10.2+

# Composer check
composer --version
```

### 2. Database Setup

```sql
-- phpMyAdmin ya MySQL command line se:
CREATE DATABASE task_reminder;
```

Phir `database/schema.sql` file import karein.

### 3. Configuration

`config/config.php` file edit karein:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_reminder');
define('DB_USER', 'root');        // Apna username
define('DB_PASS', '');            // Apna password
```

### 4. Dependencies Install

```bash
composer install
# Ya
php composer.phar install
```

### 5. WebSocket Server Start

**Windows:**
```powershell
cd websocket
.\start_server.ps1
```

**Linux/Mac:**
```bash
cd websocket
php server.php
```

### 6. Browser Mein Open Karein

```
http://localhost/task-reminder/
```

## ❌ Agar Problem Aaye

### Database Connect Nahi Ho Raha?
- `config/config.php` mein credentials check karein
- Database create hua hai ya nahi verify karein

### WebSocket Connect Nahi Ho Raha?
- WebSocket server chal raha hai ya nahi check karein
- Port 8080 open hai ya nahi verify karein
- `assets/js/websocket.js` mein URL sahi hai ya nahi

### Icons Nahi Dikha?
- `assets/icons/` folder mein sab icons hain ya nahi check karein
- Browser console (F12) mein errors check karein

### Page 404 Error?
- `.htaccess` file hai ya nahi check karein
- Apache mod_rewrite enable hai ya nahi

## 📱 Mobile Se Access

1. Apna IP address nikalein: `ipconfig` (Windows) ya `ifconfig` (Linux/Mac)
2. Mobile browser mein: `http://YOUR_IP/task-reminder/`
3. WebSocket URL update karein: `ws://YOUR_IP:8080`

## 🔧 Production Ke Liye

1. HTTPS enable karein
2. Strong passwords use karein
3. WebSocket ko service banaein (systemd)
4. Regular backups lein

---

**Detailed Guide:** `INSTALLATION_GUIDE.md` dekhein

