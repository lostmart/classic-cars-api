sequenceDiagram
    autonumber
    participant Client
    participant API as API Gateway / V1 Controller
    participant Auth as Auth Microservice
    participant Logic as Booking Service
    participant DB as SQLite (Postgres-Ready)

    Note over Client, DB: Request Lifecycle with Resilience Patterns

    Client->>API: POST /api/v1/bookings
    API->>API: Validate Schema (Joi/Zod)
    
    alt Invalid Input
        API-->>Client: 400 Bad Request (Validation Errors)
    end

    API->>Auth: Verify JWT / Permissions
    
    alt Auth Service Down
        Note right of API: Circuit Breaker Trips
        API-->>Client: 503 Service Unavailable (Graceful Degradation)
    else Auth Success
        Auth-->>API: 200 OK (User Context)
        
        API->>Logic: Create Booking(Data)
        Logic->>DB: ACID Transaction (Parameterized SQL)
        
        alt DB Conflict
            DB-->>Logic: Error
            Logic-->>API: 409 Conflict
            API-->>Client: 409 Conflict (Slot taken)
        else Success
            DB-->>Logic: Saved
            Logic-->>API: Booking Object
            API-->>Client: 201 Created
        end
    end