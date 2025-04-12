<?php
/**
 * Security Helper
 * 
 * This file contains helper functions for security-related operations.
 */

/**
 * Sanitize data to prevent XSS attacks
 * 
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    if(is_array($data)) {
        foreach($data as $key => $value) {
            $data[$key] = sanitize($value);
        }
    } else {
        $data = htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    
    return $data;
}

/**
 * Generate a CSRF token
 * 
 * @return string CSRF token
 */
function generateCsrfToken() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token Token to validate
 * @return boolean True if valid, false otherwise
 */
function validateCsrfToken($token) {
    if(!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Check if token is valid
    if(hash_equals($_SESSION['csrf_token'], $token)) {
        // Check if token is expired
        if(time() - $_SESSION['csrf_token_time'] < CSRF_TOKEN_LIFETIME) {
            return true;
        }
    }
    
    return false;
}

/**
 * Generate a random token
 * 
 * @param int $length Token length
 * @return string Random token
 */
function generateRandomToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password using bcrypt
 * 
 * @param string $password Password to hash
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
}

/**
 * Verify password against hash
 * 
 * @param string $password Password to verify
 * @param string $hash Hash to verify against
 * @return boolean True if password matches hash, false otherwise
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if password needs rehash
 * 
 * @param string $hash Hash to check
 * @return boolean True if needs rehash, false otherwise
 */
function passwordNeedsRehash($hash) {
    return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
}

/**
 * Generate a secure random string
 * 
 * @param int $length String length
 * @param string $chars Characters to use
 * @return string Random string
 */
function generateRandomString($length = 10, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    $string = '';
    $max = strlen($chars) - 1;
    
    for($i = 0; $i < $length; $i++) {
        $string .= $chars[random_int(0, $max)];
    }
    
    return $string;
}

/**
 * Check for rate limiting
 * 
 * @param string $key Rate limit key (e.g. login, register)
 * @param int $limit Number of attempts allowed
 * @param int $seconds Time window in seconds
 * @return boolean True if rate limited, false otherwise
 */
function isRateLimited($key, $limit = 5, $seconds = 300) {
    $rateKey = 'rate_limit_' . $key . '_' . $_SERVER['REMOTE_ADDR'];
    
    if(!isset($_SESSION[$rateKey])) {
        $_SESSION[$rateKey] = [
            'attempts' => 0,
            'time' => time()
        ];
    }
    
    // Reset if time window has passed
    if(time() - $_SESSION[$rateKey]['time'] > $seconds) {
        $_SESSION[$rateKey] = [
            'attempts' => 0,
            'time' => time()
        ];
    }
    
    // Check if limit exceeded
    if($_SESSION[$rateKey]['attempts'] >= $limit) {
        return true;
    }
    
    // Increment attempts
    $_SESSION[$rateKey]['attempts']++;
    
    return false;
}

/**
 * Log activity
 * 
 * @param string $activity Activity description
 * @param int $userId User ID (optional)
 * @param string $ipAddress IP address (optional)
 * @return boolean True if logged, false otherwise
 */
function logActivity($activity, $userId = null, $ipAddress = null) {
    // Use current user ID if not provided
    if($userId === null && isset($_SESSION['user_id'])) {
        $userId = $_SESSION['user_id'];
    }
    
    // Use current IP address if not provided
    if($ipAddress === null) {
        $ipAddress = $_SERVER['REMOTE_ADDR'];
    }
    
    // TODO: Log to database
    // This is just a placeholder for now
    if(LOG_LEVEL >= 3) {
        error_log(date('Y-m-d H:i:s') . ' | User: ' . ($userId ?? 'Guest') . 
                 ' | IP: ' . $ipAddress . ' | ' . $activity);
    }
    
    return true;
} 