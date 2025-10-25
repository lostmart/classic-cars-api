# Authentication Integration Guide

This document explains how to integrate the Paris Classic Tours API with an external authentication microservice.

## Overview

The Tours API delegates all authentication responsibilities to an external service, focusing instead on:

- JWT token validation
- Role-based authorization
- User context management
- Business logic execution

## Authentication Service Requirements

Your authentication microservice must provide:

### 1. JWT Token Issuance

After successful login, issue JWT tokens with these claims:

```json
{
	"user_id": "unique-external-user-id",
	"email": "user@example.com",
	"first_name": "John",
	"last_name": "Doe",
	"role": "customer", // or "driver" or "admin"
	"exp": 1735689600, // Unix timestamp
	"iat": 1735603200
}
```

### 2. User Synchronization Endpoint

When a user is created/updated in your auth service, sync to Tours API:

```bash
POST /api/v1/internal/users/sync
Content-Type: application/json
X-Internal-Secret: your-internal-api-secret

{
  "external_user_id": "auth-service-uuid",
  "email": "user@example.com",
  "first_name": "John",
  "last_name": "Doe",
  "role": "customer",
  "phone": "+33612345678"
}
```

### 3. JWT Configuration

**Option A: Shared Secret (HS256)**

- Both services use the same secret key
- Tours API validates signature locally
- Faster, no network calls needed

**Option B: Public Key (RS256)**

- Auth service signs with private key
- Tours API validates with public key
- More secure for distributed systems

## Tours API Integration Points

### JWT Validation Middleware

The Tours API will implement middleware to:

1. Extract Bearer token from Authorization header
2. Validate token signature
3. Check expiration
4. Extract user claims
5. Load user from local database
6. Attach user to request context

Example flow:

```
Request → Extract Token → Validate JWT → Load User → Execute Route Handler
```

### User Data Model

Minimal user table in Tours API:

```sql
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    external_user_id TEXT UNIQUE NOT NULL,
    email TEXT NOT NULL,
    first_name TEXT,
    last_name TEXT,
    phone TEXT,
    role TEXT NOT NULL CHECK(role IN ('customer', 'driver', 'admin')),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
)
```

**Key Points:**

- `external_user_id` links to auth service user ID
- NO password field
- Role stored for authorization decisions
- Can be extended with business-specific fields (e.g., driver license number for drivers)

## Authorization Logic

### Role Permissions

**Customer:**

- Browse tours and cars (public)
- Create bookings
- View/cancel own bookings
- Update own profile

**Driver:**

- View assigned car
- View tour schedule
- Update booking status (start, complete tour)
- View customer contact info for assigned tours

**Admin:**

- Full CRUD on all resources
- Manage user roles
- View system analytics
- Assign drivers to cars

### Implementation Pattern

```php
// Pseudo-code for protected endpoint
$app->get('/api/v1/bookings', function ($request, $response) {
    $user = $request->getAttribute('user'); // Set by auth middleware

    if ($user->role !== 'customer') {
        return $response->withStatus(403);
    }

    $bookings = $bookingRepository->findByCustomerId($user->id);

    return $response->withJson(['success' => true, 'data' => $bookings]);
})->add($authMiddleware);
```

## Security Considerations

### Token Handling

- Always use HTTPS in production
- Set appropriate token expiration (15-60 minutes recommended)
- Implement token refresh mechanism in auth service
- Never log tokens

### JWT Secret Management

- Use strong, random secrets (minimum 256 bits)
- Store in environment variables, never in code
- Rotate secrets periodically
- Use different secrets for different environments

### Rate Limiting

- Implement at API gateway level
- Limit token validation failures
- Block suspicious patterns

### Audit Logging

- Log all authentication attempts
- Log authorization failures
- Track admin actions
- Monitor for unusual patterns

## Error Handling

### Authentication Errors

**401 Unauthorized** - Invalid or missing token

```json
{
	"success": false,
	"message": "Authentication required",
	"error_code": "AUTH_REQUIRED"
}
```

**401 Unauthorized** - Expired token

```json
{
	"success": false,
	"message": "Token expired",
	"error_code": "TOKEN_EXPIRED"
}
```

**403 Forbidden** - Insufficient permissions

```json
{
	"success": false,
	"message": "Insufficient permissions for this action",
	"error_code": "FORBIDDEN"
}
```

## Testing Authentication Integration

### Manual Testing

1. **Get token from auth service:**

```bash
curl -X POST https://auth-service.com/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password123"}'
```

2. **Use token with Tours API:**

```bash
curl https://tours-api.com/api/v1/bookings \
  -H "Authorization: Bearer eyJhbGciOiJIUzI1NiIs..."
```

### Automated Testing

Create test tokens for different roles:

```php
// Test helper to generate valid tokens
function generateTestToken(string $role, int $userId): string {
    $payload = [
        'user_id' => "test-user-$userId",
        'email' => "test-$role@example.com",
        'role' => $role,
        'exp' => time() + 3600,
        'iat' => time()
    ];

    return JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');
}
```

## Migration Strategy

If migrating from integrated auth to microservice:

1. **Phase 1:** Keep existing auth, add JWT validation alongside
2. **Phase 2:** Migrate users to new auth service, sync data
3. **Phase 3:** Redirect login/register to auth service
4. **Phase 4:** Remove old authentication code
5. **Phase 5:** Clean up unused user fields (password hashes, etc.)

## Deployment Checklist

- [ ] JWT_SECRET configured in environment
- [ ] AUTH_SERVICE_URL set correctly
- [ ] User sync endpoint implemented
- [ ] Auth middleware added to protected routes
- [ ] Role checks implemented for sensitive operations
- [ ] Error responses standardized
- [ ] HTTPS enabled
- [ ] Rate limiting configured
- [ ] Audit logging enabled
- [ ] Token expiration tested
- [ ] Role permission matrix documented
- [ ] Runbook created for common issues

## Troubleshooting

### Token Validation Fails

- Verify JWT_SECRET matches between services
- Check token hasn't expired
- Ensure Authorization header format: `Bearer <token>`
- Validate JWT algorithm matches (HS256 vs RS256)

### User Not Found After Token Validation

- Check external_user_id matches between services
- Verify user sync endpoint was called
- Check database for user record
- Confirm role is valid

### Permission Denied Errors

- Verify user role in database
- Check role claim in JWT token
- Review authorization logic for endpoint
- Confirm middleware order (auth before authorization)

## Additional Resources

- JWT.io - Token debugging tool
- RFC 7519 - JWT specification
- OWASP JWT Cheat Sheet
- Microservices Security Best Practices

## Support

For integration questions:

- Check API documentation
- Review example implementations
- Contact development team
- Open GitHub issue with "auth-integration" label
