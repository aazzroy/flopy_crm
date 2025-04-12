# URL Routing in Flopy CRM

This document explains the URL routing system used in Flopy CRM, which is based on a custom MVC framework.

## Overview

The routing system in Flopy CRM follows a convention-based approach, where URLs are mapped to controllers and methods based on a predictable pattern:

```
example.com/controller/method/param1/param2
```

- The first segment of the URL determines the controller to be loaded
- The second segment determines the method to be called on that controller
- Any remaining segments are passed as parameters to the method

## How Routing Works

### 1. Apache Rewrite Rules

The routing begins with Apache's mod_rewrite, which is configured in the `.htaccess` files:

**Root .htaccess:**
```
<IfModule mod_rewrite.c>
  RewriteEngine on
  RewriteRule ^$ public/ [L]
  RewriteRule (.*) public/$1 [L]
</IfModule>
```

This redirects all requests to the `public/` directory.

**Public .htaccess:**
```
<IfModule mod_rewrite.c>
  Options -Multiviews
  RewriteEngine On
  RewriteBase /flopy_crm/public
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteRule ^(.+)$ index.php?url=$1 [QSA,L]
</IfModule>
```

This captures the URL path and passes it to `index.php` as a query parameter called `url`.

### 2. URL Parsing in Core.php

The `Core.php` class is responsible for parsing the URL and loading the appropriate controller and method:

```php
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
```

The `__construct()` method of the Core class then:
1. Gets the URL components using `getUrl()`
2. Determines the controller to load based on the first segment
3. Loads the controller file and instantiates the controller class
4. Determines the method to call based on the second segment
5. Gathers any remaining segments as parameters
6. Calls the method with the parameters

## URL Structure Examples

### Default Route

When no URL segments are provided, the system uses the default controller and method:

```
example.com/flopy_crm/ 
```

This loads:
- Controller: `DashboardController` (default)
- Method: `index` (default)

### Basic Routes

```
example.com/flopy_crm/contacts
```

This loads:
- Controller: `ContactsController`
- Method: `index` (default)

```
example.com/flopy_crm/contacts/view/5
```

This loads:
- Controller: `ContactsController`
- Method: `view`
- Parameters: `[5]`

### Complex Routes

```
example.com/flopy_crm/deals/filter/open/high/2023-01-01/2023-12-31
```

This loads:
- Controller: `DealsController`
- Method: `filter`
- Parameters: `['open', 'high', '2023-01-01', '2023-12-31']`

## Controller and Method Naming Conventions

- Controller file names are PascalCase and end with "Controller.php": `ContactsController.php`
- Controller class names match the file name: `class ContactsController`
- Method names are camelCase: `public function viewContact()`

## Access Control

The routing system can be extended with access control middleware by adding checking logic to the controller's constructor or individual methods:

```php
public function __construct() {
    // Check if user is logged in
    if(!$this->isLoggedIn()) {
        redirect('users/login');
    }
    
    // Check user role for certain controllers
    if(get_class($this) == 'AdminController' && $_SESSION['user_role'] != 'admin') {
        redirect('dashboard/accessDenied');
    }
}
```

## Creating Custom Routes

To create a custom route in Flopy CRM:

1. Create a new controller file in `app/controllers/`
2. Define your controller class extending the base `Controller` class
3. Add methods for each action you want to support
4. Create view files in `app/views/yourcontroller/`

Example:

```php
<?php
class ProductsController extends Controller {
    public function index() {
        $this->view('products/index');
    }
    
    public function view($id) {
        $this->view('products/view', ['id' => $id]);
    }
}
```

With this controller, you can now access:
- `example.com/flopy_crm/products`
- `example.com/flopy_crm/products/view/123`

## Troubleshooting Routes

If your routes are not working as expected:

1. Check that mod_rewrite is enabled in Apache
2. Verify that the .htaccess files are present and correct
3. Ensure your controller and method names follow the naming conventions
4. Check for typos in URL paths
5. Verify that the method exists in the controller 