# Coffee API

RESTful API built with pure PHP following Clean Architecture principles.

## Requirements

- PHP 8.0+
- MySQL 5.7+

## Installation

1. Create database and import schema:
```bash
mysql -u root -p < database/schema.sql
```

2. Copy environment configuration:
```bash
cp config.env.example .env
```

3. Update `.env` with your database credentials.

4. Start server:
```bash
php -S localhost:8000
```

## API Endpoints

### Authentication

#### POST /login
Authenticate user and receive JWT token.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "jwt-token",
        "iduser": 1,
        "name": "User Name",
        "drinkCounter": 0
    }
}
```

### Users

#### POST /users/
Create new user.

**Request:**
```json
{
    "email": "user@example.com",
    "name": "User Name",
    "password": "password"
}
```

**Response (201):**
```json
{
    "success": true,
    "message": "User created successfully",
    "data": {
        "iduser": 1,
        "email": "user@example.com",
        "name": "User Name",
        "drinkCounter": 0
    }
}
```

**Error (409):**
```json
{
    "error": true,
    "message": "User with this email already exists"
}
```

#### GET /users/
Get users list (requires authentication).

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 10, max: 100)

**Response (200):**
```json
{
    "success": true,
    "message": "Users retrieved successfully",
    "data": {
        "data": [
            {
                "iduser": 1,
                "email": "user@example.com",
                "name": "User Name",
                "drinkCounter": 5
            }
        ],
        "pagination": {
            "page": 1,
            "limit": 10,
            "total": 1,
            "pages": 1
        }
    }
}
```

#### GET /users/:iduser
Get user by ID (requires authentication).

**Headers:** `Authorization: Bearer <token>`

#### PUT /users/:iduser
Update user (requires authentication, users can only update themselves).

**Headers:** `Authorization: Bearer <token>`

**Request:**
```json
{
    "name": "New Name",
    "email": "new@example.com"
}
```

#### DELETE /users/:iduser
Delete user (requires authentication, users can only delete themselves).

**Headers:** `Authorization: Bearer <token>`

**Response (204):** No content

**Error (403):**
```json
{
    "error": true,
    "message": "You can only delete your own account"
}
```

#### POST /users/:iduser/drink
Increment coffee counter (requires authentication).

**Headers:** `Authorization: Bearer <token>`

**Response (200):**
```json
{
    "success": true,
    "message": "Drink counter incremented successfully",
    "data": {
        "iduser": 1,
        "email": "user@example.com",
        "name": "User Name",
        "drinkCounter": 6
    }
}
```

## Sample Users

- `admin@example.com` / `password`
- `user1@example.com` / `password`
- `user2@example.com` / `password`

## Status Codes

- **200** - Success
- **201** - Created
- **204** - No Content
- **400** - Bad Request
- **401** - Unauthorized
- **403** - Forbidden
- **404** - Not Found
- **409** - Conflict
- **422** - Unprocessable Entity
- **500** - Internal Server Error

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` and `password_verify()`
- **JWT Authentication**: Secure token-based authentication with 1-hour expiration
- **Input Validation**: All inputs are validated and sanitized
- **SQL Injection Protection**: Uses prepared statements
- **Authorization**: Users can only modify their own accounts
- **CORS**: Configured for cross-origin requests

## Optional Features

### Daily Drink History

#### GET /users/:iduser/drinks/daily
Get user's daily coffee consumption history.

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `from` (optional): Start date (YYYY-MM-DD)
- `to` (optional): End date (YYYY-MM-DD)

**Response (200):**
```json
{
    "success": true,
    "message": "Daily drinks history retrieved successfully",
    "data": {
        "user_id": 1,
        "history": [
            {
                "day": "2023-09-18",
                "total_drinks": 3
            },
            {
                "day": "2023-09-17",
                "total_drinks": 2
            }
        ]
    }
}
```

### Coffee Rankings

#### GET /drinks/ranking
Get ranking of users by coffee consumption.

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `date` (optional): Specific date for ranking (YYYY-MM-DD)
- `days` (optional): Number of past days to include (1-365)
- `limit` (optional): Maximum number of users to return (default: 10)

**Response (200):**
```json
{
    "success": true,
    "message": "Drink ranking retrieved successfully",
    "data": {
        "ranking": [
            {
                "user_id": 1,
                "name": "John Doe",
                "email": "john@example.com",
                "total_drinks": 8
            },
            {
                "user_id": 2,
                "name": "Jane Smith",
                "email": "jane@example.com",
                "total_drinks": 5
            }
        ],
        "parameters": {
            "date": "2023-09-18",
            "days": null,
            "limit": 10
        }
    }
}
```

## Architecture

The project follows Clean Architecture:

```
src/
├── Application/          # Use cases and services
├── Domain/              # Entities and interfaces
└── Infrastructure/      # Controllers and repositories
```