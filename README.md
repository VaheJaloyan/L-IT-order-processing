# L-IT Symfony Backend Test Assignment

REST API for a simple order processing system built with Symfony, Doctrine ORM, and PostgreSQL.

---

## Requirements

- Docker
- Docker Compose

---

## Installation

```bash
git clone https://github.com/VaheJaloyan/L-IT-order-processing
cd L-IT-order-processing
```

---

## Environment setup

The committed `.env` contains safe defaults that work out of the box with Docker.

For local overrides (real secrets, personal config) create `.env.local` — it is gitignored and never committed:

```bash
cp .env .env.local
```

Then edit `.env.local` and set real values:

```dotenv
APP_SECRET=<generate with: openssl rand -hex 32>
POSTGRES_PASSWORD=your_password
DATABASE_URL="postgresql://app:your_password@127.0.0.1:5432/app?serverVersion=16&charset=utf8"
```

> `.env.local` is only needed if you want to override defaults. The Docker setup works without it.

---

## Start the application

```bash
docker compose up -d
```

This starts three containers:

- `php` — FrankenPHP application server (HTTP on port 80, HTTPS on port 443)
- `worker` — Symfony Messenger consumer for async notifications
- `database` — PostgreSQL

Run migrations:

```bash
docker compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

---

## Load fixtures

Fixtures are split into two classes loaded in dependency order:

- `UserFixtures` — creates two customers (`john@example.com`, `jane@example.com`)
- `OrderFixtures` — creates one pre-existing order for John (PDF example: `BOOK-001 × 2 + PEN-001 × 3 = 46.00`)

```bash
docker compose exec php php bin/console doctrine:fixtures:load --no-interaction
```

---

## Run tests

Create and migrate the test database first:

```bash
docker compose exec php bin/console -e test doctrine:database:create
docker compose exec php bin/console -e test doctrine:migrations:migrate --no-interaction
```

Then run the tests:

```bash
docker compose exec php php bin/phpunit
```

---

## API endpoints

### POST /api/orders — Create an order

```bash
curl -X POST http://localhost/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "customer": {
      "email": "john@example.com",
      "name": "John Doe"
    },
    "items": [
      { "productCode": "BOOK-001", "quantity": 2, "price": 15.50 },
      { "productCode": "PEN-001",  "quantity": 3, "price": 5.00  }
    ]
  }'
```

**Response `201 Created`:**

```json
{
  "id": 2,
  "total": 46.0,
  "status": "created",
  "customer": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "items": [
    {
      "productCode": "BOOK-001",
      "quantity": 2,
      "unitPrice": 15.5,
      "subtotal": 31.0
    },
    {
      "productCode": "PEN-001",
      "quantity": 3,
      "unitPrice": 5.0,
      "subtotal": 15.0
    }
  ]
}
```

**Validation error `422 Unprocessable Entity`:**

```json
{
  "errors": {
    "items": ["Order must have at least one item"],
    "customer.email": ["Customer email is not valid"]
  }
}
```

---

### GET /api/orders/{id} — Get order details

```bash
curl http://localhost/api/orders/1
```

> After loading fixtures, order `1` already exists and can be fetched immediately without creating one first.

**Response `200 OK`:** same shape as the create response above.

**Not found `404`:**

```json
{ "message": "Order not found" }
```

---

## Postman collection

Import `postman_collection.json` from the project root into Postman for ready-to-run requests covering all endpoints and validation cases.

---

## Design decisions

### Monetary values stored in cents

Prices are stored as integers in the smallest currency unit (cents) to avoid floating-point rounding errors. The API accepts and returns decimal values (`15.50`); conversion happens once on input inside `OrderService`.

### Customer upsert

If a customer with the given email already exists, the existing record is reused. No duplicate customers are created.

### Transactional order creation

The entire order creation (customer, order, items) is wrapped in a single database transaction. If any step fails, nothing is persisted.

### Async notifications via Symfony Messenger

After an order is created, an `OrderCreatedMessage` is dispatched to the `async` transport. A separate `worker` container consumes the queue and triggers all registered notification handlers. This decouples notifications from the HTTP request cycle.

**Notification failures never affect order creation.** Because the message is dispatched asynchronously, a failing email server or push provider does not roll back the order or return an error to the caller.

### Extensible notification handlers

Notification channel handlers (`EmailNotificationHandler`, `PushNotificationHandler`) implement `NotificationHandlerInterface` and declare which event types they support via `supports()`. Adding a new channel requires only a new class — no existing code is modified (Open/Closed Principle).

### Event types as a backed enum

`NotificationEventType` is a backed enum (`string`). This eliminates magic strings and provides a central registry of all notification event types. If handlers ever need typed, event-specific fields rather than a generic payload array, migrating to a class-per-type hierarchy would be the natural next step.
