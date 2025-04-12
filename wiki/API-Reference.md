# API Reference

Flopy CRM provides a RESTful API that allows developers to integrate with the system. This document provides detailed information about using the API.

## Authentication

All API requests require authentication using an API token. You can generate an API token from your user profile in the application.

### Request Format

Include your API token in the header of all requests:

```
GET /api/contacts
Headers:
  X-API-Token: your_api_token_here
```

### Response Format

API responses are returned in JSON format:

```json
{
  "status": "success",
  "data": {
    // Response data here
  }
}
```

Error responses include error details:

```json
{
  "status": "error",
  "message": "Error message here",
  "code": 400
}
```

## API Endpoints

### Contacts

#### List All Contacts

```
GET /api/contacts
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number for pagination |
| limit | integer | Number of results per page |
| search | string | Search term to filter contacts |
| tag_id | integer | Filter by tag ID |
| owner_id | integer | Filter by owner ID |
| sort | string | Field to sort by (e.g., "last_name") |
| order | string | Sort order ("asc" or "desc") |

**Response:**

```json
{
  "status": "success",
  "data": {
    "contacts": [
      {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john.doe@example.com",
        "phone": "123-456-7890",
        "company": "ACME Inc",
        "created_at": "2023-01-15T10:30:00Z",
        "updated_at": "2023-01-20T14:15:00Z"
      },
      // More contacts...
    ],
    "pagination": {
      "total": 125,
      "page": 1,
      "limit": 10,
      "pages": 13
    }
  }
}
```

#### Get a Single Contact

```
GET /api/contacts/{id}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "phone": "123-456-7890",
    "mobile": "987-654-3210",
    "company": "ACME Inc",
    "position": "CEO",
    "website": "https://example.com",
    "address": "123 Main St",
    "city": "New York",
    "state": "NY",
    "zip": "10001",
    "country": "USA",
    "notes": "Met at conference",
    "lead_source": "Web",
    "lead_status": "Qualified",
    "lead_score": 85,
    "owner_id": 2,
    "avatar": "default_contact.jpg",
    "created_by": 1,
    "created_at": "2023-01-15T10:30:00Z",
    "updated_at": "2023-01-20T14:15:00Z",
    "tags": [
      {"id": 1, "name": "VIP", "color": "#FF5733"},
      {"id": 3, "name": "Decision Maker", "color": "#C70039"}
    ]
  }
}
```

#### Create a Contact

```
POST /api/contacts
```

**Request Body:**

```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane.smith@example.com",
  "phone": "555-123-4567",
  "company": "XYZ Corp",
  "position": "CTO",
  "owner_id": 2,
  "tags": [1, 4]
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 126,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "phone": "555-123-4567",
    "company": "XYZ Corp",
    "position": "CTO",
    "owner_id": 2,
    "created_by": 1,
    "created_at": "2023-05-12T09:45:00Z",
    "updated_at": "2023-05-12T09:45:00Z",
    "tags": [
      {"id": 1, "name": "VIP", "color": "#FF5733"},
      {"id": 4, "name": "Technical", "color": "#3498DB"}
    ]
  }
}
```

#### Update a Contact

```
PUT /api/contacts/{id}
```

**Request Body:**

```json
{
  "phone": "555-987-6543",
  "lead_status": "Customer",
  "lead_score": 95
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 126,
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane.smith@example.com",
    "phone": "555-987-6543",
    "company": "XYZ Corp",
    "position": "CTO",
    "lead_status": "Customer",
    "lead_score": 95,
    "owner_id": 2,
    "created_by": 1,
    "created_at": "2023-05-12T09:45:00Z",
    "updated_at": "2023-05-12T10:30:00Z"
  }
}
```

#### Delete a Contact

```
DELETE /api/contacts/{id}
```

**Response:**

```json
{
  "status": "success",
  "message": "Contact deleted successfully"
}
```

### Deals

#### List All Deals

```
GET /api/deals
```

**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number for pagination |
| limit | integer | Number of results per page |
| contact_id | integer | Filter by contact ID |
| stage | string | Filter by deal stage |
| owner_id | integer | Filter by owner ID |
| sort | string | Field to sort by |
| order | string | Sort order ("asc" or "desc") |

**Response:**

```json
{
  "status": "success",
  "data": {
    "deals": [
      {
        "id": 1,
        "contact_id": 5,
        "title": "Software License",
        "amount": 5000.00,
        "currency": "USD",
        "stage": "proposal",
        "probability": 60,
        "expected_close_date": "2023-06-30",
        "owner_id": 2,
        "created_at": "2023-05-01T13:45:00Z",
        "updated_at": "2023-05-10T09:20:00Z"
      },
      // More deals...
    ],
    "pagination": {
      "total": 48,
      "page": 1,
      "limit": 10,
      "pages": 5
    }
  }
}
```

#### Get a Single Deal

```
GET /api/deals/{id}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 1,
    "contact_id": 5,
    "contact": {
      "id": 5,
      "first_name": "Robert",
      "last_name": "Johnson",
      "email": "robert.j@example.com",
      "company": "Big Corp"
    },
    "title": "Software License",
    "description": "Enterprise license for 100 users",
    "amount": 5000.00,
    "currency": "USD",
    "stage": "proposal",
    "probability": 60,
    "expected_close_date": "2023-06-30",
    "actual_close_date": null,
    "owner_id": 2,
    "created_by": 1,
    "created_at": "2023-05-01T13:45:00Z",
    "updated_at": "2023-05-10T09:20:00Z"
  }
}
```

#### Create a Deal

```
POST /api/deals
```

**Request Body:**

```json
{
  "contact_id": 126,
  "title": "Cloud Migration",
  "description": "Migrate on-premise systems to cloud",
  "amount": 25000.00,
  "currency": "USD",
  "stage": "qualified",
  "probability": 40,
  "expected_close_date": "2023-08-15",
  "owner_id": 2
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 49,
    "contact_id": 126,
    "title": "Cloud Migration",
    "description": "Migrate on-premise systems to cloud",
    "amount": 25000.00,
    "currency": "USD",
    "stage": "qualified",
    "probability": 40,
    "expected_close_date": "2023-08-15",
    "owner_id": 2,
    "created_by": 1,
    "created_at": "2023-05-12T11:30:00Z",
    "updated_at": "2023-05-12T11:30:00Z"
  }
}
```

#### Update a Deal

```
PUT /api/deals/{id}
```

**Request Body:**

```json
{
  "stage": "proposal",
  "probability": 60,
  "expected_close_date": "2023-07-30"
}
```

**Response:**

```json
{
  "status": "success",
  "data": {
    "id": 49,
    "contact_id": 126,
    "title": "Cloud Migration",
    "description": "Migrate on-premise systems to cloud",
    "amount": 25000.00,
    "currency": "USD",
    "stage": "proposal",
    "probability": 60,
    "expected_close_date": "2023-07-30",
    "owner_id": 2,
    "created_by": 1,
    "created_at": "2023-05-12T11:30:00Z",
    "updated_at": "2023-05-15T14:20:00Z"
  }
}
```

#### Delete a Deal

```
DELETE /api/deals/{id}
```

**Response:**

```json
{
  "status": "success",
  "message": "Deal deleted successfully"
}
```

### Other Endpoints

The API also provides endpoints for:

- Interactions
- Events
- Reminders
- Tags
- Users
- Settings

## Rate Limiting

API requests are subject to rate limiting:

- 100 requests per minute per API token
- When the limit is exceeded, requests will return a 429 status code

## Error Codes

| Code | Description |
|------|-------------|
| 400 | Bad Request - Invalid or missing parameters |
| 401 | Unauthorized - Invalid or missing API token |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource does not exist |
| 422 | Unprocessable Entity - Validation error |
| 429 | Too Many Requests - Rate limit exceeded |
| 500 | Internal Server Error - Something went wrong on the server |

## Webhooks

Flopy CRM supports webhooks for real-time notifications about events. To configure webhooks, use the following API endpoints:

### List Webhooks

```
GET /api/webhooks
```

### Create Webhook

```
POST /api/webhooks
```

**Request Body:**

```json
{
  "event": "contact.created",
  "target_url": "https://your-app.com/webhook-handler",
  "secret": "your_webhook_secret"
}
```

Available events:
- contact.created
- contact.updated
- contact.deleted
- deal.created
- deal.updated
- deal.deleted
- interaction.created
- event.created

## SDKs and Client Libraries

Official client libraries:

- [PHP SDK](https://github.com/aazzroy/flopy-crm-php-sdk)
- [JavaScript SDK](https://github.com/aazzroy/flopy-crm-js-sdk)

## Example Code

### PHP Example

```php
<?php
// Using cURL to make an API request
$url = 'https://your-flopy-crm.com/api/contacts';
$token = 'your_api_token';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-Token: ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
```

### JavaScript Example

```javascript
// Using fetch to make an API request
const url = 'https://your-flopy-crm.com/api/contacts';
const token = 'your_api_token';

fetch(url, {
  headers: {
    'X-API-Token': token,
    'Content-Type': 'application/json'
  }
})
.then(response => response.json())
.then(data => {
  console.log(data);
})
.catch(error => {
  console.error('Error:', error);
});
```

## Support

For API support or to report issues, please contact:

- Email: api-support@flopy.com
- GitHub: [Create an issue](https://github.com/aazzroy/flopy_crm/issues) 