<?php
/**
 * Main Configuration File
 * 
 * This file contains the main configuration settings for the Flopy CRM system.
 * It defines constants and settings used throughout the application.
 */

// URL Root
define('URLROOT', 'http://localhost/flopy_crm');

// Site Name
define('SITENAME', 'Flopy CRM');

// App Version
define('APPVERSION', '1.0.0');

// App Root
define('APPROOT', dirname(dirname(__FILE__)));

// Public Root
define('PUBLICROOT', URLROOT . '/public');

// Session Configuration
define('SESSION_TIMEOUT', 1800); // 30 minutes
define('SESSION_SECURE', false); // Set to true in production with HTTPS
define('SESSION_HTTPONLY', true);

// CSRF Token Lifetime (in seconds)
define('CSRF_TOKEN_LIFETIME', 3600); // 1 hour

// File Upload Limits
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,csv');

// API Configuration
define('API_KEY_LIFETIME', 86400); // 24 hours
define('API_RATE_LIMIT', 100); // requests per hour

// Date & Time Format
define('DATE_FORMAT', 'Y-m-d');
define('TIME_FORMAT', 'H:i:s');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Logging Level (0=None, 1=Errors, 2=Warnings, 3=Info, 4=Debug)
define('LOG_LEVEL', 4); // Debug in development, set to 1 or 2 in production

// Pagination
define('ITEMS_PER_PAGE', 10);

// Authentication
define('MIN_PASSWORD_LENGTH', 8);
define('PASSWORD_HASH_COST', 12); // bcrypt cost factor
define('AUTH_TOKEN_LIFETIME', 86400); // 24 hours

// Role IDs
define('ROLE_ADMIN', 1);
define('ROLE_AGENT', 2);
define('ROLE_CLIENT', 3);

// Theme Settings
define('DEFAULT_THEME', 'light');

// Time zone
date_default_timezone_set('UTC'); 