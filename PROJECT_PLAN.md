# Task Reminder Application - Project Plan

## Overview
A comprehensive task reminder application with real-time updates using WebSockets, built with modern web technologies.

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Libraries**: jQuery, Bootstrap Icons
- **Real-time**: WebSockets (no polling)
- **Backend**: PHP 7.4+
- **Database**: MySQL
- **Communication**: AJAX, WebSockets

## Project Structure
```
task-reminder/
├── index.php                 # Main dashboard
├── config/
│   └── config.php           # Database connection only
├── database/
│   ├── schema.sql           # Database schema
│   └── alter.sql            # Future schema modifications
├── api/
│   ├── tasks.php            # Task CRUD operations
│   ├── reminders.php        # Reminder management
│   └── websocket.php        # WebSocket server
├── assets/
│   ├── css/
│   │   └── style.css        # Main stylesheet
│   ├── js/
│   │   ├── main.js          # Main JavaScript
│   │   ├── tasks.js         # Task management
│   │   └── websocket.js     # WebSocket client
│   └── images/
└── includes/
    ├── header.php            # Header component
    └── footer.php            # Footer component
```

## Database Schema

### Tables

#### 1. tasks
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `title` (VARCHAR 255)
- `description` (TEXT)
- `priority` (ENUM: 'low', 'medium', 'high', 'urgent')
- `status` (ENUM: 'pending', 'in_progress', 'completed', 'cancelled')
- `due_date` (DATETIME, nullable)
- `created_at` (TIMESTAMP)
- `updated_at` (TIMESTAMP)
- `user_id` (INT, for future multi-user support)

#### 2. reminders
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `task_id` (INT, FOREIGN KEY → tasks.id)
- `reminder_time` (DATETIME)
- `is_sent` (BOOLEAN, default: 0)
- `reminder_type` (ENUM: 'email', 'notification', 'both')
- `created_at` (TIMESTAMP)

#### 3. task_history (for audit trail)
- `id` (INT, PRIMARY KEY, AUTO_INCREMENT)
- `task_id` (INT, FOREIGN KEY → tasks.id)
- `action` (VARCHAR 100) - 'created', 'updated', 'deleted', 'status_changed'
- `old_value` (TEXT, nullable)
- `new_value` (TEXT, nullable)
- `created_at` (TIMESTAMP)

## Features

### Core Features
1. **Task Management**
   - Create, Read, Update, Delete tasks
   - Task filtering (by status, priority, due date)
   - Task search functionality
   - Task sorting (by date, priority, status)

2. **Reminder System**
   - Set multiple reminders per task
   - Reminder notifications (WebSocket)
   - Visual countdown timers
   - Reminder history

3. **Real-time Updates**
   - WebSocket connection for live updates
   - Multiple browser tabs sync
   - Instant notifications for reminders

4. **UI/UX Features**
   - Responsive design
   - Drag & drop for task priorities
   - Color coding by priority
   - Status badges
   - Calendar view (future enhancement)
   - Dark mode toggle (future enhancement)

### Priority Levels
- **Low**: Green
- **Medium**: Yellow
- **High**: Orange
- **Urgent**: Red

### Status Types
- **Pending**: Gray
- **In Progress**: Blue
- **Completed**: Green
- **Cancelled**: Red (strikethrough)

## API Endpoints

### Tasks API (`api/tasks.php`)
- `GET /api/tasks.php?action=list` - Get all tasks
- `GET /api/tasks.php?action=get&id={id}` - Get single task
- `POST /api/tasks.php?action=create` - Create task
- `POST /api/tasks.php?action=update` - Update task
- `POST /api/tasks.php?action=delete` - Delete task
- `POST /api/tasks.php?action=update_status` - Update task status

### Reminders API (`api/reminders.php`)
- `GET /api/reminders.php?action=list&task_id={id}` - Get reminders for task
- `POST /api/reminders.php?action=create` - Create reminder
- `POST /api/reminders.php?action=delete` - Delete reminder
- `GET /api/reminders.php?action=upcoming` - Get upcoming reminders

## WebSocket Events

### Client → Server
- `connect` - Establish connection
- `subscribe_tasks` - Subscribe to task updates
- `subscribe_reminders` - Subscribe to reminder updates

### Server → Client
- `task_created` - New task created
- `task_updated` - Task updated
- `task_deleted` - Task deleted
- `reminder_triggered` - Reminder fired
- `status_changed` - Task status changed

## Development Phases

### Phase 1: Foundation
1. Database schema creation
2. Config file setup
3. Basic HTML structure
4. CSS framework setup

### Phase 2: Core Functionality
1. Task CRUD operations (Backend)
2. Task CRUD operations (Frontend)
3. AJAX integration
4. Basic UI for task list

### Phase 3: Reminder System
1. Reminder CRUD operations
2. Reminder scheduling logic
3. Reminder notifications UI

### Phase 4: Real-time Features
1. WebSocket server setup
2. WebSocket client integration
3. Real-time task updates
4. Real-time reminder notifications

### Phase 5: UI/UX Enhancements
1. Filtering and search
2. Sorting functionality
3. Priority and status badges
4. Responsive design polish

### Phase 6: Testing & Refinement
1. Cross-browser testing
2. Error handling
3. Performance optimization
4. Final polish

## UI Components

### Main Dashboard
- Task list with cards
- Filter sidebar
- Add task button (floating)
- Status summary cards
- Search bar

### Task Card
- Task title
- Description (truncated)
- Priority badge
- Status badge
- Due date
- Reminder count
- Action buttons (Edit, Delete, Complete)

### Add/Edit Task Modal
- Title input
- Description textarea
- Priority dropdown
- Due date picker
- Reminder section
- Save/Cancel buttons

## Security Considerations
- SQL injection prevention (prepared statements)
- XSS prevention (input sanitization)
- CSRF protection (tokens)
- Input validation (both client & server)

## Future Enhancements
- User authentication
- Multi-user support
- Email reminders
- Task categories/tags
- Calendar view
- Export/Import functionality
- Mobile app
- Dark mode

---

**Note**: This plan follows the user's preferences:
- UI first approach
- Minimal static data
- Unique classes/IDs
- Bootstrap icons (not emojis)
- WebSockets for real-time (no polling)
- Database schema in separate .sql file
- Config only for DB connection



