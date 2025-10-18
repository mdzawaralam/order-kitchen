# 🍽️ Order Kitchen Project – Task Completion Summary

This document summarizes all completed tasks, configurations, and commands for the **Order Kitchen Symfony Application** using **PostgreSQL** and **Docker**.

---

## ✅ 1. Core APIs

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
If the kitchen is full:
{
  "error": "Kitchen is full",
  "next_available_pickup_time": "2025-10-17T11:00:00+00:00"
}

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
    "items": ["Samosha", "Idly"],
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
**Response:**
```json
{
  "message": "Order completed"
}
```

---

## ✅ 2. Kitchen Capacity + VIP

- Controlled by `.env` variable:  
  ```bash
  KITCHEN_CAPACITY=10
  ```
- Logic handled in `KitchenService.php`
- VIP orders prioritized automatically in queue handling.

---

## ✅ 3. Suggest Next Pickup Time

- Controlled via buffer in `.env`:
  ```bash
  NEXT_PICKUP_BUFFER=15
  ```
- Logic in `OrderController` suggests next available pickup slot when the kitchen is full.

---

## ✅ 4. Priority Queue (VIP)

- VIP orders are prioritized automatically using sorting logic in the repository/service layer.
- Example: VIP orders always appear first in `/orders/active` response.

---

## ✅ 5. Auto-Complete Background Job

- Command: `App\Command\AutoCompleteOrdersCommand`
- Registered in `services.yaml`:
  ```yaml
  App\Command\AutoCompleteOrdersCommand:
      arguments:
          $autoCompleteDelay: '%env(int:AUTO_COMPLETE_DELAY)%'
  ```

- Environment variable:
  ```bash
  AUTO_COMPLETE_DELAY=30
  ```

- Cron job (if running inside Docker):
  ```bash
  * * * * * php /var/www/html/bin/console app:auto-complete-orders >> /var/log/cron.log 2>&1
  ```

---

## ✅ 6. Database Persistence

- Database: **PostgreSQL**
- Environment variables in `.env`:
  ```bash
  DATABASE_URL="postgresql://dbuser:dbpass@db:5432/order_kitchen?serverVersion=15&charset=utf8"
  ```

- Commands:
  ```bash
  php bin/console doctrine:migrations:migrate
  php bin/console doctrine:schema:update --force
  ```

---

## ✅ 7. Unit Tests

- Example: `tests/Repository/OrderRepositoryTest.php`
- Test database uses PostgreSQL URL:
  ```xml
  <env name="DATABASE_URL" value="postgresql://dbuser:dbpass@127.0.0.1:5432/order_kitchen_test"/>
  ```

- Run tests:
  ```bash
  php bin/phpunit
  ```

---

## ✅ 8. Docker Setup

### **docker-compose.yaml**
```yaml
version: '3.8'
services:
  db:
    image: postgres:15
    restart: always
    environment:
      POSTGRES_DB: order_kitchen
      POSTGRES_USER: dbuser
      POSTGRES_PASSWORD: dbpass
    ports:
      - "5432:5432"
    volumes:
      - ./pgdata:/var/lib/postgresql/data

  php:
    build: .
    volumes:
      - ./:/srv/app
    ports:
      - "8000:8000"
    depends_on:
      - db
```

---

## ✅ 9. Dockerfile (App)
```dockerfile
FROM php:8.2-cli
WORKDIR /srv/app
COPY . .
RUN apt-get update && apt-get install -y libpq-dev git unzip     && docker-php-ext-install pdo pdo_pgsql
CMD ["php", "-S", "0.0.0.0:8000", "-t", "public"]
```

---

## ✅ 10. Run Project Commands

### **Run containers**
```bash
docker-compose up -d --build
```

### **Migrate database**
```bash
docker exec -it <php-container-id> php bin/console doctrine:migrations:migrate
```

### **Run Symfony server (if local)**
```bash
symfony serve -d
```

---

## 🎯 Project Summary
| Feature | Status | Notes |
|----------|---------|-------|
| Core APIs | ✅ Completed | CRUD and completion APIs |
| Kitchen Capacity | ✅ Completed | Configurable in `.env` |
| VIP Priority | ✅ Completed | Priority queue implemented |
| Next Pickup Suggestion | ✅ Completed | Dynamic logic applied |
| Auto-Complete Job | ✅ Completed | Background command + cron |
| Database | ✅ PostgreSQL | Persistent storage |
| Unit Tests | ✅ Basic coverage | PHPUnit configured |
| Docker Setup | ✅ Working | App + DB containers |

---

✅ **All core and extended tasks have been implemented successfully.**
