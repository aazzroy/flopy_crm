<?php
/**
 * Database Migration - Create Tables
 * 
 * This file contains the SQL queries to create all the tables needed for the Flopy CRM system.
 */

// Load config
require_once '../../config/config.php';
require_once '../../config/database.php';

// Create database connection
$db = new Database();

// Create users table
$db->query("CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL DEFAULT 2,
    `theme` VARCHAR(50) DEFAULT 'light',
    `phone` VARCHAR(50) NULL,
    `position` VARCHAR(100) NULL,
    `profile_image` VARCHAR(255) DEFAULT 'default.jpg',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `oauth_provider` VARCHAR(50) NULL,
    `oauth_id` VARCHAR(255) NULL,
    `api_token` VARCHAR(100) NULL,
    `api_token_expiry` DATETIME NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create roles table
$db->query("CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Insert default roles
$db->query("INSERT IGNORE INTO `roles` (`id`, `name`, `description`) VALUES 
    (1, 'Admin', 'Administrator with full access'),
    (2, 'Agent', 'Regular user with limited access'),
    (3, 'Client', 'External client with minimal access')");
$db->execute();

// Create contacts table
$db->query("CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NULL,
    `phone` VARCHAR(50) NULL,
    `mobile` VARCHAR(50) NULL,
    `company` VARCHAR(100) NULL,
    `position` VARCHAR(100) NULL,
    `website` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `city` VARCHAR(100) NULL,
    `state` VARCHAR(100) NULL,
    `zip` VARCHAR(20) NULL,
    `country` VARCHAR(100) NULL,
    `notes` TEXT NULL,
    `lead_source` VARCHAR(100) NULL,
    `lead_status` VARCHAR(100) NULL,
    `lead_score` INT NULL,
    `owner_id` INT NULL,
    `avatar` VARCHAR(255) DEFAULT 'default_contact.jpg',
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create tags table
$db->query("CREATE TABLE IF NOT EXISTS `tags` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `color` VARCHAR(20) DEFAULT '#4F46E5',
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create contact_tags pivot table
$db->query("CREATE TABLE IF NOT EXISTS `contact_tags` (
    `contact_id` INT NOT NULL,
    `tag_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`contact_id`, `tag_id`),
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`tag_id`) REFERENCES `tags`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create interactions table
$db->query("CREATE TABLE IF NOT EXISTS `interactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT NOT NULL,
    `type` ENUM('call', 'email', 'meeting', 'task', 'note', 'other') NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `date` DATETIME NOT NULL,
    `duration` INT NULL,
    `status` ENUM('planned', 'completed', 'canceled') DEFAULT 'planned',
    `outcome` TEXT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create deals table
$db->query("CREATE TABLE IF NOT EXISTS `deals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) DEFAULT 'USD',
    `stage` ENUM('lead', 'qualified', 'proposal', 'negotiation', 'closed-won', 'closed-lost') NOT NULL,
    `probability` INT DEFAULT 0,
    `expected_close_date` DATE NULL,
    `actual_close_date` DATE NULL,
    `owner_id` INT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create events table (for calendar)
$db->query("CREATE TABLE IF NOT EXISTS `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `start` DATETIME NOT NULL,
    `end` DATETIME NOT NULL,
    `all_day` BOOLEAN DEFAULT 0,
    `location` VARCHAR(255) NULL,
    `color` VARCHAR(20) DEFAULT '#4F46E5',
    `user_id` INT NOT NULL,
    `contact_id` INT NULL,
    `reminder` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`contact_id`) REFERENCES `contacts`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create files table
$db->query("CREATE TABLE IF NOT EXISTS `files` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(255) NOT NULL,
    `file_type` VARCHAR(100) NULL,
    `file_size` INT NULL,
    `related_type` ENUM('contact', 'deal', 'interaction') NOT NULL,
    `related_id` INT NOT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create email templates table
$db->query("CREATE TABLE IF NOT EXISTS `email_templates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `body` TEXT NOT NULL,
    `is_default` BOOLEAN DEFAULT 0,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create reminders table
$db->query("CREATE TABLE IF NOT EXISTS `reminders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `due_date` DATETIME NOT NULL,
    `priority` ENUM('low', 'medium', 'high') DEFAULT 'medium',
    `status` ENUM('pending', 'completed', 'dismissed') DEFAULT 'pending',
    `user_id` INT NOT NULL,
    `related_type` ENUM('contact', 'deal', 'interaction', 'event', 'task') NULL,
    `related_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create activity_log table
$db->query("CREATE TABLE IF NOT EXISTS `activity_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(255) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Create settings table
$db->query("CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(100) DEFAULT 'general',
    `is_public` BOOLEAN DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
$db->execute();

// Insert default settings
$db->query("INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`, `setting_group`, `is_public`) VALUES 
    ('site_name', 'Flopy CRM', 'general', 1),
    ('company_name', 'Flopy Inc.', 'general', 1),
    ('company_email', 'contact@flopy.com', 'general', 1),
    ('company_phone', '+1234567890', 'general', 1),
    ('company_address', '123 Main St, Anytown, USA', 'general', 1),
    ('date_format', 'Y-m-d', 'general', 1),
    ('time_format', 'H:i', 'general', 1),
    ('language', 'en', 'general', 1),
    ('timezone', 'UTC', 'general', 1),
    ('currency', 'USD', 'general', 1),
    ('default_theme', 'light', 'appearance', 1),
    ('primary_color', '#4F46E5', 'appearance', 1),
    ('secondary_color', '#EC4899', 'appearance', 1),
    ('logo_path', 'images/logo.png', 'appearance', 1),
    ('favicon_path', 'images/favicon.ico', 'appearance', 1),
    ('items_per_page', '10', 'general', 1),
    ('enable_registration', '0', 'security', 0),
    ('max_login_attempts', '5', 'security', 0),
    ('login_timeout', '300', 'security', 0),
    ('session_timeout', '1800', 'security', 0),
    ('maintenance_mode', '0', 'system', 0),
    ('system_email', 'system@flopy.com', 'system', 0),
    ('smtp_host', '', 'email', 0),
    ('smtp_port', '587', 'email', 0),
    ('smtp_username', '', 'email', 0),
    ('smtp_password', '', 'email', 0),
    ('smtp_encryption', 'tls', 'email', 0)");
$db->execute();

// Create admin user
$password = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]);
$db->query("INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role_id`, `status`) VALUES 
    (1, 'Admin User', 'admin@flopy.com', :password, 1, 'active')");
$db->bind(':password', $password);
$db->execute();

echo "Database migration completed successfully!"; 