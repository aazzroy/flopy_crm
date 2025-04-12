<?php
/**
 * Base Controller Class
 * 
 * This is the base controller class that loads models and views.
 * All other controllers extend this class.
 */
class Controller {
    /**
     * Load Model
     * 
     * @param string $model Model name
     * @return object Model instance
     */
    public function model($model) {
        // Require model file
        require_once '../app/models/' . $model . '.php';
        
        // Instantiate model
        return new $model();
    }
    
    /**
     * Load View
     * 
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return void
     */
    public function view($view, $data = []) {
        // Check for view file
        if(file_exists('../app/views/' . $view . '.php')) {
            require_once '../app/views/' . $view . '.php';
        } else {
            // View does not exist
            die('View does not exist');
        }
    }
    
    /**
     * Render JSON Response
     * 
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @return void
     */
    public function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Redirect to URL
     * 
     * @param string $url URL to redirect to
     * @return void
     */
    public function redirect($url) {
        header('Location: ' . URLROOT . '/' . $url);
        exit;
    }
    
    /**
     * Set Flash Message
     * 
     * @param string $name Message name
     * @param string $message Message content
     * @param string $class Message class (success, danger, etc.)
     * @return void
     */
    public function setFlash($name, $message, $class = 'success') {
        $_SESSION['flash'][$name] = [
            'message' => $message,
            'class' => $class
        ];
    }
    
    /**
     * Check if user is logged in
     * 
     * @return boolean True if logged in, false otherwise
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Get logged in user
     * 
     * @return object|bool User object or false if not logged in
     */
    public function getUser() {
        if($this->isLoggedIn()) {
            $userModel = $this->model('User');
            return $userModel->getUserById($_SESSION['user_id']);
        }
        return false;
    }
    
    /**
     * Generate CSRF Token
     * 
     * @return string CSRF Token
     */
    public function generateCsrfToken() {
        if(!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF Token
     * 
     * @param string $token CSRF Token to validate
     * @return boolean True if valid, false otherwise
     */
    public function validateCsrfToken($token) {
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
} 