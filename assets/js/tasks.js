/**
 * Tasks Management JavaScript
 * Handles task CRUD operations
 */

// ============================================
// Task Management Functions
// ============================================

let tasksList = [];
let currentSort = 'due_date';
let currentFilters = {
    search: '',
    priority: [],
    status: ['pending', 'in_progress', 'completed'],
    dueDate: 'all'
};

/**
 * Load all tasks
 */
function loadTasks() {
    $.ajax({
        url: 'api/tasks.php',
        method: 'GET',
        dataType: 'json',
        data: { action: 'list' },
        success: function(response) {
            if (response.success) {
                tasksList = response.tasks || [];
                displayTasks(tasksList);
                updateStats(tasksList);
            } else {
                console.error('Failed to load tasks:', response.message);
                showEmptyState();
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading tasks:', error);
            showEmptyState();
        }
    });
}

/**
 * Display tasks in the container
 */
function displayTasks(tasks) {
    const container = $('#tasksContainer');
    container.empty();
    
    if (!tasks || tasks.length === 0) {
        showEmptyState();
        return;
    }
    
    // Apply filters
    let filteredTasks = applyFilters(tasks);
    
    // Apply sorting
    filteredTasks = applySorting(filteredTasks);
    
    if (filteredTasks.length === 0) {
        container.html(`
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="bi bi-funnel"></i>
                <h3>No tasks match your filters</h3>
                <p>Try adjusting your filters to see more tasks.</p>
            </div>
        `);
        return;
    }
    
    // Create task cards
    filteredTasks.forEach(task => {
        const taskCard = createTaskCard(task);
        container.append(taskCard);
    });
}

/**
 * Create task card HTML
 */
function createTaskCard(task) {
    const dueDate = task.due_date ? new Date(task.due_date) : null;
    const dueDateFormatted = dueDate ? formatDateTime(dueDate) : 'No due date';
    const isOverdue = dueDate && dueDate < new Date() && task.status !== 'completed';
    
    return $(`
        <div class="task-card" data-task-id="${task.id}" data-priority="${task.priority}" data-status="${task.status}">
            <div class="task-card-header">
                <h3 class="task-title" style="cursor: pointer;" onclick="viewTaskDetails(${task.id})">${escapeHtml(task.title)}</h3>
                <div class="task-actions">
                    <button class="task-btn view" data-task-id="${task.id}" title="View Details" onclick="viewTaskDetails(${task.id})">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="task-btn edit" data-task-id="${task.id}" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="task-btn complete" data-task-id="${task.id}" data-status="${task.status}" title="Toggle Complete">
                        <i class="bi ${task.status === 'completed' ? 'bi-check-circle-fill' : 'bi-circle'}"></i>
                    </button>
                    <button class="task-btn delete" data-task-id="${task.id}" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            
            ${task.description ? `
                <div class="task-description">${escapeHtml(task.description)}</div>
            ` : ''}
            
            <div class="task-meta">
                <span class="priority-badge ${task.priority}">${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</span>
                <span class="status-badge ${task.status}">${formatStatus(task.status)}</span>
            </div>
            
            <div class="task-footer">
                <div class="task-due-date ${isOverdue ? 'overdue' : ''}">
                    <i class="bi bi-calendar-event"></i>
                    <span>${dueDateFormatted}</span>
                </div>
                ${task.reminder_count > 0 ? `
                    <div class="task-reminder-count">
                        <i class="bi bi-bell"></i>
                        <span>${task.reminder_count}</span>
                    </div>
                ` : ''}
            </div>
        </div>
    `);
}

/**
 * Apply filters to tasks
 */
function applyFilters(tasks) {
    let filtered = tasks;
    
    // Search filter
    if (currentFilters.search) {
        const searchLower = currentFilters.search.toLowerCase();
        filtered = filtered.filter(task => 
            task.title.toLowerCase().includes(searchLower) ||
            (task.description && task.description.toLowerCase().includes(searchLower))
        );
    }
    
    // Priority filter
    if (currentFilters.priority.length > 0) {
        filtered = filtered.filter(task => 
            currentFilters.priority.includes(task.priority)
        );
    }
    
    // Status filter
    if (currentFilters.status.length > 0) {
        filtered = filtered.filter(task => 
            currentFilters.status.includes(task.status)
        );
    }
    
    // Due date filter
    if (currentFilters.dueDate !== 'all') {
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const tomorrow = new Date(today);
        tomorrow.setDate(tomorrow.getDate() + 1);
        const weekEnd = new Date(today);
        weekEnd.setDate(weekEnd.getDate() + 7);
        
        filtered = filtered.filter(task => {
            if (!task.due_date) return false;
            const dueDate = new Date(task.due_date);
            
            switch (currentFilters.dueDate) {
                case 'today':
                    return dueDate.toDateString() === today.toDateString();
                case 'tomorrow':
                    return dueDate.toDateString() === tomorrow.toDateString();
                case 'this_week':
                    return dueDate >= today && dueDate <= weekEnd;
                case 'overdue':
                    return dueDate < today && task.status !== 'completed';
                default:
                    return true;
            }
        });
    }
    
    return filtered;
}

/**
 * Apply sorting to tasks
 */
function applySorting(tasks) {
    const sorted = [...tasks];
    
    sorted.sort((a, b) => {
        switch (currentSort) {
            case 'due_date':
                if (!a.due_date && !b.due_date) return 0;
                if (!a.due_date) return 1;
                if (!b.due_date) return -1;
                return new Date(a.due_date) - new Date(b.due_date);
            
            case 'priority':
                const priorityOrder = { urgent: 4, high: 3, medium: 2, low: 1 };
                return (priorityOrder[b.priority] || 0) - (priorityOrder[a.priority] || 0);
            
            case 'created_at':
                return new Date(b.created_at) - new Date(a.created_at);
            
            case 'title':
                return a.title.localeCompare(b.title);
            
            default:
                return 0;
        }
    });
    
    return sorted;
}

/**
 * Update statistics
 */
function updateStats(tasks) {
    const stats = {
        pending: 0,
        in_progress: 0,
        completed: 0,
        total: tasks.length
    };
    
    tasks.forEach(task => {
        if (stats.hasOwnProperty(task.status)) {
            stats[task.status]++;
        }
    });
    
    $('#statPending').text(stats.pending);
    $('#statProgress').text(stats.in_progress);
    $('#statCompleted').text(stats.completed);
    $('#statTotal').text(stats.total);
}

/**
 * Show empty state
 */
function showEmptyState() {
    $('#tasksContainer').html(`
        <div class="empty-state" id="emptyState">
            <i class="bi bi-inbox"></i>
            <h3>No tasks found</h3>
            <p>Create your first task to get started!</p>
        </div>
    `);
}

/**
 * Format status for display
 */
function formatStatus(status) {
    const statusMap = {
        'pending': 'Pending',
        'in_progress': 'In Progress',
        'completed': 'Completed',
        'cancelled': 'Cancelled'
    };
    return statusMap[status] || status;
}

/**
 * Format datetime for display
 */
function formatDateTime(date) {
    const now = new Date();
    const taskDate = new Date(date);
    const diffMs = taskDate - now;
    const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24));
    
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    
    if (diffDays === 0) {
        return 'Today ' + taskDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (diffDays === 1) {
        return 'Tomorrow ' + taskDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (diffDays === -1) {
        return 'Yesterday ' + taskDate.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (diffDays < 0) {
        return taskDate.toLocaleDateString('en-US', options) + ' (Overdue)';
    } else {
        return taskDate.toLocaleDateString('en-US', options);
    }
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? String(text).replace(/[&<>"']/g, m => map[m]) : '';
}

/**
 * Save task (create or update)
 */
function saveTask(taskData) {
    const url = 'api/tasks.php';
    const method = taskData.id ? 'update' : 'create';
    const action = taskData.id ? 'update' : 'create';
    
    $.ajax({
        url: url,
        method: 'POST',
        dataType: 'json',
        data: {
            action: action,
            ...taskData
        },
        success: function(response) {
            if (response.success) {
                loadTasks(); // Reload tasks
                if (typeof window.closeTaskModal === 'function') {
                    window.closeTaskModal();
                }
            } else {
                alert('Error: ' + (response.message || 'Failed to save task'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error saving task:', error);
            alert('Failed to save task. Please try again.');
        }
    });
}

/**
 * Delete task
 */
function deleteTask(taskId) {
    if (!confirm('Are you sure you want to delete this task?')) {
        return;
    }
    
    $.ajax({
        url: 'api/tasks.php',
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'delete',
            id: taskId
        },
        success: function(response) {
            if (response.success) {
                loadTasks(); // Reload tasks
            } else {
                alert('Error: ' + (response.message || 'Failed to delete task'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error deleting task:', error);
            alert('Failed to delete task. Please try again.');
        }
    });
}

/**
 * Update task status
 */
function updateTaskStatus(taskId, newStatus) {
    $.ajax({
        url: 'api/tasks.php',
        method: 'POST',
        dataType: 'json',
        data: {
            action: 'update_status',
            id: taskId,
            status: newStatus
        },
        success: function(response) {
            if (response.success) {
                loadTasks(); // Reload tasks
            } else {
                alert('Error: ' + (response.message || 'Failed to update status'));
            }
        },
        error: function(xhr, status, error) {
            console.error('Error updating status:', error);
            alert('Failed to update status. Please try again.');
        }
    });
}

/**
 * Load task for editing
 */
function loadTaskForEdit(taskId) {
    $.ajax({
        url: 'api/tasks.php',
        method: 'GET',
        dataType: 'json',
        data: {
            action: 'get',
            id: taskId
        },
        success: function(response) {
            if (response.success && response.task) {
                if (typeof window.openTaskModal === 'function') {
                    window.openTaskModal(response.task);
                } else {
                    console.error('openTaskModal function not available');
                }
            } else {
                alert('Failed to load task');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading task:', error);
            alert('Failed to load task');
        }
    });
}

/**
 * View task details
 */
function viewTaskDetails(taskId) {
    $.ajax({
        url: 'api/tasks.php',
        method: 'GET',
        dataType: 'json',
        data: {
            action: 'get',
            id: taskId
        },
        success: function(response) {
            if (response.success && response.task) {
                displayTaskDetails(response.task);
            } else {
                alert('Failed to load task details');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error loading task details:', error);
            alert('Failed to load task details');
        }
    });
}

/**
 * Display task details in modal
 */
function displayTaskDetails(task) {
    const dueDate = task.due_date ? new Date(task.due_date) : null;
    const dueDateFormatted = dueDate ? formatDateTime(dueDate) : 'Not set';
    const isOverdue = dueDate && dueDate < new Date() && task.status !== 'completed';
    const createdDate = task.created_at ? new Date(task.created_at) : null;
    const updatedDate = task.updated_at ? new Date(task.updated_at) : null;
    
    let remindersHtml = '';
    if (task.reminders && task.reminders.length > 0) {
        remindersHtml = '<div class="details-reminders-list">';
        task.reminders.forEach(reminder => {
            const reminderTime = new Date(reminder.reminder_time);
            const reminderFormatted = formatDateTime(reminderTime);
            const isPast = reminderTime < new Date();
            remindersHtml += `
                <div class="details-reminder-item ${reminder.is_sent ? 'sent' : ''} ${isPast ? 'past' : ''}">
                    <i class="bi bi-bell${reminder.is_sent ? '-fill' : ''}"></i>
                    <span>${reminderFormatted}</span>
                    ${reminder.is_sent ? '<span class="reminder-status">Sent</span>' : '<span class="reminder-status pending">Pending</span>'}
                </div>
            `;
        });
        remindersHtml += '</div>';
    } else {
        remindersHtml = '<p class="details-empty">No reminders set</p>';
    }
    
    const detailsHtml = `
        <div class="task-details-view">
            <div class="details-section">
                <div class="details-header">
                    <h3 class="details-title">${escapeHtml(task.title)}</h3>
                    <div class="details-badges">
                        <span class="priority-badge ${task.priority}">${task.priority.charAt(0).toUpperCase() + task.priority.slice(1)}</span>
                        <span class="status-badge ${task.status}">${formatStatus(task.status)}</span>
                    </div>
                </div>
            </div>
            
            <div class="details-section">
                <h4 class="details-section-title"><i class="bi bi-text-paragraph"></i> Description</h4>
                <div class="details-content">
                    ${task.description ? `<p>${escapeHtml(task.description)}</p>` : '<p class="details-empty">No description provided</p>'}
                </div>
            </div>
            
            <div class="details-section">
                <h4 class="details-section-title"><i class="bi bi-calendar-event"></i> Due Date</h4>
                <div class="details-content">
                    <p class="${isOverdue ? 'overdue' : ''}">
                        <i class="bi bi-calendar3"></i> ${dueDateFormatted}
                        ${isOverdue ? '<span class="overdue-badge">Overdue</span>' : ''}
                    </p>
                </div>
            </div>
            
            <div class="details-section">
                <h4 class="details-section-title"><i class="bi bi-bell"></i> Reminders (${task.reminders ? task.reminders.length : 0})</h4>
                <div class="details-content">
                    ${remindersHtml}
                </div>
            </div>
            
            <div class="details-section">
                <h4 class="details-section-title"><i class="bi bi-info-circle"></i> Task Information</h4>
                <div class="details-content details-info-grid">
                    <div class="details-info-item">
                        <span class="info-label"><i class="bi bi-calendar-plus"></i> Created:</span>
                        <span class="info-value">${createdDate ? formatDateTime(createdDate) : 'N/A'}</span>
                    </div>
                    <div class="details-info-item">
                        <span class="info-label"><i class="bi bi-pencil-square"></i> Last Updated:</span>
                        <span class="info-value">${updatedDate ? formatDateTime(updatedDate) : 'N/A'}</span>
                    </div>
                    <div class="details-info-item">
                        <span class="info-label"><i class="bi bi-hash"></i> Task ID:</span>
                        <span class="info-value">#${task.id}</span>
                    </div>
                </div>
            </div>
            
            <div class="details-actions">
                <button class="btn-details-action btn-edit" onclick="loadTaskForEdit(${task.id}); closeTaskDetailsModal();">
                    <i class="bi bi-pencil"></i> Edit Task
                </button>
                <button class="btn-details-action btn-delete" onclick="if(confirm('Are you sure you want to delete this task?')) { deleteTask(${task.id}); closeTaskDetailsModal(); }">
                    <i class="bi bi-trash"></i> Delete Task
                </button>
            </div>
        </div>
    `;
    
    $('#taskDetailsBody').html(detailsHtml);
    $('#detailsModalTitle').text('Task Details: ' + escapeHtml(task.title));
    $('#taskDetailsModal').addClass('active');
}

/**
 * Close task details modal
 */
function closeTaskDetailsModal() {
    $('#taskDetailsModal').removeClass('active');
    $('#taskDetailsBody').empty();
}

// Make function globally accessible
window.viewTaskDetails = viewTaskDetails;
window.closeTaskDetailsModal = closeTaskDetailsModal;

