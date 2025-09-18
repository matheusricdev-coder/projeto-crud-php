<<<<<<< HEAD
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

#### GET /users/
Get users list (requires authentication).

**Headers:** `Authorization: Bearer <token>`

**Query Parameters:**
- `page` (optional): Page number
- `limit` (optional): Items per page

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

#### POST /users/:iduser/drink
Increment coffee counter (requires authentication).

**Headers:** `Authorization: Bearer <token>`

## Sample Users

- `admin@example.com` / `password`
- `user1@example.com` / `password`
- `user2@example.com` / `password`

## Architecture

The project follows Clean Architecture:

```
src/
├── Application/          # Use cases and services
├── Domain/              # Entities and interfaces
└── Infrastructure/      # Controllers and repositories
```
=======
# projeto-crud-php
>>>>>>> abad7dbbceb56dd412d72be56884be142dd380ee
