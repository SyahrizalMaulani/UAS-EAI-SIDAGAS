# 💧 SIDAGAS (Sistem Informasi Dagang Syahrizal Galon)

**Enterprise Application Integration (EAI) - Tugas Besar**

---

## 📖 Deskripsi Proyek

**SIDAGAS (Sistem Informasi Dagang)** adalah sebuah platform sistem informasi manajemen transaksi dan distribusi air minum (galon) serta gas LPG. Sistem ini dirancang menggunakan arsitektur **Enterprise Application Integration (EAI)** dengan pendekatan **Microservices** yang mandiri (_Shared-Nothing Architecture_). Komponen-komponen dalam SIDAGAS terintegrasi meskipun memiliki perbedaan protokol komunikasi (GraphQL vs REST) dan format data (JSON vs XML) dengan bantuan **API Gateway** dan **Message Broker** secara asinkron menggunakan **RabbitMQ**.

---

## 📁 Struktur Direktori & Folder

Proyek SIDAGAS EAI dibagi menjadi dua bagian utama: **Backend (Microservice)** dan **Frontend (Laravel BFF)**.

```text
EAI_UAS/
├── Microservice/                 # Folder infrastruktur Backend (Dockerized)
│   ├── api-gateway/              # Entry point utama & Message Translator
│   ├── order-service/            # GraphQL Modular (index.js, schema.js, resolvers.js)
│   ├── inventory-service/        # GraphQL Modular (index.js, schema.js, resolvers.js)
│   ├── delivery-service/         # GraphQL Modular (index.js, schema.js, resolvers.js)
│   ├── finance-service/          # REST API XML (Legacy System)
│   ├── .env                      # File kredensial environment variables
│   └── docker-compose.yml        # Orkestrasi otomatis untuk 10 container
│
├── Frontend/                      # Folder UI Frontend (Laravel 12)
│   ├── app/Http/Controllers/     # Logika autentikasi dan controller UI
│   ├── resources/views/          # File desain antarmuka (Blade + Tailwind CSS)
│   │   ├── admin/                # Panel untuk Role Admin
│   │   ├── driver/               # Panel untuk Role Driver
│   │   ├── pelanggan/            # Panel untuk Role Pelanggan
│   │   └── karyawan/             # Panel untuk Role Karyawan Pabrik
│   └── routes/web.php            # Routing sistem BFF
│
├── Laporan_Tugas_Besar_SIDAGAS.pdf  # Dokumen laporan resmi
├── Infrastruktur_Docker_Manifest.md # Detail manifest Docker
└── Arsitektur_Integrasi_EIP.md      # Penjelasan konsep EIP (Mermaid Diagram)
```

---

## Desain Database (ERD)

Sistem ini menggunakan arsitektur database terdistribusi di mana setiap microservice mengelola databasenya sendiri (_Database-per-service_). Berikut rancangan tabel database relasional untuk masing-masing service:

#### 1. Database: `order_db` (Tabel: `orders`)

- Menyimpan informasi transaksi pemesanan yang diajukan oleh pelanggan.
- **Skema Kolom:**
  - `id` (INT, Primary Key, Auto Increment)
  - `customer_name` (VARCHAR 255, Not Null)
  - `item_name` (VARCHAR 255, Not Null)
  - `quantity` (INT, Not Null)
  - `status` (VARCHAR 50, Default: 'pending')
  - `created_at` (TIMESTAMP, Default: Current Timestamp)

#### 2. Database: `inventory_db` (Tabel: `inventory`)

- Menyimpan data stok barang dagang (Galon, Gas LPG).
- **Skema Kolom:**
  - `id` (INT, Primary Key, Auto Increment)
  - `item_name` (VARCHAR 255, Unique, Not Null)
  - `stock` (INT, Default: 0, Not Null)
  - `updated_at` (TIMESTAMP, Default: Current/On Update Current Timestamp)

#### 3. Database: `delivery_db` (Tabel: `deliveries`)

- Menyimpan jadwal dan status pengiriman barang oleh kurir/driver.
- **Skema Kolom:**
  - `id` (INT, Primary Key, Auto Increment)
  - `order_id` (INT, Not Null)
  - `customer_name` (VARCHAR 255, Not Null)
  - `address` (VARCHAR 255, Default: 'Alamat belum diset')
  - `status` (VARCHAR 50, Default: 'scheduled')
  - `created_at` (TIMESTAMP, Default: Current Timestamp)

#### 4. Database: `finance_db` (Tabel: `transactions`)

- Menyimpan verifikasi transaksi pembayaran masuk (Data Legacy XML).
- **Skema Kolom:**
  - `id` (INT, Primary Key, Auto Increment)
  - `order_id` (INT, Not Null)
  - `amount` (DECIMAL 10,2, Not Null)
  - `method` (VARCHAR 50, Not Null)
  - `status` (VARCHAR 50, Default: 'verified')
  - `created_at` (TIMESTAMP, Default: Current Timestamp)

---

## Daftar Sistem & Endpoint

Seluruh sistem di- _hosting_ di kontainer Docker terpisah. Berikut adalah daftar komponen yang beroperasi dalam ekosistem SIDAGAS:

| Nama Layanan          | Port Internal | Port Host        | Endpoint Utama                  | Deskripsi                                                     |
| --------------------- | ------------- | ---------------- | ------------------------------- | ------------------------------------------------------------- |
| **API Gateway**       | `3000`        | `3000`           | `http://localhost:3000/*`       | Entry point utama (Router). Semua _client_ menembak ke sini.  |
| **Order Service**     | `3001`        | `3001`           | `http://localhost:3001/graphql` | Mengurus pembuatan transaksi & pesanan masuk.                 |
| **Inventory Service** | `3002`        | `3002`           | `http://localhost:3002/graphql` | Mengelola sisa stok galon/gas dan _intake_ barang produksi.   |
| **Delivery Service**  | `3003`        | `3003`           | `http://localhost:3003/graphql` | Mengelola data pengiriman, jadwal armada, dan status kurir.   |
| **Finance Service**   | `3004`        | `3004`           | `http://localhost:3004/verify`  | Menerima dan memverifikasi laporan pembayaran dari transaksi. |
| **RabbitMQ**          | `5672`        | `5672` / `15672` | `amqp://localhost:5672`         | _Message Broker_ untuk komunikasi sistem asinkron.            |

---

### Struktur Container

File `docker-compose.yml` mengorkestrasi 10 buah kontainer yang saling terhubung dalam satu jaringan virtual bernama `microservice`:

| Layanan               | Image                   | Port Host (Mapping)         | Fungsi                                   |
| --------------------- | ----------------------- | --------------------------- | ---------------------------------------- |
| **api-gateway**       | Node.js (Built)         | `3000`                      | Entry point utama / Router / Translator  |
| **order-service**     | Node.js (Built)         | `3001`                      | Mengatur GraphQL & Menerbitkan Event     |
| **inventory-service** | Node.js (Built)         | `3002`                      | Mengelola stok pabrik                    |
| **delivery-service**  | Node.js (Built)         | `3003`                      | Mengelola pengiriman driver              |
| **finance-service**   | Node.js (Built)         | `3004`                      | Verifikasi pembayaran via XML REST API   |
| **rabbitmq**          | `rabbitmq:3-management` | `5672` (AMQP), `15672` (UI) | Message Broker untuk komunikasi asinkron |
| **order-db**          | `mysql:8.0`             | `33061`                     | Penyimpanan persisten (Volume) Order     |
| **inventory-db**      | `mysql:8.0`             | `33062`                     | Penyimpanan persisten (Volume) Inventory |
| **delivery-db**       | `mysql:8.0`             | `33063`                     | Penyimpanan persisten (Volume) Delivery  |
| **finance-db**        | `mysql:8.0`             | `33064`                     | Penyimpanan persisten (Volume) Finance   |

### Fitur Docker Canggih yang Digunakan:

1. **Depends_On & Healthcheck:** Kontainer Microservices diatur agar baru menyala setelah RabbitMQ benar-benar sehat (_healthy_) dan port database terbuka.
2. **Docker Volumes:** Setiap database MySQL diberi `volume` (misal: `eai_uas_order_db_data`) agar ketika komputer direstart, data pesanan dan stok galon tidak hilang.
3. **Environment Variables:** Keamanan dijaga di mana password database (_MYSQL_ROOT_PASSWORD_) tidak ditulis _hardcode_ di dalam kode JS, melainkan di-_inject_ melalui file `.env`.

---

## 🗃️ Format Data & Protokol Tiap Sistem

Sistem ini sengaja didesain untuk mensimulasikan lingkungan perusahaan (Enterprise) yang seringkali menggunakan protokol dan format data yang beraneka ragam (Heterogenitas Data).

### 1. Order, Inventory, dan Delivery Service (Modern)

- **Protokol:** HTTP POST
- **Tipe Komunikasi:** GraphQL API
- **Format Data (In & Out):** JSON (JavaScript Object Notation)
- **Implementasi Tingkat Lanjut (High Level):**
  - **Schema Modular:** Definisi _schema_ GraphQL dipisah ke dalam file mandiri (`schema.js`).
  - **Multiple Mutations:** Setiap _service_ dibekali minimal 2 mutasi fungsional (contoh: `createOrder` dan `deleteOrder`).
  - **Modular Resolvers:** Logika fungsi dan pemanggilan _database_ dipisahkan ke `resolvers.js` menggunakan teknik _Dependency Injection_.
  - **Error Handling:** Semua operasi database dibungkus dengan blok `try-catch` sehingga jika terjadi kegagalan (misal data tidak ditemukan), aplikasi tidak akan _crash_, melainkan memunculkan pesan _Error_ terstruktur khas GraphQL.
- **Alasan:** Menghindari _over-fetching_ (mengambil data tak perlu). UI Frontend dapat me- _request_ bentuk data (kolom) sesuai kebutuhan (misal: hanya butuh "status" pesanan tanpa perlu menarik semua biodata pelanggan).

### 2. Finance Service (Simulasi Sistem Legacy/Jadul)

- **Protokol:** REST API (HTTP POST)
- **Format Data (In & Out):** XML (eXtensible Markup Language)
- **Alasan:** Banyak sistem perbankan kuno/ERP yang masih meminta XML. Sistem ini menuntut API Gateway SIDAGAS bertindak sebagai **Message Translator** yang mengubah JSON pengguna menjadi XML sebelum diserahkan ke Finance.

### 3. Komunikasi Antar Layanan (Server-to-Server)

- **Protokol:** AMQP (Advanced Message Queuing Protocol) via RabbitMQ.
- **Tipe Komunikasi:** Event-Driven (Asynchronous / Publish-Subscribe)
- **Cara Kerja:** Layanan _Order_ tidak memanggil API _Inventory_ secara HTTP. _Order_ hanya berteriak: _"Hei, ada pesanan baru!"_ ke RabbitMQ. _Inventory_ yang kebetulan sedang _standby_ mendengar teriakan tersebut dan langsung memotong stoknya sendiri.

---

## 🚀 Panduan Menjalankan Proyek (Langkah Rinci)

Pastikan aplikasi **Docker Desktop** (atau Docker Engine) telah terinstal dan dalam status _Running_ di komputer Anda.

### Tahap 1: Persiapan Repository

1. **Clone Repository (Jika belum)**
   Buka terminal/CMD Anda, lalu jalankan:
   ```bash
   git clone https://github.com/[username_anda]/SIDAGAS.git
   cd SIDAGAS/EAI_UAS
   ```
2. **Konfigurasi Environment**
   Salin file konfigurasi lingkungan. Secara _default_, sistem sudah bisa jalan langsung tanpa pengubahan.
   _(Jika file `.env` belum ada, buatlah berdasarkan `.env.example`)_.

### Tahap 2: Menyalakan Infrastruktur (Docker Compose)

Dari dalam direktori `EAI_UAS` (yang memuat file `docker-compose.yml`), ketikkan satu baris sakti berikut di terminal Anda:

```bash
docker compose up --build -d
```

**Penjelasan Perintah:**

- `up`: Memerintahkan Docker menyalakan seluruh sistem.
- `--build`: Memaksa Docker untuk membaca ulang kode Javascript Node.js Anda dan membangun _image_ baru jika ada perubahan.
- `-d`: (Detached Mode) Menjalankan server di latar belakang agar terminal Anda tidak terkunci dan tetap bisa digunakan.

### Tahap 3: Verifikasi Sistem Telah Aktif

Membangun puluhan komponen dan 4 database MySQL secara bersamaan membutuhkan waktu sekitar **30 - 60 detik** pada saat pertama kali berjalan (tergantung kecepatan laptop).

Cara mengecek apakah semua sudah _Running_:

1. **Buka Docker Desktop** -> Tab **Containers**. Pastikan grup `eai_uas` memiliki 10 kontainer dengan status ikon **Hijau (Running)**.
2. **Cek Koneksi Gateway:** Buka Web Browser, ketik `http://localhost:3000`. Jika terbuka informasi server (atau pesan respons JSON), artinya sistem _online_.
3. **Cek RabbitMQ:** Buka `http://localhost:15672`. Login dengan Username `guest` dan Password `guest`. Jika muncul _dashboard_ statistik pesan, sistem _Broker_ sehat.

### Tahap 4: Mengintegrasikan ke Frontend (Laravel UI)

Setelah Backend Microservices Docker Anda menyala sehat:

1. Buka tab terminal baru.
2. Masuk ke folder Laravel:
   ```bash
   cd ../Backend
   ```
3. Instal library PHP dan NPM:
   ```bash
   composer install
   npm install
   ```
4. Jalankan Laravel:
   ```bash
   php artisan serve
   ```
   Lalu di terminal baru, jalankan CSS Compiler:
   ```bash
   npm run dev
   ```
5. **Sukses!** Buka browser ke `http://localhost:8000/login`. Seluruh transaksi yang Anda lakukan di antarmuka Laravel sekarang secara otomatis dialirkan, diubah formatnya, dan disalurkan ke kontainer-kontainer Microservice Docker secara _real-time_.
