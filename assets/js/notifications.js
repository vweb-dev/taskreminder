/**
 * Notification Panel Management
 * Handles notification display and management
 */

let notificationList = [];
let unreadCount = 0;

// Make notificationList globally accessible for WebSocket
window.notificationList = notificationList;

/**
 * Initialize notification panel
 */
function initNotificationPanel() {
    // Load notifications on page load
    loadNotifications();
    
    // Move notification dropdown to body to avoid stacking context issues
    const $dropdown = $('#notificationDropdown');
    if ($dropdown.parent().attr('id') !== 'notificationMenu') {
        // Already moved
    } else {
        $dropdown.detach().appendTo('body');
    }
    
    // Toggle notification dropdown
    $('#btnNotification').on('click', function(e) {
        e.stopPropagation();
        const isActive = $dropdown.hasClass('active');
        
        if (!isActive) {
            // Calculate position dynamically
            const $btn = $(this);
            const btnOffset = $btn.offset();
            const btnHeight = $btn.outerHeight();
            const btnWidth = $btn.outerWidth();
            
            $dropdown.css({
                'top': (btnOffset.top + btnHeight + 8) + 'px',
                'right': ($(window).width() - btnOffset.left - btnWidth) + 'px'
            });
            
            loadNotifications();
        }
        
        $dropdown.toggleClass('active');
    });
    
    // Update position on window resize
    $(window).on('resize', function() {
        if ($dropdown.hasClass('active')) {
            const $btn = $('#btnNotification');
            const btnOffset = $btn.offset();
            const btnHeight = $btn.outerHeight();
            const btnWidth = $btn.outerWidth();
            
            $dropdown.css({
                'top': (btnOffset.top + btnHeight + 8) + 'px',
                'right': ($(window).width() - btnOffset.left - btnWidth) + 'px'
            });
        }
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#notificationMenu, #notificationDropdown').length) {
            $dropdown.removeClass('active');
        }
    });
    
    // Mark all as read
    $('#btnMarkAllRead').on('click', function(e) {
        e.stopPropagation();
        markAllAsRead();
    });
    
    // WebSocket will handle real-time notifications
    // Only check once on page load to show existing reminders
    setTimeout(() => {
        loadNotifications(false); // Show existing notifications (silent mode)
    }, 2000);
}

// Track shown notifications to avoid duplicates
let shownNotificationIds = [];

/**
 * Load notifications from API
 */
function loadNotifications(silent = false) {
    if (!window.currentUserId) {
        console.error('User ID not available');
        return;
    }
    
    $.ajax({
        url: 'api/reminders.php',
        method: 'GET',
        dataType: 'json',
        data: {
            action: 'check_due',
            user_id: window.currentUserId
        },
        success: function(response) {
            console.log('Notifications response:', response);
            
            if (response.success && response.reminders && response.reminders.length > 0) {
                // Filter out already shown notifications
                const newReminders = response.reminders.filter(r => {
                    return !shownNotificationIds.includes(r.id);
                });
                
                // Add new reminder IDs to shown list
                newReminders.forEach(r => {
                    shownNotificationIds.push(r.id);
                });
                
                // Merge with existing list (avoid duplicates)
                newReminders.forEach(newReminder => {
                    const exists = notificationList.find(r => r.id === newReminder.id);
                    if (!exists) {
                        notificationList.push(newReminder);
                    }
                });
                
                // Update global reference
                window.notificationList = notificationList;
                
                displayNotifications();
                updateBadge();
                
                // Show new notifications if not silent
                if (!silent && newReminders.length > 0) {
                    newReminders.forEach(reminder => {
                        showNotificationToast(reminder);
                        showBrowserNotification(reminder);
                    });
                }
            } else {
                // No new reminders, but keep existing list
                displayNotifications();
                updateBadge();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading notifications:', error, xhr.responseText);
        }
    });
}

/**
 * Display notifications in panel
 */
function displayNotifications() {
    const container = $('#notificationList');
    container.empty();
    
    if (notificationList.length === 0) {
        container.html(`
            <div class="notification-empty">
                <i class="bi bi-bell-slash"></i>
                <p>No reminders</p>
            </div>
        `);
        return;
    }
    
    // Sort by reminder time (newest first)
    const sorted = [...notificationList].sort((a, b) => {
        return new Date(b.reminder_time) - new Date(a.reminder_time);
    });
    
    sorted.forEach(reminder => {
        const notificationItem = createNotificationItem(reminder);
        container.append(notificationItem);
    });
}

/**
 * Create notification item HTML
 */
function createNotificationItem(reminder) {
    const time = formatNotificationTime(reminder.reminder_time);
    const isUnread = !reminder.is_sent || reminder.is_sent == 0;
    
    return $(`
        <div class="notification-item ${isUnread ? 'unread' : ''}" data-reminder-id="${reminder.id}">
            <div class="notification-item-icon">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div class="notification-item-content">
                <div class="notification-item-title">${escapeHtml(reminder.task_title)}</div>
                <div class="notification-item-message">${escapeHtml(reminder.task_description || 'Task reminder')}</div>
                <div class="notification-item-time">${time}</div>
            </div>
            <div class="notification-item-actions">
                <button class="notification-item-btn" onclick="markNotificationAsRead(${reminder.id})" title="Mark as read">
                    <i class="bi bi-check"></i>
                </button>
                <button class="notification-item-btn" onclick="deleteNotification(${reminder.id})" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `);
}

/**
 * Update notification badge
 */
function updateBadge() {
    unreadCount = notificationList.filter(r => {
        return !r.is_sent || r.is_sent == 0 || r.is_sent === null;
    }).length;
    const badge = $('#notificationBadge');
    
    if (unreadCount > 0) {
        badge.text(unreadCount > 9 ? '9+' : unreadCount).show();
        // Add animation
        badge.addClass('pulse');
        setTimeout(() => badge.removeClass('pulse'), 500);
    } else {
        badge.hide();
    }
}

/**
 * Show browser notification
 */
function showBrowserNotification(reminder) {
    if (!('Notification' in window)) {
        console.warn('Browser does not support notifications');
        return;
    }

    // Request permission if needed
    if (Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                showBrowserNotification(reminder);
            }
        });
        return;
    }

    if (Notification.permission === 'granted') {
        const notification = new Notification(`🔔 Task Reminder: ${reminder.task_title}`, {
            body: reminder.task_description || 'You have a task reminder',
            icon: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiM2MzY2RjEiLz4KPHBhdGggZD0iTTMyIDIwQzI4LjY4NzMgMjAgMjYgMjIuNjg3MyAyNiAyNlYzMkMyNiAzNS4zMTI3IDI4LjY4NzMgMzggMzIgMzhDMzUuMzEyNyAzOCAzOCAzNS4zMTI3IDM4IDMyVjI2QzM4IDIyLjY4NzMgMzUuMzEyNyAyMCAzMiAyMFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo=',
            badge: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiNFRjQ0NDQiLz4KPC9zdmc+',
            tag: `reminder-${reminder.id}`,
            requireInteraction: true,
            vibrate: [200, 100, 200]
        });
        
        // Handle notification click
        notification.onclick = function() {
            window.focus();
            notification.close();
        };
        
        // Auto close after 10 seconds
        setTimeout(() => {
            notification.close();
        }, 10000);
    } else if (Notification.permission === 'denied') {
        console.warn('Browser notifications are blocked. Please enable them in browser settings.');
    }
}

/**
 * Show notification toast
 */
function showNotificationToast(reminder) {
    if (!reminder) {
        console.error('No reminder data provided');
        return;
    }
    
    console.log('Showing toast notification:', reminder);
    
    // This will be called by websocket.js when reminder arrives
    // But we can also show it here
    const toast = $(`
        <div class="notification-toast" data-reminder-id="${reminder.id || 'test'}">
            <div class="toast-icon">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div class="toast-content">
                <div class="toast-title">${escapeHtml(reminder.task_title || 'Reminder')}</div>
                <div class="toast-message">${escapeHtml(reminder.task_description || 'Task reminder')}</div>
            </div>
            <button class="toast-close" onclick="$(this).closest('.notification-toast').fadeOut()">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `);
    
    $('body').append(toast);
    
    // Animate in
    setTimeout(() => {
        toast.addClass('show');
    }, 100);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.fadeOut(() => {
            toast.remove();
        });
    }, 5000);
}

// Make function globally accessible for testing
window.showNotificationToast = showNotificationToast;
window.showBrowserNotification = showBrowserNotification;

/**
 * Mark notification as read
 */
function markNotificationAsRead(reminderId) {
    $.ajax({
        url: 'api/reminders.php',
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'mark_read',
            id: reminderId
        },
        success: function(response) {
            if (response.success) {
                // Update local list
                const reminder = notificationList.find(r => r.id == reminderId);
                if (reminder) {
                    reminder.is_sent = 1;
                }
                displayNotifications();
                updateBadge();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error marking notification as read:', error);
        }
    });
}

/**
 * Delete notification
 */
function deleteNotification(reminderId) {
    if (!confirm('Are you sure you want to delete this reminder?')) {
        return;
    }
    
    $.ajax({
        url: 'api/reminders.php',
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'delete',
            id: reminderId
        },
        success: function(response) {
            if (response.success) {
                // Remove from local list
                notificationList = notificationList.filter(r => r.id != reminderId);
                window.notificationList = notificationList;
                displayNotifications();
                updateBadge();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error deleting notification:', error);
        }
    });
}

/**
 * Mark all notifications as read
 */
function markAllAsRead() {
    const unreadIds = notificationList
        .filter(r => !r.is_sent || r.is_sent == 0)
        .map(r => r.id);
    
    if (unreadIds.length === 0) {
        return;
    }
    
    $.ajax({
        url: 'api/reminders.php',
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'mark_all_read',
            ids: unreadIds
        },
        success: function(response) {
            if (response.success) {
                // Update all to read
                notificationList.forEach(r => {
                    r.is_sent = 1;
                });
                window.notificationList = notificationList;
                displayNotifications();
                updateBadge();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error marking all as read:', error);
        }
    });
}

/**
 * Format notification time
 */
function formatNotificationTime(timeString) {
    if (!timeString) return 'Unknown';
    
    const time = new Date(timeString);
    const now = new Date();
    const diffMs = now - time;
    const diffMins = Math.floor(diffMs / 60000);
    
    if (diffMins < 1) {
        return 'Just now';
    } else if (diffMins < 60) {
        return `${diffMins} minute${diffMins > 1 ? 's' : ''} ago`;
    } else if (diffMins < 1440) {
        const hours = Math.floor(diffMins / 60);
        return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    } else {
        return time.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: time.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
        });
    }
}

/**
 * Request browser notification permission
 */
function requestNotificationPermission() {
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                console.log('✅ Browser notifications enabled');
            } else {
                console.log('❌ Browser notifications blocked');
            }
        });
    }
}

/**
 * Escape HTML
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Initialize on page load
$(document).ready(function() {
    // Request notification permission first
    requestNotificationPermission();
    
    // Initialize notification panel
    initNotificationPanel();
    
    // Log for debugging
    console.log('Notification panel initialized');
});

