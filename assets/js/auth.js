/**
 * Authentication JavaScript
 * Handles login and registration
 */

$(document).ready(function() {
    'use strict';
    
    // ============================================
    // Password Toggle
    // ============================================
    
    function togglePassword(inputId, buttonId) {
        $(buttonId).on('click', function() {
            const input = $(inputId);
            const icon = $(this).find('i');
            
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
    }
    
    // Login password toggle
    if ($('#toggleLoginPassword').length) {
        togglePassword('#loginPassword', '#toggleLoginPassword');
    }
    
    // Register password toggles
    if ($('#toggleRegisterPassword').length) {
        togglePassword('#registerPassword', '#toggleRegisterPassword');
    }
    
    if ($('#toggleConfirmPassword').length) {
        togglePassword('#registerConfirmPassword', '#toggleConfirmPassword');
    }
    
    // ============================================
    // Login Form
    // ============================================
    
    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: $('#loginUsername').val().trim(),
            password: $('#loginPassword').val(),
            remember_me: $('#rememberMe').is(':checked') ? 1 : 0
        };
        
        // Validation
        if (!formData.username || !formData.password) {
            showError('loginError', 'Please fill in all required fields');
            return;
        }
        
        // Disable button
        const $btn = $('#btnLogin');
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Signing in...');
        
        // Hide previous errors
        $('#loginError').hide();
        
        // AJAX request
        $.ajax({
            url: 'api/auth.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'login',
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    // Success - redirect to dashboard
                    window.location.href = 'index.php';
                } else {
                    showError('loginError', response.message || 'Invalid credentials');
                    $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> Sign In');
                }
            },
            error: function(xhr, status, error) {
                console.error('Login error:', error, xhr.responseText);
                
                let errorMessage = 'An error occurred. Please try again.';
                
                // Try to parse error response
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {
                    // If JSON parse fails, use default message
                    if (xhr.status === 0) {
                        errorMessage = 'Cannot connect to server. Please check if PHP server is running.';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Server error. Please check database configuration.';
                    }
                }
                
                showError('loginError', errorMessage);
                $btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> Sign In');
            }
        });
    });
    
    // ============================================
    // Register Form
    // ============================================
    
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            username: $('#registerUsername').val().trim(),
            email: $('#registerEmail').val().trim(),
            full_name: $('#registerFullName').val().trim(),
            password: $('#registerPassword').val(),
            confirm_password: $('#registerConfirmPassword').val(),
            agree_terms: $('#agreeTerms').is(':checked')
        };
        
        // Validation
        if (!formData.username || !formData.email || !formData.password || !formData.confirm_password) {
            showError('registerError', 'Please fill in all required fields');
            return;
        }
        
        if (formData.username.length < 3) {
            showError('registerError', 'Username must be at least 3 characters');
            return;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(formData.username)) {
            showError('registerError', 'Username can only contain letters, numbers, and underscores');
            return;
        }
        
        if (!isValidEmail(formData.email)) {
            showError('registerError', 'Please enter a valid email address');
            return;
        }
        
        if (formData.password.length < 6) {
            showError('registerError', 'Password must be at least 6 characters');
            return;
        }
        
        if (formData.password !== formData.confirm_password) {
            showError('registerError', 'Passwords do not match');
            return;
        }
        
        if (!formData.agree_terms) {
            showError('registerError', 'Please agree to the terms and conditions');
            return;
        }
        
        // Disable button
        const $btn = $('#btnRegister');
        $btn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Creating account...');
        
        // Hide previous messages
        $('#registerError').hide();
        $('#registerSuccess').hide();
        
        // Remove confirm_password from data
        delete formData.confirm_password;
        delete formData.agree_terms;
        
        // AJAX request
        $.ajax({
            url: 'api/auth.php',
            method: 'POST',
            dataType: 'json',
            data: {
                action: 'register',
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    // Success - show message and redirect
                    showSuccess('registerSuccess', response.message || 'Account created successfully! Redirecting...');
                    $btn.prop('disabled', false).html('<i class="bi bi-person-plus"></i> Create Account');
                    
                    setTimeout(function() {
                        window.location.href = 'login.php?registered=1';
                    }, 2000);
                } else {
                    showError('registerError', response.message || 'Registration failed. Please try again.');
                    $btn.prop('disabled', false).html('<i class="bi bi-person-plus"></i> Create Account');
                }
            },
            error: function(xhr, status, error) {
                console.error('Register error:', error);
                let errorMsg = 'An error occurred. Please try again.';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                
                showError('registerError', errorMsg);
                $btn.prop('disabled', false).html('<i class="bi bi-person-plus"></i> Create Account');
            }
        });
    });
    
    // ============================================
    // Helper Functions
    // ============================================
    
    function showError(elementId, message) {
        const $error = $('#' + elementId);
        $error.html(message).show();
        
        // Scroll to error
        $('html, body').animate({
            scrollTop: $error.offset().top - 20
        }, 300);
    }
    
    function showSuccess(elementId, message) {
        const $success = $('#' + elementId);
        $success.html(message).show();
        
        // Scroll to success
        $('html, body').animate({
            scrollTop: $success.offset().top - 20
        }, 300);
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Check if redirected from registration
    if (window.location.search.includes('registered=1')) {
        showSuccess('loginError', 'Registration successful! Please login.');
        setTimeout(function() {
            $('#loginError').hide();
        }, 5000);
    }
});

