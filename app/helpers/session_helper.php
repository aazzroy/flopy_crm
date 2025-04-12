<?php
/**
 * Session Helper
 * 
 * This file contains helper functions for managing sessions and flash messages.
 */

/**
 * Flash message helper
 * 
 * EXAMPLE: flash('register_success', 'You are now registered!', 'alert alert-success');
 * DISPLAY IN VIEW: echo flash('register_success');
 * 
 * @param string $name
 * @param string $message
 * @param string $class
 * @return string
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if(!empty($name)) {
        // If setting a message
        if(!empty($message) && empty($_SESSION['flash'][$name])) {
            // Unset any existing flash with this name
            if(!empty($_SESSION['flash'][$name])) {
                unset($_SESSION['flash'][$name]);
            }
            
            // Set the flash message
            $_SESSION['flash'][$name] = [
                'message' => $message,
                'class' => $class
            ];
        } 
        // If getting a message
        elseif(!empty($_SESSION['flash'][$name]) && empty($message)) {
            // Create output
            $output = '<div class="' . $_SESSION['flash'][$name]['class'] . ' alert-dismissible fade show" role="alert">';
            $output .= $_SESSION['flash'][$name]['message'];
            $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            $output .= '</div>';
            
            // Unset the flash message
            unset($_SESSION['flash'][$name]);
            
            // Return output
            return $output;
        }
    }
    
    return '';
}

/**
 * Check if user is logged in
 * 
 * @return boolean
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get user role
 * 
 * @return int|null
 */
function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Check if user has admin role
 * 
 * @return boolean
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_ADMIN;
}

/**
 * Get current user ID
 * 
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Set user session
 * 
 * @param object $user
 * @return void
 */
function setUserSession($user) {
    $_SESSION['user_id'] = $user->id;
    $_SESSION['user_email'] = $user->email;
    $_SESSION['user_name'] = $user->name;
    $_SESSION['user_role'] = $user->role_id;
    $_SESSION['user_theme'] = $user->theme ?? DEFAULT_THEME;
    $_SESSION['last_activity'] = time();
}

/**
 * Clear user session
 * 
 * @return void
 */
function clearUserSession() {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    unset($_SESSION['user_theme']);
    unset($_SESSION['last_activity']);
    session_regenerate_id(true);
}

/**
 * Check for session timeout
 * 
 * @return boolean
 */
function checkSessionTimeout() {
    if(isset($_SESSION['last_activity'])) {
        if(time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
            clearUserSession();
            flash('session_timeout', 'Your session has timed out. Please log in again.', 'alert alert-warning');
            return true;
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
    
    return false;
}

/**
 * Get current user theme
 * 
 * @return string
 */
function getUserTheme() {
    return isset($_SESSION['user_theme']) ? $_SESSION['user_theme'] : DEFAULT_THEME;
} 