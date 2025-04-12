<?php
/**
 * Main Application Entry Point
 * 
 * This file is the main entry point to the application.
 * It loads all the necessary components and initializes the app.
 */

// Start session
session_start();

// Load Config
require_once '../config/config.php';
require_once '../config/database.php';

// Load Helpers
require_once '../app/helpers/session_helper.php';
require_once '../app/helpers/url_helper.php';
require_once '../app/helpers/security_helper.php';

// Autoload Core Libraries
require_once '../app/Core.php';
require_once '../app/Controller.php';

// Initialize Core Application
$init = new Core(); 