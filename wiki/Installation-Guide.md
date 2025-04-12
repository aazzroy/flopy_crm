# Installation Guide

This guide provides step-by-step instructions for installing and configuring Flopy CRM.

## Requirements

Before installing Flopy CRM, ensure your server meets the following requirements:

- PHP 7.4+
- MySQL 5.7+
- Apache web server with mod_rewrite enabled
- PHP Extensions:
  - PDO (with MySQL driver)
  - mbstring
  - cURL
  - GD Library
  - OpenSSL

## Installation Steps

### 1. Download the Application

Clone the repository to your web server's document root:

```bash
git clone https://github.com/aazzroy/flopy_crm.git
```

Alternatively, you can download the [latest release](https://github.com/aazzroy/flopy_crm/releases) as a ZIP file and extract it.

### 2. Create the Database

Create a new MySQL database for Flopy CRM:

```sql
CREATE DATABASE flopy_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Import the Database Schema

Import the database schema from the included `database.sql` file:

```bash
mysql -u username -p flopy_crm < database.sql
```

### 4. Configure the Database Connection

Edit the `config/database.php` file to set your database connection details:

```php
// Database Configuration
define('DB_HOST', 'localhost');     // Replace with your database host
define('DB_USER', 'your_username'); // Replace with your database username
define('DB_PASS', 'your_password'); // Replace with your database password
define('DB_NAME', 'flopy_crm');     // Replace with your database name
```

### 5. Set Directory Permissions

Ensure the web server has the proper permissions to write to certain directories:

```bash
chmod -R 755 public/
chmod -R 755 app/
chmod -R 777 public/uploads/
```

### 6. Configure Web Server

#### Apache Configuration

Ensure that mod_rewrite is enabled and that .htaccess files are allowed. Your virtual host configuration should look similar to this:

```apache
<VirtualHost *:80>
    ServerName crm.example.com
    DocumentRoot /path/to/flopy_crm
    
    <Directory /path/to/flopy_crm>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/crm_error.log
    CustomLog ${APACHE_LOG_DIR}/crm_access.log combined
</VirtualHost>
```

### 7. Access the Application

After completing the above steps, open your web browser and navigate to the URL where you installed Flopy CRM:

```
http://your-domain.com/flopy_crm/
```

Or if you configured a virtual host:

```
http://crm.example.com/
```

### 8. Log In

Use the default login credentials to access the application:

- **Email**: admin@flopy.com
- **Password**: admin123

**Important**: After logging in, immediately change the default password for security reasons.

## Troubleshooting

### Common Issues

#### 404 Not Found Error

- Check that mod_rewrite is enabled in Apache
- Verify that .htaccess files are present and correct
- Ensure the AllowOverride directive is set to All in your Apache configuration

#### Database Connection Error

- Verify database credentials in `config/database.php`
- Check that the MySQL server is running
- Ensure the database exists

#### Permission Denied Error

- Check file permissions on the uploads directory
- Ensure the web server has write permissions to required directories

For more detailed troubleshooting, refer to the [Troubleshooting Guide](Troubleshooting).

## Next Steps

After installation, you should:

1. [Configure the application settings](User-Guide#settings)
2. [Set up your user account](User-Guide#user-management)
3. [Import or add your contacts](User-Guide#contact-management)
4. [Configure email templates](User-Guide#email-templates)

For additional help, visit our [User Guide](User-Guide). 