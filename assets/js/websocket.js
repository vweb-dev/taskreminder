/**
 * WebSocket Client for Task Reminders
 * Handles real-time reminder notifications
 */

class ReminderWebSocket {
    constructor() {
        this.ws = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5; // Increased attempts for better reliability
        this.reconnectDelay = 3000; // 3 seconds
        this.userId = null;
        this.isConnected = false;
    }

    /**
     * Initialize WebSocket connection
     */
    init(userId) {
        this.userId = userId;
        
        // Determine WebSocket URL
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const host = window.location.hostname;
        const wsPort = 8080; // WebSocket server port
        const wsUrl = `${protocol}//${host}:${wsPort}`;
        
        try {
            this.connect(wsUrl);
        } catch (error) {
            console.error('WebSocket connection error:', error);
            console.warn('⚠️ WebSocket server unavailable. Please start WebSocket server: php websocket/server.php');
        }
    }

    /**
     * Connect to WebSocket server
     */
    connect(wsUrl) {
        try {
            this.ws = new WebSocket(wsUrl);

            // Set connection timeout
            const connectTimeout = setTimeout(() => {
                if (this.ws && this.ws.readyState === WebSocket.CONNECTING) {
                    console.warn('WebSocket connection timeout. Please ensure server is running: php websocket/server.php');
                    this.ws.close();
                    this.reconnect();
                }
            }, 10000); // 10 second timeout

            this.ws.onopen = () => {
                clearTimeout(connectTimeout);
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                this.subscribe();
            };

            this.ws.onmessage = (event) => {
                this.handleMessage(event);
            };

            this.ws.onerror = (error) => {
                clearTimeout(connectTimeout);
                if (this.reconnectAttempts === 0) {
                    console.warn('WebSocket connection failed. Please start server: php websocket/server.php');
                }
            };

            this.ws.onclose = (event) => {
                clearTimeout(connectTimeout);
                // Only log if it was a connected connection
                if (this.isConnected) {
                    console.log('WebSocket disconnected');
                }
                this.isConnected = false;
                
                // Only try to reconnect if it wasn't a clean close
                if (event.code !== 1000 && this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnect();
                } else if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                    console.error('❌ WebSocket connection failed after multiple attempts.');
                    console.warn('⚠️ Please start WebSocket server to receive real-time reminders: php websocket/server.php');
                }
            };

        } catch (error) {
            console.error('WebSocket not supported or server unavailable:', error);
            console.warn('⚠️ Please start WebSocket server: php websocket/server.php');
        }
    }

    /**
     * Subscribe to reminders
     */
    subscribe() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN && this.userId) {
            this.ws.send(JSON.stringify({
                action: 'subscribe',
                user_id: this.userId
            }));
        }
    }

    /**
     * Handle incoming messages
     */
    handleMessage(event) {
        try {
            const data = JSON.parse(event.data);

            switch (data.type) {
                case 'reminder':
                    this.handleReminder(data.data);
                    break;
                
                case 'subscribed':
                    console.log('Subscribed to reminders');
                    break;
                
                case 'pong':
                    // Keep-alive response
                    break;
                
                default:
                    console.log('Unknown message type:', data.type);
            }
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }

    /**
     * Handle reminder notification
     */
    handleReminder(reminderData) {
        console.log('🔔 Reminder received via WebSocket:', reminderData);
        
        // Add reminder to notification list immediately
        if (typeof window.notificationList !== 'undefined' && Array.isArray(window.notificationList)) {
            // Check if reminder already exists
            const exists = window.notificationList.find(r => r.id == reminderData.id);
            if (!exists) {
                window.notificationList.push(reminderData);
                // Also update local notificationList if it exists
                if (typeof notificationList !== 'undefined' && Array.isArray(notificationList)) {
                    notificationList.push(reminderData);
                }
                console.log('✅ Reminder added to notification list:', reminderData);
            }
        } else {
            // Initialize if not exists
            window.notificationList = [reminderData];
            if (typeof notificationList !== 'undefined') {
                notificationList = [reminderData];
            }
            console.log('✅ Notification list initialized with reminder');
        }
        
        // Show browser notification if permission granted
        this.showNotification(reminderData);
        
        // Show in-app notification toast
        if (typeof showNotificationToast === 'function') {
            showNotificationToast(reminderData);
        } else {
            this.showInAppNotification(reminderData);
        }
        
        // Update notification panel display
        if (typeof displayNotifications === 'function') {
            displayNotifications();
        }
        
        // Update badge
        if (typeof updateBadge === 'function') {
            updateBadge();
        }
        
        // Also reload from API to ensure sync
        if (typeof loadNotifications === 'function') {
            loadNotifications(true); // Silent mode to avoid duplicate toasts
        }
        
        // Reload tasks if on tasks page
        if (typeof loadTasks === 'function') {
            loadTasks();
        }
    }

    /**
     * Show browser notification
     */
    showNotification(reminderData) {
        if (!('Notification' in window)) {
            console.warn('Browser does not support notifications');
            return;
        }

        // Request permission if not already granted/denied
        if (Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.showNotification(reminderData);
                }
            });
            return;
        }

        if (Notification.permission === 'granted') {
            const notification = new Notification(`🔔 Task Reminder: ${reminderData.task_title}`, {
                body: reminderData.task_description || 'You have a task reminder',
                icon: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiM2MzY2RjEiLz4KPHBhdGggZD0iTTMyIDIwQzI4LjY4NzMgMjAgMjYgMjIuNjg3MyAyNiAyNlYzMkMyNiAzNS4zMTI3IDI4LjY4NzMgMzggMzIgMzhDMzUuMzEyNyAzOCAzOCAzNS4zMTI3IDM4IDMyVjI2QzM4IDIyLjY4NzMgMzUuMzEyNyAyMCAzMiAyMFoiIGZpbGw9IndoaXRlIi8+Cjwvc3ZnPgo=',
                badge: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMzIiIGN5PSIzMiIgcj0iMzIiIGZpbGw9IiNFRjQ0NDQiLz4KPC9zdmc+',
                tag: `reminder-${reminderData.id}`,
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
     * Show in-app notification
     */
    showInAppNotification(reminderData) {
        // Escape HTML helper
        const escapeHtml = (text) => {
            if (!text) return '';
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        };
        
        // Create notification element
        const notification = $(`
            <div class="reminder-notification" data-reminder-id="${reminderData.id}">
                <div class="notification-icon">
                    <i class="bi bi-bell-fill"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${escapeHtml(reminderData.task_title)}</div>
                    <div class="notification-message">Reminder: ${escapeHtml(reminderData.task_description || 'Task reminder')}</div>
                </div>
                <button class="notification-close" onclick="$(this).closest('.reminder-notification').fadeOut()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `);

        // Add to page
        $('body').append(notification);

        // Animate in
        setTimeout(() => {
            notification.addClass('show');
        }, 100);

        // Auto remove after 10 seconds
        setTimeout(() => {
            notification.fadeOut(() => {
                notification.remove();
            });
        }, 10000);
    }

    /**
     * Reconnect to WebSocket
     */
    reconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            // Only log first few attempts to reduce console spam
            if (this.reconnectAttempts <= 2) {
                console.log(`WebSocket reconnecting... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);
            }
            
            setTimeout(() => {
                const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
                const host = window.location.hostname;
                const wsUrl = `${protocol}//${host}:8080`;
                this.connect(wsUrl);
            }, this.reconnectDelay);
        } else {
            console.error('❌ WebSocket connection failed after multiple attempts.');
            console.warn('⚠️ Please start WebSocket server to receive real-time reminders: php websocket/server.php');
        }
    }

    /**
     * Send ping to keep connection alive
     */
    ping() {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({
                action: 'ping'
            }));
        }
    }

    /**
     * Close WebSocket connection
     */
    close() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
            this.isConnected = false;
        }
    }
}

// Global instance
let reminderWebSocket = null;

// Initialize WebSocket when page loads
$(document).ready(function() {
    // Request browser notification permission
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission().then(permission => {
            if (permission === 'granted') {
                console.log('✅ Browser notifications enabled');
            }
        });
    }
    
    // Get user ID from session or global variable
    // This should be set in index.php
    if (typeof window.currentUserId !== 'undefined' && window.currentUserId) {
        reminderWebSocket = new ReminderWebSocket();
        reminderWebSocket.init(window.currentUserId);
        
        // Send ping every 30 seconds to keep connection alive
        setInterval(() => {
            if (reminderWebSocket && reminderWebSocket.isConnected) {
                reminderWebSocket.ping();
            }
        }, 30000);
    } else {
        console.error('User ID not available for WebSocket');
    }
});

