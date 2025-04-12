# Database Schema Documentation

This document provides a detailed explanation of the database schema used in Flopy CRM.

## Overview

Flopy CRM uses a relational database (MySQL) with multiple tables to store and organize its data. The database follows a normalized design to minimize redundancy and improve data integrity.

## Table Relationships

The following diagram illustrates the relationships between the main tables:

```
users 1───────*┐
  │            │
  │            ↓
  │         contacts────*─── contact_tags ───*─── tags
  │            │ 
  │            ↓
  │         interactions
  │            ↑
  └────*─── deals
         │
         ↓
       events
```

## Table Descriptions

### Users Table

Stores user account information and authentication data.

**Structure:**
- `id`: Primary key
- `name`: User's full name
- `email`: User's email address (unique)
- `password`: Hashed password
- `role_id`: Reference to the role table
- `theme`: User's UI theme preference
- `phone`: Contact phone number
- `position`: Job position or title
- `profile_image`: Path to profile image
- `last_login`: Timestamp of last login
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp
- `oauth_provider`: For OAuth authentication
- `oauth_id`: For OAuth authentication
- `api_token`: API authentication token
- `api_token_expiry`: Token expiration date
- `status`: Account status (active, inactive, suspended)

**Relationships:**
- Has many Contacts (as owner)
- Has many Deals (as owner)
- Has many Interactions (as creator)
- Has many Events

### Roles Table

Defines user roles and permissions.

**Structure:**
- `id`: Primary key
- `name`: Role name (Admin, Agent, Client)
- `description`: Role description
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Has many Users

### Contacts Table

Stores customer and prospect information.

**Structure:**
- `id`: Primary key
- `first_name`: Contact's first name
- `last_name`: Contact's last name
- `email`: Contact's email address
- `phone`: Primary phone number
- `mobile`: Mobile phone number
- `company`: Company or organization
- `position`: Job position or title
- `website`: Company website
- `address`: Physical address
- `city`: City
- `state`: State or province
- `zip`: Postal code
- `country`: Country
- `notes`: Additional notes
- `lead_source`: How the lead was acquired
- `lead_status`: Current status in the sales process
- `lead_score`: Numerical score for lead qualification
- `owner_id`: Reference to the user that owns this contact
- `avatar`: Path to contact's image
- `created_by`: Reference to the user that created this contact
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User (as owner)
- Belongs to User (as creator)
- Has many Interactions
- Has many Deals
- Has many Tags (through contact_tags)
- Has many Events

### Tags Table

Stores tags for categorizing contacts.

**Structure:**
- `id`: Primary key
- `name`: Tag name
- `color`: Tag color for UI display
- `created_by`: Reference to the user that created this tag
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User (as creator)
- Has many Contacts (through contact_tags)

### Contact_Tags Table

Junction table for the many-to-many relationship between contacts and tags.

**Structure:**
- `contact_id`: Foreign key to contacts
- `tag_id`: Foreign key to tags
- `created_at`: Creation timestamp

**Relationships:**
- Belongs to Contact
- Belongs to Tag

### Interactions Table

Records all interactions with contacts (calls, emails, meetings, tasks, etc.).

**Structure:**
- `id`: Primary key
- `contact_id`: Reference to the contact
- `type`: Type of interaction (call, email, meeting, task, note, other)
- `subject`: Subject or title
- `description`: Detailed description
- `date`: When the interaction occurred or is scheduled
- `duration`: Length of the interaction (in minutes)
- `status`: Current status (planned, completed, canceled)
- `outcome`: Result of the interaction
- `created_by`: Reference to the user that created this interaction
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to Contact
- Belongs to User (as creator)

### Deals Table

Tracks sales opportunities and deals in the pipeline.

**Structure:**
- `id`: Primary key
- `contact_id`: Reference to the contact
- `title`: Deal title
- `description`: Deal description
- `amount`: Monetary value
- `currency`: Currency code
- `stage`: Current stage in the sales pipeline
- `probability`: Likelihood of closing (percentage)
- `expected_close_date`: Projected closing date
- `actual_close_date`: Actual closing date
- `owner_id`: Reference to the user that owns this deal
- `created_by`: Reference to the user that created this deal
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to Contact
- Belongs to User (as owner)
- Belongs to User (as creator)

### Events Table

Stores calendar events and appointments.

**Structure:**
- `id`: Primary key
- `title`: Event title
- `description`: Event description
- `start`: Start date and time
- `end`: End date and time
- `all_day`: Whether it's an all-day event
- `location`: Physical or virtual location
- `color`: Color for UI display
- `user_id`: Reference to the user this event belongs to
- `contact_id`: Reference to a related contact (optional)
- `reminder`: Reminder time (in minutes before the event)
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User
- Belongs to Contact (optional)

### Files Table

Tracks files and documents uploaded to the system.

**Structure:**
- `id`: Primary key
- `name`: File name
- `file_path`: Path to the file
- `file_type`: MIME type
- `file_size`: Size in bytes
- `related_type`: Type of entity this file is related to
- `related_id`: ID of the related entity
- `uploaded_by`: Reference to the user that uploaded this file
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User (as uploader)
- Polymorphic relationship to various entities

### Email_Templates Table

Stores templates for email communications.

**Structure:**
- `id`: Primary key
- `name`: Template name
- `subject`: Email subject
- `body`: Email body content
- `is_default`: Whether this is a default template
- `created_by`: Reference to the user that created this template
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User (as creator)

### Reminders Table

Stores reminders and notifications.

**Structure:**
- `id`: Primary key
- `title`: Reminder title
- `description`: Reminder description
- `due_date`: When the reminder is due
- `priority`: Importance level (low, medium, high)
- `status`: Current status (pending, completed, dismissed)
- `user_id`: Reference to the user this reminder belongs to
- `related_type`: Type of entity this reminder is related to
- `related_id`: ID of the related entity
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

**Relationships:**
- Belongs to User
- Polymorphic relationship to various entities

### Activity_Log Table

Records system activity for auditing.

**Structure:**
- `id`: Primary key
- `user_id`: Reference to the user
- `action`: Description of the action
- `details`: Additional details
- `ip_address`: IP address
- `user_agent`: Browser/client information
- `created_at`: Timestamp when the activity occurred

**Relationships:**
- Belongs to User

### Settings Table

Stores application configuration settings.

**Structure:**
- `id`: Primary key
- `setting_key`: Setting identifier
- `setting_value`: Setting value
- `setting_group`: Category or group
- `is_public`: Whether this setting is publicly accessible
- `created_at`: Creation timestamp
- `updated_at`: Last update timestamp

## Database Indexes

The following indexes are used to optimize database performance:

1. Primary keys on all tables
2. Foreign key indexes on all relationship columns
3. Unique index on `users.email`
4. Index on `contacts.email`
5. Index on `contacts.last_name, contacts.first_name`
6. Index on `interactions.date`
7. Index on `deals.expected_close_date`
8. Index on `events.start`
9. Composite index on `contact_tags.contact_id, contact_tags.tag_id`

## Database Transactions

Database transactions are used for operations that require multiple related changes:

1. Contact creation with tags
2. Deal creation with related files
3. Bulk operations on contacts or deals

## Data Access Layer

The application uses a PDO-based database abstraction layer with prepared statements to prevent SQL injection.

Example of a query in the Contact model:

```php
public function getContactsByOwner($ownerId) {
    $this->db->query('SELECT * FROM contacts WHERE owner_id = :owner_id ORDER BY last_name, first_name');
    $this->db->bind(':owner_id', $ownerId);
    return $this->db->resultSet();
}
```

## Database Migrations

Database schema changes are managed through migration files located in `app/migrations/`. 