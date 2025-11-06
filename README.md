# 📋 Task Reminder

A modern, feature-rich Task Management and Reminder application built with PHP, MySQL, and WebSockets.

## ✨ Features

- ✅ **Task Management** - Create, edit, delete, and organize tasks
- 🔔 **Smart Reminders** - Set multiple reminders for each task
- 📱 **PWA Support** - Install as Progressive Web App
- ⚡ **Real-time Updates** - WebSocket-based live notifications
- 🎨 **Modern UI** - Beautiful, responsive design
- 🔐 **User Authentication** - Secure login/registration
- 📊 **Task Statistics** - View pending, in-progress, and completed tasks
- 🔍 **Search & Filter** - Find tasks quickly
- 📅 **Due Date Tracking** - Never miss a deadline
- 🏷️ **Priority Levels** - Organize by Low, Medium, High, Urgent

## 🚀 Quick Start

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web Server (Apache/Nginx)

### Installation

1. **Clone/Download the repository**
```bash
git clone <repository-url> task-reminder
cd task-reminder
```

2. **Install dependencies**
```bash
composer install
```

3. **Setup database**
```sql
CREATE DATABASE task_reminder;
-- Import database/schema.sql
```

4. **Configure**
Edit `config/config.php` with your database credentials

5. **Start WebSocket server**
```bash
# Windows
cd websocket
.\start_server.ps1

# Linux/Mac
cd websocket
php server.php
```

6. **Access application**
Open `http://localhost/task-reminder/` in browser

## 📖 Documentation

- **[Quick Start Guide](QUICK_START.md)** - Get started in 5 minutes
- **[Installation Guide](INSTALLATION_GUIDE.md)** - Complete installation instructions
- **[Quick Install (Urdu/Hindi)](QUICK_INSTALL.md)** - Tezi se install karein
- **[WebSocket Setup](WEBSOCKET_SETUP.md)** - WebSocket server configuration
- **[PWA Setup](PWA_SETUP.md)** - Progressive Web App configuration
- **[Start Server Guide](START_SERVER.md)** - How to start WebSocket server

## 🛠️ Technology Stack

- **Backend:** PHP 7.4+
- **Database:** MySQL/MariaDB
- **Frontend:** HTML5, CSS3, JavaScript (jQuery)
- **Real-time:** WebSockets (Ratchet)
- **Icons:** Bootstrap Icons
- **PWA:** Service Worker, Manifest

## 📁 Project Structure

```
task-reminder/
├── api/                 # API endpoints
├── assets/             # CSS, JS, icons
├── config/             # Configuration files
├── database/           # SQL schemas
├── includes/           # PHP includes
├── websocket/          # WebSocket server
├── index.php           # Main dashboard
├── login.php           # Login page
├── register.php        # Registration page
├── manifest.json       # PWA manifest
├── service-worker.js   # PWA service worker
└── composer.json       # PHP dependencies
```

## 🔧 Configuration

### Database
Edit `config/config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'task_reminder');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### WebSocket
Edit `assets/js/websocket.js`:
```javascript
const WS_URL = 'ws://localhost:8080';
```

## 🌐 Deployment

### Production Checklist
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Configure WebSocket as service
- [ ] Set proper file permissions
- [ ] Generate PWA icons
- [ ] Test all features
- [ ] Setup backups

See [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) for detailed deployment instructions.

## 🐛 Troubleshooting

### Common Issues

**Database Connection Failed**
- Check `config/config.php` credentials
- Verify database exists

**WebSocket Not Connecting**
- Ensure WebSocket server is running
- Check port 8080 is not blocked
- Verify WebSocket URL in `assets/js/websocket.js`

**Icons Not Loading**
- Check `assets/icons/` folder has all required icons
- Verify `manifest.json` paths

See [INSTALLATION_GUIDE.md](INSTALLATION_GUIDE.md) for more solutions.

## 📱 PWA Features

- ✅ Offline support
- ✅ Installable on devices
- ✅ Push notifications
- ✅ App-like experience

See [PWA_SETUP.md](PWA_SETUP.md) for setup instructions.

## 🔐 Security

- Prepared statements (SQL injection protection)
- Password hashing (bcrypt)
- Session management
- CSRF protection ready
- XSS protection

## 📝 License

This project is open source and available for personal and commercial use.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📞 Support

For issues and questions:
1. Check documentation files
2. Review browser console (F12)
3. Check server logs
4. Verify all requirements

## 🎯 Features Roadmap

- [ ] Task categories/tags
- [ ] File attachments
- [ ] Task templates
- [ ] Calendar view
- [ ] Export/Import tasks
- [ ] Team collaboration
- [ ] Email notifications

---

**Made with ❤️ for better task management**

