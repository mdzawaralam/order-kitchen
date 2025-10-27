# Order Kitchen – Symfony + Docker + PostgreSQL
  A kitchen order management application built using **Symfony 6.4**, **PHP 8.3**, and **PostgreSQL 15**, containerized with **Docker**.  
  It manages orders, tracks active orders, and auto-completes them after a configurable buffer time.

---

## Project Overview
  - Create and manage kitchen orders with pickup times and VIP priority.
  - Supports automatic completion after a delay (via Symfony command or cron job).
  - Includes full **Docker**, **PHPUnit**, and **Doctrine ORM** setup.

---

## Docker Setup

### Clone the Repository
```bash
git clone https://github.com/mdzawaralam/order-kitchen.git
cd order-kitchen
```

### (Optional but recommended) Clean up any old containers/volumes for a fresh run
```bash
docker compose down -v
docker system prune -f
docker volume prune -f
```

### Build and Start Containers
 ```bash
docker-compose up --build -d
```

### Verify Containers
 ```bash
docker ps
```
You should see containers:
- `order_kitchen_app` → PHP + Symfony container  
- `order_kitchen_db` → PostgreSQL container

### Application Setup (inside PHP container)
```bash
docker-compose exec php bash
```  

### Run migrations and validate schema:
```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console doctrine:schema:validate
```  

### Test API Locally
Once containers are up and migrations are done, test APIs via browser or Postman:
http://localhost:8000/orders/active

---

## Running Test Cases

## Prepare Test Database
```bash
php bin/console --env=test doctrine:database:drop --force --if-exists
php bin/console --env=test doctrine:database:create
php bin/console --env=test doctrine:schema:create
```

### Run PHPUnit Tests
```bash
php vendor/bin/phpunit
```
Expected output:
```
PHPUnit 12.4.1 by Sebastian Bergmann and contributors.
......                                                              6 / 6 (100%)
Time: 00:00.654, Memory: 30.50 MB
OK (6 tests, 14 assertions)
```
---

## Core APIs

### **POST /orders**
**URL:** `http://127.0.0.1:8000/orders`

**Request Body:**
```json
{
  "items": ["Bread", "Rice"],
  "pickup_time": "2025-10-17T10:30:00Z",
  "VIP": false
}
```

**Response:**
```json
{
  "id": 40,
  "items": ["Bread", "Rice"],
  "pickup_time": "2025-10-17T10:30:00+00:00",
  "VIP": false,
  "status": "active"
}
```
**If the kitchen is full:**
```json
{
  "error": "Kitchen is full",
  "next_available_pickup_time": "2025-10-17T11:00:00+00:00"
}
```

---

### **GET /orders/active**
**URL:** `http://127.0.0.1:8000/orders/active`  

**Response:**
```json
[
  {
    "id": 38,
    "items": ["burger", "fries"],
    "pickup_time": "2025-10-18T12:30:00+00:00",
    "VIP": true,
    "created_at": "2025-10-18T16:42:19+00:00"
  },
  {
    "id": 39,
    "items": ["Samosa", "Idli"],
    "pickup_time": "2025-10-17T10:30:00+00:00",
    "VIP": true,
    "created_at": "2025-10-18T16:42:49+00:00"
  },
  {
    "id": 40,
    "items": ["Bread", "Rice"],
    "pickup_time": "2025-10-17T10:30:00+00:00",
    "VIP": false,
    "created_at": "2025-10-18T16:43:13+00:00"
  }
]
```

---

### **POST /orders/{orderId}/complete**
**URL:** `http://127.0.0.1:8000/orders/{orderId}/complete`  

**Example:**
```bash
curl -X POST http://localhost:8000/orders/40/complete
```

**Response:**
```json
{
  "message": "Order completed"
}
```

---

## Cron Job – Auto-Complete Orders
A cron job runs inside the PHP container to automatically mark overdue orders as completed.  
You can also trigger it manually:
```bash
php bin/console app:auto-complete-orders
```

---

## Application Environment
- Database connection verified (`DATABASE_URL` in `.env`)
- Ports exposed:  
  - App → **8000**  
  - Database → **5432**

---

## Docker Setup Files
- `docker-compose.yml`
- `Dockerfile`
- `.env` → contains environment variables
- `cronjob.txt` → for container cron setup (if used)

---

## Ports and Credentials

| Service | Host | Port | Credentials |
|----------|------|------|--------------|
| PHP App | localhost | 8000 | N/A |
| PostgreSQL | localhost | 5432 | From `.env` (`dbuser` / `dbpass`) |

---

## Tech Stack
- PHP 8.3
- Symfony 6.4
- PostgreSQL 15
- Docker
- PHPUnit (for testing)
- Composer (for dependency management)

---

## Stop and Clean Up Containers
## exit from PHP container
```bash
exit
docker compose down -v
```