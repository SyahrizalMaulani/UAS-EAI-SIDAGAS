# Infrastruktur & Docker Manifest (SIDAGAS)

Dokumen ini memuat konfigurasi lengkap untuk men-_deploy_ ekosistem Microservices SIDAGAS menggunakan Docker dan Docker Compose.

---

## 1. `docker-compose.yml` (Orkestrasi Utama)
File ini adalah pusat kendali yang menjalankan 10 _containers_ sekaligus (4 Microservices, 1 API Gateway, 4 MySQL Databases, dan 1 RabbitMQ).

```yaml
version: '3.8'

services:
  # Message Broker
  rabbitmq:
    image: rabbitmq:3-management
    ports:
      - "5672:5672"   # Port komunikasi AMQP
      - "15672:15672" # Port GUI Dashboard RabbitMQ
    healthcheck:
      test: ["CMD", "rabbitmq-diagnostics", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

  # API Gateway
  api-gateway:
    build: ./integration-layer
    ports:
      - "3000:3000"
    environment:
      - ORDER_SERVICE_URL=http://order-service:3001
      - INVENTORY_SERVICE_URL=http://inventory-service:3002
      - DELIVERY_SERVICE_URL=http://delivery-service:3003
      - FINANCE_SERVICE_URL=http://finance-service:3004
    depends_on:
      - order-service
      - inventory-service

  # Microservices
  order-service:
    build: ./order-service
    environment:
      - PORT=3001
      - DB_HOST=order-db
      - RABBITMQ_URL=amqp://rabbitmq
    depends_on:
      rabbitmq:
        condition: service_healthy
      order-db:
        condition: service_healthy

  inventory-service:
    build: ./inventory-service
    environment:
      - PORT=3002
      - DB_HOST=inventory-db
      - RABBITMQ_URL=amqp://rabbitmq
    depends_on:
      rabbitmq:
        condition: service_healthy

  delivery-service:
    build: ./delivery-service
    environment:
      - PORT=3003
      - DB_HOST=delivery-db
      - RABBITMQ_URL=amqp://rabbitmq
    depends_on:
      rabbitmq:
        condition: service_healthy

  finance-service:
    build: ./finance-service
    environment:
      - PORT=3004
      - DB_HOST=finance-db

  # Databases (Shared-Nothing Data)
  order-db:
    image: mysql:8.0
    ports:
      - "33061:3306"
    environment:
      MYSQL_DATABASE: order_db
      MYSQL_ROOT_PASSWORD: sidagas_pass
    volumes:
      - order-db-data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 10s
      retries: 3

  inventory-db:
    image: mysql:8.0
    ports:
      - "33062:3306"
    environment:
      MYSQL_DATABASE: inventory_db
      MYSQL_ROOT_PASSWORD: sidagas_pass
    volumes:
      - inventory-db-data:/var/lib/mysql

  delivery-db:
    image: mysql:8.0
    ports:
      - "33063:3306"
    environment:
      MYSQL_DATABASE: delivery_db
      MYSQL_ROOT_PASSWORD: sidagas_pass
    volumes:
      - delivery-db-data:/var/lib/mysql

  finance-db:
    image: mysql:8.0
    ports:
      - "33064:3306"
    environment:
      MYSQL_DATABASE: finance_db
      MYSQL_ROOT_PASSWORD: sidagas_pass
    volumes:
      - finance-db-data:/var/lib/mysql

volumes:
  order-db-data:
  inventory-db-data:
  delivery-db-data:
  finance-db-data:
```

---

## 2. Struktur `Dockerfile` (Tiap Komponen Node.js)
Setiap layanan Node.js (seperti `order-service`, `finance-service`, dll) menggunakan `Dockerfile` seragam yang sangat ringan dan efisien (berbasis Alpine Linux).

```dockerfile
# Base Image: Sangat ringan, khusus untuk Node.js
FROM node:18-alpine

# Set working directory di dalam container
WORKDIR /usr/src/app

# Salin definisi dependency terlebih dahulu untuk mengoptimalkan Layer Caching Docker
COPY package*.json ./

# Install dependensi (mengabaikan devDependencies untuk production)
RUN npm install --only=production

# Salin sisa kode program dari komputer host ke container
COPY . .

# Ekspos port default (3000). Akan ditimpa oleh env PORT dari docker-compose
EXPOSE 3000

# Eksekusi server
CMD ["node", "index.js"]
```

## 3. Cara _Deployment_
Cukup jalankan satu perintah berikut di direktori yang sama dengan `docker-compose.yml`:

```bash
docker compose up --build -d
```
Sistem secara otomatis akan mengunduh _images_ MySQL & RabbitMQ, membangun _images_ layanan lokal, membuat _virtual network_, dan menyalakan _container_ secara berurutan sesuai aturan `depends_on`.
