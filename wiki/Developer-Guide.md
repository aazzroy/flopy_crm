# Developer Guide

This guide provides information for developers who want to extend or customize Flopy CRM.

## Architecture Overview

Flopy CRM follows the Model-View-Controller (MVC) architectural pattern:

- **Models**: Handle data operations and business logic
- **Views**: Handle presentation and user interface
- **Controllers**: Handle request processing and application flow

## Directory Structure

```
flopy_crm/
├── app/
│   ├── controllers/      # Controller classes
│   ├── models/           # Model classes
│   ├── views/            # View templates
│   ├── helpers/          # Helper functions
│   └── migrations/       # Database migrations
├── config/               # Configuration files
├── core/                 # Core framework classes
├── public/               # Publicly accessible files
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   ├── images/           # Image assets
│   ├── uploads/          # User uploaded files
│   ├── index.php         # Application entry point
│   └── .htaccess         # Apache rewrite rules
├── docs/                 # Documentation files
└── .htaccess             # Root Apache rewrite rules
```

## Getting Started with Development

### Setting Up a Development Environment

1. Clone the repository:
   ```bash
   git clone https://github.com/aazzroy/flopy_crm.git
   ```

2. Create a MySQL database:
   ```sql
   CREATE DATABASE flopy_crm_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Import the database schema:
   ```bash
   mysql -u username -p flopy_crm_dev < database.sql
   ```

4. Configure the database connection in `config/database.php`

5. Set up a virtual host for development (optional but recommended)

### Development Workflow

1. Create a feature branch:
   ```bash
   git checkout -b feature/my-new-feature
   ```

2. Make your changes
3. Test your changes
4. Commit your changes
5. Push to your fork
6. Create a pull request

## Extending the Application

### Adding a New Controller

1. Create a new file in `app/controllers/` named `YourNameController.php`:

```php
<?php
class ProductsController extends Controller {
    public function __construct() {
        // Check if user is logged in
        if(!$this->isLoggedIn()) {
            redirect('users/login');
        }
    }
    
    public function index() {
        $productModel = $this->model('Product');
        $products = $productModel->getProducts();
        
        $this->view('products/index', [
            'products' => $products
        ]);
    }
    
    public function view($id) {
        $productModel = $this->model('Product');
        $product = $productModel->getProductById($id);
        
        if(!$product) {
            redirect('products');
        }
        
        $this->view('products/view', [
            'product' => $product
        ]);
    }
    
    public function add() {
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate input
            
            // Save product
            $productModel = $this->model('Product');
            if($productModel->addProduct($_POST)) {
                // Set flash message
                $this->setFlash('product_success', 'Product added successfully');
                redirect('products');
            } else {
                die('Something went wrong');
            }
        } else {
            // Load form view
            $this->view('products/add');
        }
    }
}
```

2. Create corresponding view files in `app/views/products/`:
   - `index.php`: List all products
   - `view.php`: View a single product
   - `add.php`: Form to add a new product

### Adding a New Model

Create a new file in `app/models/` named `Product.php`:

```php
<?php
class Product {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    public function getProducts() {
        $this->db->query('SELECT * FROM products ORDER BY created_at DESC');
        return $this->db->resultSet();
    }
    
    public function getProductById($id) {
        $this->db->query('SELECT * FROM products WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    public function addProduct($data) {
        $this->db->query('INSERT INTO products (name, description, price, created_by) VALUES (:name, :description, :price, :created_by)');
        
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description']);
        $this->db->bind(':price', $data['price']);
        $this->db->bind(':created_by', $_SESSION['user_id']);
        
        // Execute
        if($this->db->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
```

### Creating Database Migrations

Database schema changes should be done through migrations. Create a new migration file in `app/migrations/`:

```php
<?php
// app/migrations/create_products_table.php

/**
 * Create Products Table Migration
 */
class CreateProductsTable {
    private $db;
    
    public function __construct() {
        $this->db = new Database;
    }
    
    public function up() {
        $sql = "CREATE TABLE IF NOT EXISTS `products` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `price` DECIMAL(10,2) NOT NULL,
            `created_by` INT NOT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        return $this->db->query($sql) && $this->db->execute();
    }
    
    public function down() {
        $sql = "DROP TABLE IF EXISTS `products`;";
        
        return $this->db->query($sql) && $this->db->execute();
    }
}
```

To run the migration:

```php
$migration = new CreateProductsTable();
$migration->up();
```

## Customizing the UI

### CSS Customization

Custom CSS can be added to:
- `public/css/style.css`: For global styles
- `public/css/custom.css`: For your customizations (create if it doesn't exist)

### JavaScript Customization

Custom JavaScript can be added to:
- `public/js/app.js`: For global scripts
- `public/js/custom.js`: For your customizations (create if it doesn't exist)

### Layout Customization

To customize the main layout:

1. Edit `app/views/layouts/default.php`
2. Or create a new layout file and use it in your controllers:

```php
public function index() {
    $this->view('products/index', [
        'products' => $products,
        'layout' => 'custom'  // Will use app/views/layouts/custom.php
    ]);
}
```

## Working with the API

See the [API Reference](API-Reference) for detailed information about the API.

## Best Practices

### Coding Standards

- Follow PSR-1, PSR-12, and PSR-4 coding standards
- Use meaningful variable and function names
- Write descriptive comments and documentation
- Keep functions and methods short and focused on a single task
- Use proper indentation (4 spaces)
- Add PHPDoc blocks for all classes, methods, and functions

### Security Best Practices

- Always validate and sanitize user input
- Use prepared statements for all database queries
- Use CSRF protection for forms
- Apply proper access control to all controllers and methods
- Never store sensitive information in client-side code
- Keep dependencies updated

### Performance Optimization

- Cache frequently accessed data
- Optimize database queries with proper indexes
- Minimize database calls
- Use pagination for large datasets
- Optimize asset loading (CSS/JS)

## Troubleshooting

### Common Development Issues

- **404 Not Found Error**: Check controller and method names follow conventions
- **Database Connection Error**: Verify database credentials in config
- **Internal Server Error**: Check PHP error logs for details
- **CSRF Token Mismatch**: Ensure CSRF token is included in all forms
- **Permission Denied**: Check file permissions

### Debugging Tips

- Enable error reporting in development:
  ```php
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  error_reporting(E_ALL);
  ```
  
- Use `var_dump()` or `print_r()` to inspect variables
- Check the PHP error logs
- Use browser developer tools for front-end issues

## Contributing

Please see the [Contributing Guide](Contributing-Guide) for information on how to contribute to the project.

## Additional Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [JavaScript MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/JavaScript)
