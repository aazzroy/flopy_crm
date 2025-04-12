# Flopy CRM

Flopy CRM is a lightweight Customer Relationship Management system built with PHP using a custom MVC framework. It provides a complete solution for managing customer relationships, sales pipelines, and business interactions.

## Features

- **Contact Management**: Store and manage customer contact information
  - Contact profiles with detailed information
  - Contact activity history
  - Custom tags and categorization
  - Contact ownership and assignment
  
- **Deal Tracking**: Track sales pipeline and deal status
  - Visual sales pipeline
  - Deal stages (lead, qualified, proposal, negotiation, closed)
  - Deal valuation and probability tracking
  - Expected close dates and forecasting
  
- **Interaction Logging**: Log calls, emails, meetings, and tasks
  - Multi-type interaction tracking
  - Scheduled and completed interactions
  - Outcome recording
  - Duration tracking
  
- **Calendar Events**: Schedule and manage appointments
  - Event scheduling with contacts
  - Reminders and notifications
  - All-day event support
  - Location tracking
  
- **Reminders**: Set reminders for follow-ups and tasks
  - Priority-based reminders
  - Status tracking (pending, completed, dismissed)
  - Relate reminders to contacts, deals, or events
  
- **Dashboard**: Visual analytics of CRM activities
  - Activity summaries
  - Deal pipeline visualization
  - Recent interactions
  - Upcoming events and reminders
  
- **User Management**: Role-based access control
  - Admin, Agent, and Client roles
  - User profiles with customizations
  - Theme preferences
  - Activity logging

## Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache web server with mod_rewrite enabled
- PHP Extensions:
  - PDO (with MySQL driver)
  - mbstring
  - cURL
  - GD Library
  - OpenSSL

## Installation

1. Clone the repository to your web server's document root:
   ```
   git clone https://github.com/aazzroy/flopy_crm.git
   ```
   
2. Create a new MySQL database:
   ```
   CREATE DATABASE flopy_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
   
3. Import the database schema from the included `database.sql` file:
   ```
   mysql -u username -p flopy_crm < database.sql
   ```
   
4. Configure your database connection in `config/database.php`:
   ```php
   // Database Configuration
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'flopy_crm');
   ```
   
5. Ensure proper permissions on the application directories:
   ```
   chmod -R 755 public/
   chmod -R 755 app/
   ```
   
6. Configure your web server to point to the application's root directory
   
7. Access the application through your web browser

## Default Login

- **Email**: admin@flopy.com
- **Password**: admin123

## Documentation

Detailed documentation is available in the `docs/` directory:

- [Main Documentation](docs/index.md) - Complete system overview
- [URL Routing](docs/routing.md) - Detailed explanation of the routing system
- [Database Schema](docs/database.md) - Database structure and relationships

## Application Structure

### Directory Structure

- **app/**: Application code
  - **controllers/**: Controller classes that handle user requests
    - `DashboardController.php`: Dashboard display logic
    - `ContactsController.php`: Contact management
    - `DealsController.php`: Deal pipeline management
    - `UsersController.php`: User account management
    - `InteractionsController.php`: Interaction logging
    - `EventsController.php`: Calendar event management
    - `SettingsController.php`: Application settings
  - **models/**: Model classes that interact with the database
    - `User.php`: User account operations
    - `Contact.php`: Contact data operations
    - `Deal.php`: Deal and pipeline operations
    - `Interaction.php`: Interaction data handling
    - `Event.php`: Calendar event operations
    - `Setting.php`: Application settings storage
    - `Tag.php`: Tag management
  - **views/**: View templates that render the UI
    - `dashboard/`: Dashboard views
    - `contacts/`: Contact management views
    - `deals/`: Deal management views
    - `users/`: User management views
    - `interactions/`: Interaction views
    - `events/`: Calendar views
    - `settings/`: Settings views
    - `layouts/`: Layout templates
    - `includes/`: Reusable UI components
  - **helpers/**: Helper functions and utilities
    - `session_helper.php`: Session management
    - `url_helper.php`: URL manipulation
    - `security_helper.php`: Security functions
    - `date_helper.php`: Date formatting
    - `form_helper.php`: Form generation
  - **migrations/**: Database migrations
    - `create_database.php`: Initial database setup
- **config/**: Configuration files
  - `config.php`: Application configuration
  - `database.php`: Database connection settings
- **core/**: Core framework classes
  - `Router.php`: URL routing
  - `Request.php`: HTTP request handling
  - `Response.php`: HTTP response generation
  - `Session.php`: Session management
  - `Database.php`: Database abstraction
- **public/**: Publicly accessible files
  - **css/**: Stylesheets
    - `style.css`: Main stylesheet
    - `bootstrap.min.css`: Bootstrap framework
    - `dashboard.css`: Dashboard specific styles
  - **js/**: JavaScript files
    - `app.js`: Main application script
    - `chart.min.js`: Chart generation
    - `bootstrap.bundle.min.js`: Bootstrap scripts
  - **images/**: Image assets
    - `logo.png`: Application logo
    - `favicon.ico`: Browser favicon
    - `default.jpg`: Default user avatar
    - `default_contact.jpg`: Default contact avatar
  - **uploads/**: User uploaded files
    - `contacts/`: Contact related uploads
    - `users/`: User profile images
    - `documents/`: Document uploads
  - `index.php`: Application entry point
  - `.htaccess`: Apache rewrite rules
- **docs/**: Documentation files
  - `index.md`: Main documentation
  - `routing.md`: URL routing documentation
  - `database.md`: Database schema documentation

### Core Application Flow

1. All requests are routed through `public/index.php`
2. The `.htaccess` files handle URL rewriting
3. The `Core.php` class analyzes the URL and loads the appropriate controller
4. Controllers process the request and load the required models
5. Models interact with the database and return data
6. Controllers pass data to views
7. Views render the final HTML output

## URL Structure

The application uses a custom router that follows this pattern:
- `example.com/controller/method/param1/param2`

Examples:
- `example.com/contacts/view/5` - View contact with ID 5
- `example.com/deals/edit/10` - Edit deal with ID 10
- `example.com/users/profile` - View current user's profile
- `example.com/interactions/add/contact/5` - Add interaction for contact ID 5
- `example.com/events/calendar/2023/05` - View calendar for May 2023
- `example.com/settings/update` - Update application settings

## Database Schema

The database consists of multiple related tables:
- `users`: User accounts and authentication
- `roles`: User role definitions
- `contacts`: Customer and prospect information
- `tags`: Tags for categorizing contacts
- `contact_tags`: Many-to-many relationship between contacts and tags
- `interactions`: Logged interactions with contacts
- `deals`: Sales pipeline and opportunities
- `events`: Calendar events and appointments
- `files`: Uploaded files and documents
- `email_templates`: Templates for email communications
- `reminders`: Reminders and notifications
- `activity_log`: System activity audit trail
- `settings`: Application configuration settings

For complete database schema details, see the `database.sql` file or the [Database Schema Documentation](docs/database.md).

## Technologies Used

- **Backend**: PHP 7.4+ with custom MVC framework
- **Database**: MySQL 5.7+ with PDO
- **Frontend**: 
  - Bootstrap 5 for responsive UI
  - JavaScript/jQuery for interactive elements
  - Chart.js for dashboard visualizations
- **Security**:
  - Password hashing with bcrypt
  - CSRF protection
  - Input sanitization
  - Session security
- **Other**:
  - RESTful API endpoints for integration
  - AJAX for asynchronous data loading
  - Custom PDF generation for reports
  
## Development

### Adding a New Controller

1. Create a new file in `app/controllers/` named `YourNameController.php`
2. Extend the base `Controller` class
3. Add methods for each action
4. Create corresponding views in `app/views/yourname/`

Example:
```php
<?php
class ProductsController extends Controller {
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
        
        $this->view('products/view', [
            'product' => $product
        ]);
    }
}
```

### Adding a New Model

1. Create a new file in `app/models/` named `YourName.php`
2. Create a class with methods to interact with the database

Example:
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
}
```

## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Support

For support, please contact admin@flopy.com or open an issue on the GitHub repository. 