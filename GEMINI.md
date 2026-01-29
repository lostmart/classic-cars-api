# Engineering Standards & Architecture (GEMINI.md)

This document outlines the architectural decisions, maturity patterns, and senior-level engineering standards applied to this project.

## 🏗️ Architecture & Design Philosophy

### The "API-First" Approach
We utilize a RESTful architecture with a strict versioning strategy.
* **Versioning:** We use **URL-based versioning** (`/api/v1/`) for its explicitness and ease of caching.
* **Decoupling:** Business logic is isolated from transport layers (Controllers) to ensure the core domain remains testable and agnostic of the delivery mechanism.



### Service Resilience
When interacting with external dependencies (e.g., Auth Microservice), the system follows the **Circuit Breaker** pattern.
* **Retries:** Failed external calls trigger a retry with **exponential backoff**.
* **Degradation:** If the Auth service is unreachable, the system fails fast with a `503 Service Unavailable` rather than hanging, protecting the event loop.

---

## 🧪 Testing Strategy

* **Philosophy:** We follow a **Test-After** approach for rapid prototyping, transitioning to **TDD** for critical business logic in the Service layer.
* **Coverage:** * **Unit Tests:** Focus on complex logic and edge cases.
    * **Integration Tests:** Focus on the "Happy Path" of API endpoints using a mock database.
* **External Dependencies:** We use **Dependency Injection** to swap real microservice clients with **Mocks/Stubs** during test execution to ensure deterministic results.

---

## 💾 Database & Persistence

### SQLite to PostgreSQL Migration Path
Currently, **SQLite** is used for its zero-config nature and suitability for single-node demos.
* **Production Move:** For a production environment, we would migrate to **PostgreSQL** to handle concurrent writes, row-level locking, and robust indexing.
* **Migrations:** Database schema is version-controlled. We use an "Up/Down" migration strategy to ensure repeatable deployments and easy rollbacks.

---

## 🔐 Security Posture

* **Injection Prevention:** All database queries utilize **Parameterized Statements** (Prepared Statements) to eliminate SQL Injection risks.
* **Input Validation:** We implement strict **Schema Validation** (e.g., Joi/Zod) at the entry point of every request.
* **Rate Limiting:** To prevent DoS attacks, we implement a sliding-window rate limiter (e.g., 100 requests per 15 minutes per IP).

---

## 📊 Observability & Operations

### Logging & Monitoring
* **Structured Logging:** Logs are emitted in JSON format to be easily ingested by ELK or Datadog.
* **Health Checks:** A `/health` endpoint is provided that performs a "deep" check (validating DB connection and Auth service heartbeat).
* **Graceful Shutdown:** The application listens for `SIGTERM` signals to finish processing active requests and close DB connections before exiting.

---

## 🚀 Future Scalability (Roadmap)

1.  **Caching:** Implementation of a **Redis** layer for high-traffic read endpoints (e.g., Booking listings).
2.  **Async Processing:** Moving heavy tasks (Email notifications) to background workers using a message queue (RabbitMQ/BullMQ).
3.  **Advanced Deployment:** Transitioning from manual environment variables to a **Secret Management** system (AWS Secrets Manager/HashiCorp Vault).