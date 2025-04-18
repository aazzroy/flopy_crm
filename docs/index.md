# Flopy CRM Documentation

Welcome to the Flopy CRM documentation. This page serves as a central reference point for all documentation related to the Flopy CRM system.

## Main Documentation

The primary documentation for Flopy CRM is available in the GitHub Wiki:

[**Access the Flopy CRM Wiki**](https://github.com/aazzroy/flopy_crm/wiki)

The wiki contains comprehensive documentation on all aspects of the system, including:

- [Installation Guide](https://github.com/aazzroy/flopy_crm/wiki/Installation-Guide)
- [User Guide](https://github.com/aazzroy/flopy_crm/wiki/User-Guide)
- [Developer Guide](https://github.com/aazzroy/flopy_crm/wiki/Developer-Guide)
- [API Reference](https://github.com/aazzroy/flopy_crm/wiki/API-Reference)
- [Database Schema](https://github.com/aazzroy/flopy_crm/wiki/Database-Schema)
- [URL Routing](https://github.com/aazzroy/flopy_crm/wiki/URL-Routing)
- [Troubleshooting](https://github.com/aazzroy/flopy_crm/wiki/Troubleshooting)
- [Contributing Guide](https://github.com/aazzroy/flopy_crm/wiki/Contributing-Guide)

## Additional Documentation

### Quick Start Guide

To get started quickly with Flopy CRM:

1. Clone the repository:
   ```bash
   git clone https://github.com/aazzroy/flopy_crm.git
   ```

2. Set up the database (MySQL):
   ```sql
   CREATE DATABASE flopy_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. Import the database schema:
   ```bash
   mysql -u username -p flopy_crm < database.sql
   ```

4. Configure the database connection in `config/database.php`

5. Access the application through your web browser and log in with:
   - Email: admin@flopy.com
   - Password: admin123

For detailed installation instructions, see the [Installation Guide](https://github.com/aazzroy/flopy_crm/wiki/Installation-Guide).

### System Requirements

- PHP 7.4+
- MySQL 5.7+
- Apache web server with mod_rewrite enabled
- PHP Extensions:
  - PDO (with MySQL driver)
  - mbstring
  - cURL
  - GD Library
  - OpenSSL

### Key Features

- **Contact Management**: Store and manage customer contact information
- **Deal Tracking**: Track sales pipeline and deal status
- **Interaction Logging**: Log calls, emails, meetings, and tasks
- **Calendar Events**: Schedule and manage appointments
- **Reminders**: Set reminders for follow-ups and tasks
- **Dashboard**: Visual analytics of CRM activities
- **User Management**: Role-based access control

## API Documentation

Flopy CRM provides a RESTful API for integration with other systems. The API is documented in detail in the [API Reference](https://github.com/aazzroy/flopy_crm/wiki/API-Reference).

Key features of the API include:

- Authentication via API tokens
- CRUD operations for contacts, deals, and other entities
- Webhooks for real-time event notifications
- Rate limiting for protection against abuse

## Developer Resources

For developers working with Flopy CRM, the following resources may be helpful:

- [GitHub Repository](https://github.com/aazzroy/flopy_crm)
- [Developer Guide](https://github.com/aazzroy/flopy_crm/wiki/Developer-Guide)
- [Contributing Guide](https://github.com/aazzroy/flopy_crm/wiki/Contributing-Guide)
- [Database Schema](https://github.com/aazzroy/flopy_crm/wiki/Database-Schema)
- [URL Routing System](https://github.com/aazzroy/flopy_crm/wiki/URL-Routing)

## Support

If you encounter any issues with Flopy CRM:

1. Check the [Troubleshooting Guide](https://github.com/aazzroy/flopy_crm/wiki/Troubleshooting)
2. Search for existing issues in the [GitHub Issues](https://github.com/aazzroy/flopy_crm/issues)
3. Contact support at admin@flopy.com
