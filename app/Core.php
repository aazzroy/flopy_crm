<?php
/**
 * Core Application Class
 * 
 * This is the main application class that handles routing, URL mapping,
 * and loading the appropriate controllers and methods.
 */
class Core {
    // Default controller, method and params
    protected $currentController = 'Dashboard';
    protected $currentMethod = 'index';
    protected $params = [];
    
    /**
     * Constructor - Initialize core application
     */
    public function __construct() {
        $url = $this->getUrl();
        
        // Set controller from URL if exists
        if(isset($url[0]) && file_exists('../app/controllers/' . ucwords($url[0]) . 'Controller.php')) {
            // Set as controller
            $this->currentController = ucwords($url[0]);
            // Unset 0 index
            unset($url[0]);
        }
        
        // Require the controller
        require_once '../app/controllers/' . $this->currentController . 'Controller.php';
        
        // Instantiate controller class
        $this->currentController = $this->currentController . 'Controller';
        $this->currentController = new $this->currentController;
        
        // Check for second part of URL (method)
        if(isset($url[1])) {
            // Check if method exists in controller
            if(method_exists($this->currentController, $url[1])) {
                $this->currentMethod = $url[1];
                // Unset 1 index
                unset($url[1]);
            }
        }
        
        // Get params - Any values left over in url array
        $this->params = $url ? array_values($url) : [];
        
        // Call a callback with array of params
        call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
    }
    
    /**
     * Parse URL and return components
     * 
     * @return array URL components
     */
    public function getUrl() {
        if(isset($_GET['url'])) {
            // Trim trailing slash, sanitize URL
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            // Split into array
            $url = explode('/', $url);
            return $url;
        }
        
        return [];
    }
} 