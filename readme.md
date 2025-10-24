## Public deplyment

https://classic-cars-api-production.up.railway.app/

## Folder Structure

```
simple-restPHP/
├── .htaccess
├── index.php (entry point)
├── config.php (or config/)
├── routes.php (defines all routes)
├── controllers/
│   └── UserController.php
├── services/
│   └── UserService.php
├── models/
│   └── User.php (data model/entity)
├── repositories/
│   └── UserRepository.php
├── middlewares/
│   └── AuthMiddleware.php
├── helpers/ or utils/
│   └── Response.php
│   └── Validator.php
└── database.db
```

## API Endpoints

```
GET    /api/users         - Get all users
GET    /api/users/{id}    - Get specific user
POST   /api/users         - Create new user
PUT    /api/users/{id}    - Update user
DELETE /api/users/{id}    - Delete user
```

## Request/Response Examples

**Create a user (POST /api/users):**

```
Request body: { "name": "John", "email": "john@example.com" }
Response: {
    "status": "success",
    "data": {
        "id": 1,
        "name": "John",
        "email": "john@example.com"
    }
}
```

**Get all users (GET /api/users):**

```
Response: {
    "status": "success",
    "data": [
        {user1},
        {user2},
        ...
        ]
    }
```

## Layer Responsibilities Explained

### 1. **Routes** (`routes/api.php`)

- **Purpose**: Define all API endpoints and map them to controllers
- **Contains**: URL patterns and which controller method handles each
- **Example**: `GET /api/users → UserController::index()`
- **Why**: Centralized routing makes it easy to see all available endpoints

### 2. **Controllers** (`controllers/UserController.php`)

- **Purpose**: Handle HTTP requests and responses
- **Contains**: Methods like `index()`, `show()`, `store()`, `update()`, `destroy()`
- **Responsibilities**:
  - Receive request data
  - Validate input (or delegate to validator)
  - Call appropriate service methods
  - Format and return HTTP responses
- **Does NOT**: Contain business logic or database queries
- **Example**: Receives POST request, validates, calls UserService, returns JSON

### 3. **Services** (`services/UserService.php`)

- **Purpose**: Business logic layer
- **Contains**: The "what" and "how" of your application
- **Responsibilities**:
  - Complex business rules
  - Orchestrate multiple repository calls
  - Data transformation
  - Transaction management
- **Example**: `createUser()` might check for duplicates, hash passwords, send welcome email
- **Does NOT**: Know about HTTP or database specifics

### 4. **Models** (`models/User.php`)

- **Purpose**: Represent data entities
- **Contains**: Properties and basic getters/setters
- **Can include**: Validation rules, data casting, relationships
- **Example**: User object with id, name, email properties
- **Note**: This is the data structure, not database operations

### 5. **Repositories** (`repositories/UserRepository.php`) - Optional but Recommended

- **Purpose**: Data access layer (database operations)
- **Contains**: All SQL queries and database interactions
- **Responsibilities**:
  - CRUD operations
  - Complex queries
  - Database-specific logic
- **Why**: Abstracts database from business logic (could swap MySQL for PostgreSQL easily)
- **Example**: `findById()`, `findAll()`, `create()`, `update()`, `delete()`

### 6. **Middlewares** (`middlewares/`) - Optional

- **Purpose**: Pre-process requests before they reach controllers
- **Examples**:
  - Authentication (check API keys, JWT tokens)
  - Rate limiting
  - CORS handling
  - Request logging
- **Execution**: Runs before controller methods

### 7. **Helpers/Utils** (`helpers/`)

- **Purpose**: Reusable utility functions
- **Examples**:
  - `Response.php`: Standard JSON response formatting
  - `Validator.php`: Input validation
  - `Database.php`: Database connection helper

## Request Flow Example

When `POST /api/users` with user data arrives:

```
1. .htaccess → index.php
2. index.php loads routes/api.php
3. Router matches route and calls UserController::store()
4. Controller:
   - Validates input using Validator helper
   - Calls UserService::createUser($data)
5. Service:
   - Applies business logic (check duplicates, hash password)
   - Calls UserRepository::create($data)
6. Repository:
   - Executes SQL INSERT
   - Returns User model
7. Service returns User model to Controller
8. Controller formats response using Response helper
9. JSON sent back to client with status 201
```
