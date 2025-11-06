/**
 * Task Reminder - Main JavaScript
 * Handles UI interactions and basic functionality
 */

$(document).ready(function() {
    'use strict';
    
    // ============================================
    // UI State Management
    // ============================================
    
    let currentSort = 'due_date';
    let currentFilters = {
        search: '',
        priority: [],
        status: ['pending', 'in_progress', 'completed'],
        dueDate: 'all'
    };
    
    // ============================================
    // Sidebar Toggle
    // ============================================
    
    $('#btnToggleSidebar').on('click', function() {
        $('#sidebar').toggleClass('active');
    });
    
    $('#btnCloseSidebar').on('click', function() {
        $('#sidebar').removeClass('active');
    });
    
    // Close sidebar when clicking outside on mobile
    $(document).on('click', function(e) {
        if ($(window).width() <= 768) {
            if (!$(e.target).closest('#sidebar, #btnToggleSidebar').length) {
                $('#sidebar').removeClass('active');
            }
        }
    });
    
    // ============================================
    // Modal Management
    // ============================================
    
    $('#btnAddTask').on('click', function() {
        window.openTaskModal();
    });
    
    $('#btnCloseModal, #btnCancelTask').on('click', function() {
        window.closeTaskModal();
    });
    
    $('#taskModal').on('click', function(e) {
        if ($(e.target).is('#taskModal')) {
            window.closeTaskModal();
        }
    });
    
    // Task Details Modal
    $('#btnCloseDetailsModal, #taskDetailsModal').on('click', function(e) {
        if ($(e.target).is('#taskDetailsModal') || $(e.target).closest('#btnCloseDetailsModal').length) {
            closeTaskDetailsModal();
        }
    });
    
    // Make functions globally accessible
    window.openTaskModal = function(taskData = null) {
        $('#modalTitle').text(taskData ? 'Edit Task' : 'Add New Task');
        $('#taskForm')[0].reset();
        $('#taskId').val('');
        $('#remindersContainer').empty();
        
        if (taskData) {
            // Populate form with task data
            $('#taskId').val(taskData.id);
            $('#taskTitle').val(taskData.title);
            $('#taskDescription').val(taskData.description || '');
            $('#taskPriority').val(taskData.priority);
            $('#taskStatus').val(taskData.status);
            if (taskData.due_date) {
                const dueDate = new Date(taskData.due_date);
                $('#taskDueDate').val(dueDate.toISOString().slice(0, 16));
            }
            
            // Load reminders if editing
            if (taskData.reminders && taskData.reminders.length > 0) {
                taskData.reminders.forEach(reminder => {
                    const reminderTime = new Date(reminder.reminder_time);
                    const reminderHtml = `
                        <div class="reminder-item">
                            <input type="datetime-local" class="form-input reminder-time" name="reminders[]" value="${reminderTime.toISOString().slice(0, 16)}" required>
                            <button type="button" class="task-btn delete remove-reminder">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                    $('#remindersContainer').append(reminderHtml);
                });
            }
        }
        
        $('#taskModal').addClass('active');
    };
    
    window.closeTaskModal = function() {
        $('#taskModal').removeClass('active');
        $('#taskForm')[0].reset();
        $('#taskId').val('');
        $('#remindersContainer').empty();
    };
    
    // ============================================
    // Form Handling
    // ============================================
    
    $('#taskForm').on('submit', function(e) {
        e.preventDefault();
        
        const taskData = {
            task_id: $('#taskId').val(),
            title: $('#taskTitle').val().trim(),
            description: $('#taskDescription').val().trim(),
            priority: $('#taskPriority').val(),
            status: $('#taskStatus').val(),
            due_date: $('#taskDueDate').val() || null
        };
        
        // Collect reminders
        const reminders = [];
        $('.reminder-time').each(function() {
            const reminderTime = $(this).val();
            if (reminderTime) {
                reminders.push(reminderTime);
            }
        });
        taskData.reminders = reminders;
        
        // Validation
        if (!taskData.title) {
            alert('Please enter a task title');
            return;
        }
        
        // Save task using function from tasks.js
        if (typeof saveTask === 'function') {
            saveTask(taskData);
        } else {
            console.error('saveTask function not available');
            alert('Error: Task functions not loaded');
        }
    });
    
    // Add Reminder
    $('#btnAddReminder').on('click', function() {
        const reminderHtml = `
            <div class="reminder-item">
                <input type="datetime-local" class="form-input reminder-time" name="reminders[]" required>
                <button type="button" class="task-btn delete remove-reminder">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        $('#remindersContainer').append(reminderHtml);
    });
    
    $(document).on('click', '.remove-reminder', function() {
        $(this).closest('.reminder-item').remove();
    });
    
    // ============================================
    // Filter Management
    // ============================================
    
    // Search Filter
    $('#searchInput').on('input', function() {
        currentFilters.search = $(this).val().toLowerCase();
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // Priority Filter
    $('.filter-checkbox[data-filter="priority"]').on('change', function() {
        const priority = $(this).val();
        if ($(this).is(':checked')) {
            if (!currentFilters.priority.includes(priority)) {
                currentFilters.priority.push(priority);
            }
        } else {
            currentFilters.priority = currentFilters.priority.filter(p => p !== priority);
        }
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // Status Filter
    $('.filter-checkbox[data-filter="status"]').on('change', function() {
        const status = $(this).val();
        if ($(this).is(':checked')) {
            if (!currentFilters.status.includes(status)) {
                currentFilters.status.push(status);
            }
        } else {
            currentFilters.status = currentFilters.status.filter(s => s !== status);
        }
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // Due Date Filter
    $('#dueDateFilter').on('change', function() {
        currentFilters.dueDate = $(this).val();
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // Reset Filters
    $('#btnResetFilters').on('click', function() {
        currentFilters = {
            search: '',
            priority: [],
            status: ['pending', 'in_progress', 'completed'],
            dueDate: 'all'
        };
        
        $('#searchInput').val('');
        $('.filter-checkbox[data-filter="priority"]').prop('checked', false);
        $('.filter-checkbox[data-filter="status"]').prop('checked', function() {
            return ['pending', 'in_progress', 'completed'].includes($(this).val());
        });
        $('#dueDateFilter').val('all');
        
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // ============================================
    // User Menu
    // ============================================
    
    $('#btnUser').on('click', function(e) {
        e.stopPropagation();
        $('#userDropdown').toggleClass('active');
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#userMenu').length) {
            $('#userDropdown').removeClass('active');
        }
    });
    
    // Logout
    $('#btnLogout').on('click', function(e) {
        e.preventDefault();
        
        if (confirm('Are you sure you want to logout?')) {
            $.ajax({
                url: 'api/auth.php',
                method: 'POST',
                dataType: 'json',
                data: { action: 'logout' },
                success: function(response) {
                    if (response.success) {
                        window.location.href = 'login.php';
                    }
                },
                error: function() {
                    // Even if error, redirect to login
                    window.location.href = 'login.php';
                }
            });
        }
    });
    
    // ============================================
    // Sort Management
    // ============================================
    
    $('#btnSort').on('click', function(e) {
        e.stopPropagation();
        $('#sortDropdown').toggleClass('active');
    });
    
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#btnSort, #sortDropdown').length) {
            $('#sortDropdown').removeClass('active');
        }
    });
    
    $('.sort-option').on('click', function() {
        currentSort = $(this).data('sort');
        $('#sortDropdown').removeClass('active');
        $('#btnSort').html(`<i class="bi bi-sort-down"></i> Sort (${$(this).text().trim()})`);
        if (typeof displayTasks === 'function') {
            displayTasks(tasksList);
        }
    });
    
    // ============================================
    // Task Actions (Placeholder)
    // ============================================
    
    // Task Actions
    $(document).on('click', '.task-btn.edit', function() {
        const taskId = $(this).data('task-id');
        if (typeof loadTaskForEdit === 'function') {
            loadTaskForEdit(taskId);
        }
    });
    
    $(document).on('click', '.task-btn.delete', function(e) {
        e.stopPropagation();
        const taskId = $(this).data('task-id');
        if (typeof deleteTask === 'function') {
            deleteTask(taskId);
        }
    });
    
    $(document).on('click', '.task-btn.complete', function(e) {
        e.stopPropagation();
        const taskId = $(this).data('task-id');
        const currentStatus = $(this).data('status');
        const newStatus = currentStatus === 'completed' ? 'pending' : 'completed';
        
        if (typeof updateTaskStatus === 'function') {
            updateTaskStatus(taskId, newStatus);
        }
    });
    
    // ============================================
    // Initialize
    // ============================================
    
    // Load tasks on page load
    loadTasks();
    
    console.log('Task Reminder UI initialized');
});

