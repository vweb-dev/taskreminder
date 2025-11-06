<?php
session_start();
require_once 'includes/auth.php';
requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="ur" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="description" content="Manage your tasks and reminders efficiently with Task Reminder">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Task Reminder">
    <meta name="mobile-web-app-capable" content="yes">
    
    <title>Task Reminder - Dashboard</title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="manifest.json">
    
    <!-- Apple Touch Icons -->
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/icon-192x192.png">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/icons/icon-96x96.png">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container-main">
        <!-- Sidebar for Filters -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><i class="bi bi-funnel"></i> Filters</h2>
                <button class="btn-close-sidebar" id="btnCloseSidebar">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <div class="filter-section">
                <div class="filter-group">
                    <label class="filter-label"><i class="bi bi-search"></i> Search</label>
                    <input type="text" class="filter-input" id="searchInput" placeholder="Search tasks...">
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><i class="bi bi-flag"></i> Priority</label>
                    <div class="filter-checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="priority" value="urgent">
                            <span class="priority-badge urgent">Urgent</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="priority" value="high">
                            <span class="priority-badge high">High</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="priority" value="medium">
                            <span class="priority-badge medium">Medium</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="priority" value="low">
                            <span class="priority-badge low">Low</span>
                        </label>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><i class="bi bi-list-check"></i> Status</label>
                    <div class="filter-checkboxes">
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="status" value="pending" checked>
                            <span class="status-badge pending">Pending</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="status" value="in_progress" checked>
                            <span class="status-badge in_progress">In Progress</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="status" value="completed" checked>
                            <span class="status-badge completed">Completed</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" class="filter-checkbox" data-filter="status" value="cancelled">
                            <span class="status-badge cancelled">Cancelled</span>
                        </label>
                    </div>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label"><i class="bi bi-calendar"></i> Due Date</label>
                    <select class="filter-select" id="dueDateFilter">
                        <option value="all">All Tasks</option>
                        <option value="today">Today</option>
                        <option value="tomorrow">Tomorrow</option>
                        <option value="this_week">This Week</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                
                <button class="btn-reset-filters" id="btnResetFilters">
                    <i class="bi bi-arrow-clockwise"></i> Reset Filters
                </button>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content" id="mainContent">
            <!-- Top Bar -->
            <div class="top-bar">
                <button class="btn-toggle-sidebar" id="btnToggleSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div class="page-title-wrapper">
                    <div class="app-logo">
                        <img src="assets/icons/icon-96x96.png" alt="Task Reminder Logo" class="app-logo-img">
                    </div>
                    <div class="page-title-content">
                        <h1 class="page-title">Task Reminder</h1>
                        <p class="page-subtitle">Stay organized, stay productive</p>
                    </div>
                </div>
                <div class="top-bar-actions">
                    <!-- Notifications Bell -->
                    <div class="notification-menu" id="notificationMenu">
                        <button class="btn-notification" id="btnNotification">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
                        </button>
                        <div class="notification-dropdown" id="notificationDropdown">
                            <div class="notification-header">
                                <h3>Reminders</h3>
                                <button class="btn-mark-all-read" id="btnMarkAllRead">Mark all read</button>
                            </div>
                            <div class="notification-list" id="notificationList">
                                <div class="notification-empty">
                                    <i class="bi bi-bell-slash"></i>
                                    <p>No reminders</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="user-menu" id="userMenu">
                        <button class="btn-user" id="btnUser">
                            <i class="bi bi-person-circle"></i>
                            <span class="user-name"><?php echo htmlspecialchars($currentUser['username'] ?? 'User'); ?></span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="user-dropdown" id="userDropdown">
                            <div class="user-info">
                                <div class="user-name-full"><?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['username'] ?? 'User'); ?></div>
                                <div class="user-email"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a href="#" class="user-dropdown-item" id="btnLogout">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </div>
                    </div>
                    <button class="btn-sort" id="btnSort">
                        <i class="bi bi-sort-down"></i> Sort
                    </button>
                    <!-- Sort Dropdown -->
                    <div class="sort-dropdown" id="sortDropdown">
                        <div class="sort-option" data-sort="due_date">
                            <i class="bi bi-calendar"></i> Due Date
                        </div>
                        <div class="sort-option" data-sort="priority">
                            <i class="bi bi-flag"></i> Priority
                        </div>
                        <div class="sort-option" data-sort="created_at">
                            <i class="bi bi-clock-history"></i> Created Date
                        </div>
                        <div class="sort-option" data-sort="title">
                            <i class="bi bi-sort-alpha-down"></i> Title
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-container" id="statsContainer">
                <div class="stat-card stat-pending">
                    <div class="stat-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="statPending">0</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
                <div class="stat-card stat-progress">
                    <div class="stat-icon">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="statProgress">0</div>
                        <div class="stat-label">In Progress</div>
                    </div>
                </div>
                <div class="stat-card stat-completed">
                    <div class="stat-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="statCompleted">0</div>
                        <div class="stat-label">Completed</div>
                    </div>
                </div>
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="bi bi-list-task"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value" id="statTotal">0</div>
                        <div class="stat-label">Total Tasks</div>
                    </div>
                </div>
            </div>
            
            <!-- Task List Container -->
            <div class="tasks-container" id="tasksContainer">
                <!-- Tasks will be loaded here dynamically -->
                <div class="empty-state" id="emptyState">
                    <i class="bi bi-inbox"></i>
                    <h3>No tasks found</h3>
                    <p>Create your first task to get started!</p>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Floating Add Button -->
    <button class="btn-add-task" id="btnAddTask">
        <i class="bi bi-plus-lg"></i>
    </button>
    
    <!-- Task Modal (Add/Edit) -->
    <div class="modal-overlay" id="taskModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Task</h2>
                <button class="btn-close-modal" id="btnCloseModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body">
                <form id="taskForm">
                    <input type="hidden" id="taskId" name="task_id">
                    
                    <div class="form-group">
                        <label for="taskTitle" class="form-label">
                            <i class="bi bi-card-heading"></i> Task Title <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="taskTitle" name="title" required placeholder="e.g., Complete project documentation">
                        <small class="form-hint">Give your task a clear and descriptive title</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskDescription" class="form-label">
                            <i class="bi bi-text-paragraph"></i> Description
                        </label>
                        <textarea class="form-textarea" id="taskDescription" name="description" rows="4" placeholder="Add detailed information about your task... (Optional)"></textarea>
                        <small class="form-hint">Add any additional details, notes, or requirements for this task</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="taskPriority" class="form-label">
                                <i class="bi bi-flag"></i> Priority <span class="required">*</span>
                            </label>
                            <select class="form-select" id="taskPriority" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="taskStatus" class="form-label">
                                <i class="bi bi-list-check"></i> Status <span class="required">*</span>
                            </label>
                            <select class="form-select" id="taskStatus" name="status" required>
                                <option value="pending" selected>Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="taskDueDate" class="form-label">
                            <i class="bi bi-calendar-event"></i> Due Date & Time
                        </label>
                        <input type="datetime-local" class="form-input" id="taskDueDate" name="due_date" placeholder="Select due date and time">
                        <small class="form-hint">Set when this task should be completed (Optional)</small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="bi bi-bell"></i> Reminders
                        </label>
                        <div class="reminders-container" id="remindersContainer">
                            <!-- Reminders will be added here dynamically -->
                        </div>
                        <button type="button" class="btn-add-reminder" id="btnAddReminder">
                            <i class="bi bi-plus-circle"></i> Add Reminder
                        </button>
                        <small class="form-hint">Set reminders to get notified before the task is due</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" id="btnCancelTask">Cancel</button>
                        <button type="submit" class="btn-save" id="btnSaveTask">
                            <i class="bi bi-check-lg"></i> Save Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Task Details View Modal -->
    <div class="modal-overlay" id="taskDetailsModal">
        <div class="modal-content modal-content-large">
            <div class="modal-header">
                <h2 id="detailsModalTitle">Task Details</h2>
                <button class="btn-close-modal" id="btnCloseDetailsModal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body" id="taskDetailsBody">
                <!-- Task details will be loaded here -->
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Set user ID for WebSocket
        window.currentUserId = <?php echo $currentUser['id']; ?>;
        
        // Register Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                // Get base path dynamically
                const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
                const swPath = basePath + '/service-worker.js';
                navigator.serviceWorker.register(swPath)
                    .then(function(registration) {
                        console.log('✅ Service Worker registered:', registration.scope);
                        
                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New service worker available
                                    if (confirm('New version available! Reload to update?')) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(function(error) {
                        console.log('❌ Service Worker registration failed:', error);
                    });
            });
        }
        
        // PWA Install Prompt
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing
            e.preventDefault();
            // Stash the event so it can be triggered later
            deferredPrompt = e;
            // Show install button
            showInstallButton();
        });
        
        function showInstallButton() {
            // Check if install button already exists
            if ($('#pwaInstallBtn').length) {
                $('#pwaInstallBtn').fadeIn();
                return;
            }
            
            // Create install button
            const installBtn = $(`
                <button id="pwaInstallBtn" class="pwa-install-btn" title="Install App">
                    <i class="bi bi-download"></i>
                    <span>Install App</span>
                </button>
            `);
            
            $('body').append(installBtn);
            
            installBtn.on('click', async () => {
                if (!deferredPrompt) {
                    return;
                }
                
                // Show the install prompt
                deferredPrompt.prompt();
                
                // Wait for the user to respond
                const { outcome } = await deferredPrompt.userChoice;
                console.log(`User response to install prompt: ${outcome}`);
                
                // Clear the deferredPrompt
                deferredPrompt = null;
                
                // Hide install button
                installBtn.fadeOut();
            });
        }
        
        // Hide install button if app is already installed
        window.addEventListener('appinstalled', () => {
            console.log('✅ PWA installed');
            $('#pwaInstallBtn').fadeOut();
            deferredPrompt = null;
        });
    </script>
    <script src="assets/js/tasks.js"></script>
    <script src="assets/js/websocket.js"></script>
    <script src="assets/js/notifications.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>

