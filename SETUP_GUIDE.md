# Setup Prototipe POS Cafe Inventory Management

## Prasyarat
- PHP 8.4+
- PostgreSQL 18
- Composer
- Node.js & npm (untuk Vite)

## Langkah Setup

### 1. Install Dependencies

```powershell
composer install
npm install
```

### 2. Konfigurasi Environment

Copy file `.env.example` menjadi `.env`:

```powershell
Copy-Item .env.example .env
```

Edit file `.env` dan sesuaikan konfigurasi database PostgreSQL:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_cafe_db
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Generate Application Key

```powershell
php artisan key:generate
```

### 4. Jalankan Migrasi dan Seeder

```powershell
php artisan migrate:fresh --seed
```

Ini akan membuat:
- Tabel: roles, users, categories, ingredients, ingredient_batches, menu, menu_ingredients, waste_records
- Role: admin, cashier, student
- User admin: email `admin@example.com`, password `password`
- Beberapa kategori: Minuman, Makanan, Snack, Dessert

### 5. Install Filament (jika belum terinstall)

```powershell
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

### 6. Buat Admin Filament (opsional, karena sudah ada dari seeder)

Jika ingin membuat user admin baru untuk Filament:

```powershell
php artisan make:filament-user
```

### 7. Jalankan Development Server

Terminal 1 - Laravel Server:
```powershell
php artisan serve
```

Terminal 2 - Vite (untuk compile assets):
```powershell
npm run dev
```

### 8. Akses Aplikasi

- Admin Panel: http://localhost:8000/admin
- Login dengan:
  - Email: `admin@example.com`
  - Password: `password`

## Fitur yang Tersedia

### 1. Manajemen Kategori
- CRUD kategori menu (Minuman, Makanan, dll)
- Soft delete support

### 2. Manajemen Bahan Baku (Ingredients)
- CRUD bahan baku dengan unit (gram, ml, pcs)
- Ambang batas stok rendah
- Lihat total stok dari semua batch
- **Relation Manager** untuk mengelola batch stok:
  - Tambah batch baru dengan qty, expiry date, cost per unit
  - Lihat semua batch dengan info kadaluarsa
  - Otomatis sorting berdasarkan FEFO

### 3. Manajemen Menu
- CRUD menu dengan harga normal dan harga mahasiswa
- Status: available/sold_out
- Kategori menu
- **Relation Manager** untuk resep:
  - Definisikan bahan yang digunakan per porsi
  - Lihat stok tersedia untuk setiap bahan

### 4. Pencatatan Waste (Bahan Terbuang)
- Catat bahan yang terbuang dengan alasan
- Otomatis mencatat user yang input (recorded_by)

### 5. Manajemen User
- CRUD user dengan role (admin, cashier, student)
- Password hashing otomatis
- Soft delete support

### 6. **Simulasi Pesanan** 🎯
- **Custom Page** khusus untuk simulasi
- Form repeater untuk menambah multiple item
- Select menu dan input quantity
- Tombol "Simulasikan Pesanan" yang akan:
  - Mengecek ketersediaan stok
  - Mengurangi stok bahan baku sesuai resep
  - Menggunakan logika **FIFO/FEFO** (batch dengan expiry_date terdekat atau received_at terlama)
  - Menampilkan notifikasi sukses dengan detail pengurangan
  - Menampilkan error jika stok tidak mencukupi
- **TIDAK** membuat record pesanan sungguhan

## Struktur Database

Lihat file `planning/02_database_schema.dbml` untuk detail lengkap skema database.

### Tabel Utama:
- `roles` - Role pengguna
- `users` - Data user (SoftDeletes)
- `categories` - Kategori menu (SoftDeletes)
- `ingredients` - Bahan baku (SoftDeletes)
- `ingredient_batches` - Batch stok bahan
- `menu` - Menu kafe (SoftDeletes)
- `menu_ingredients` - Resep (pivot table)
- `waste_records` - Pencatatan waste

## Testing Workflow

### Skenario 1: Setup Inventori Dasar

1. **Login** ke admin panel
2. **Tambah Bahan Baku**:
   - Buat ingredient: "Kopi Bubuk" (unit: gram, threshold: 500)
   - Buat ingredient: "Susu" (unit: ml, threshold: 1000)
   - Buat ingredient: "Gula" (unit: gram, threshold: 500)

3. **Tambah Batch Stok**:
   - Masuk ke halaman edit "Kopi Bubuk"
   - Ke tab "Batch Stok"
   - Tambah batch: 1000 gram, expiry: 2026-01-01, cost: 50
   - Tambah batch: 500 gram, expiry: 2025-12-01, cost: 48
   - Lakukan hal sama untuk bahan lainnya

4. **Buat Menu**:
   - Menu: "Kopi Susu"
   - Kategori: Minuman
   - Harga: 15000, Harga Mahasiswa: 12000
   - Status: Available
   
5. **Tambah Resep**:
   - Masuk ke tab "Resep (Bahan)"
   - Tambah: Kopi Bubuk - 15 gram
   - Tambah: Susu - 100 ml
   - Tambah: Gula - 10 gram

### Skenario 2: Simulasi Pesanan & FIFO/FEFO

1. **Buka halaman "Simulasi Pesanan"**
2. **Tambah item**:
   - Pilih menu: "Kopi Susu"
   - Quantity: 10
3. **Klik "Simulasikan Pesanan"**
4. **Observasi**:
   - Sistem akan mengurangi stok dari batch dengan expiry terdekat
   - Notifikasi sukses dengan detail pengurangan stok
5. **Verifikasi**:
   - Buka halaman Ingredients
   - Lihat perubahan total stok
   - Masuk ke batch detail, lihat batch mana yang berkurang

### Skenario 3: Stok Tidak Cukup

1. **Simulasi pesanan dengan quantity besar** (misal: 100)
2. **Observasi**:
   - Sistem akan menampilkan error
   - Pesan menunjukkan bahan apa yang tidak cukup
   - **Tidak ada stok yang berkurang** (rollback transaction)

### Skenario 4: Pencatatan Waste

1. **Buka "Pencatatan Waste"**
2. **Tambah record**:
   - Ingredient: pilih bahan
   - Quantity: masukkan jumlah
   - Reason: "Kadaluarsa" / "Rusak" / dll
3. **Submit**
4. **recorded_by** otomatis terisi user yang login

## Logika FIFO/FEFO

Method `decreaseStockForOrder()` di `InventoryService`:

```php
// Priority ordering:
1. Expiry date terlama (FEFO - First Expired, First Out)
2. Received date terlama (FIFO - First In, First Out)

// Algoritma:
- Ambil semua batch dengan qty > 0
- Sort by: expiry_date ASC, received_at ASC
- Loop dan kurangi stok dari batch pertama hingga kebutuhan terpenuhi
- Jika batch habis, lanjut ke batch berikutnya
- Gunakan database transaction untuk atomicity
```

## Troubleshooting

### Error: "SQLSTATE[42P01]: Undefined table"
Pastikan migrasi sudah dijalankan:
```powershell
php artisan migrate:fresh --seed
```

### Error: "Class 'Filament\...' not found"
Install Filament:
```powershell
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

### Tidak bisa login ke admin
Cek apakah user admin sudah dibuat:
```powershell
php artisan tinker
>>> User::where('email', 'admin@example.com')->first()
```

Jika null, jalankan seeder:
```powershell
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
```

## Next Steps (Di Luar Scope Prototipe Ini)

- [ ] Implementasi fitur Orders & Order Items
- [ ] Integrasi Promosi
- [ ] UI Kasir (React PWA)
- [ ] UI Pelanggan (Livewire)
- [ ] Laporan Keuangan
- [ ] Data Mining & Analytics
- [ ] Payment Gateway (Midtrans)
- [ ] WhatsApp Gateway untuk struk digital

## Dokumentasi Tambahan

- Requirements: `planning/01_requirements.md`
- Database Schema: `planning/02_database_schema.dbml`
- Class Diagram: `planning/03_class_diagram.mmd`
- IndexedDB Schema: `planning/04_indexeddb_schema.md`
