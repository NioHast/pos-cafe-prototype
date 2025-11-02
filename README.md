# POS Cafe Inventory Prototype

> **Prototipe Sistem Manajemen Inventori untuk Kafe**  
> Laravel + Filament + PostgreSQL

## Tentang Proyek

Ini adalah **prototipe** aplikasi Point of Sale (POS) untuk kafe yang fokus pada **manajemen inventori** berbasis resep dengan logika pengurangan stok FIFO/FEFO. Prototipe ini dibangun menggunakan Laravel dan Filament untuk demonstrasi fungsi inti inventori.

### Scope Prototipe (Yang Sudah Dibuat)
✅ Manajemen Kategori Menu  
✅ Manajemen Bahan Baku (Ingredients) dengan Batch Tracking  
✅ Manajemen Menu dengan Resep  
✅ Pencatatan Waste (Bahan Terbuang)  
✅ Manajemen User & Role  
✅ **Simulasi Pesanan dengan Pengurangan Stok Otomatis (FIFO/FEFO)**  

### Yang Belum Diimplementasikan (Future Development)
❌ Transaksi Pesanan Sungguhan (Orders & Order Items)  
❌ UI Kasir (React PWA)  
❌ UI Pelanggan (Livewire)  
❌ Promosi & Diskon  
❌ Laporan Keuangan  
❌ Data Mining & Analytics  
❌ Integrasi Payment Gateway  

## Quick Start

Lihat **[SETUP_GUIDE.md](SETUP_GUIDE.md)** untuk instruksi lengkap.

### Instalasi Cepat

```powershell
# 1. Install dependencies
composer install
npm install

# 2. Setup environment
Copy-Item .env.example .env
# Edit .env untuk konfigurasi PostgreSQL

# 3. Generate key & migrate
php artisan key:generate
php artisan migrate:fresh --seed

# 4. Run servers
php artisan serve
# Terminal baru:
npm run dev
```

### Login Admin

- URL: http://localhost:8000/admin
- Email: `admin@example.com`
- Password: `password`

## Fitur Utama

### 1. Manajemen Inventori
- **Bahan Baku**: CRUD dengan unit, threshold stok rendah
- **Batch Tracking**: Kelola batch stok dengan expiry date dan cost
- **Total Stock Calculation**: Otomatis hitung total dari semua batch

### 2. Menu & Resep
- **Menu Management**: Harga normal & harga mahasiswa, status available/sold_out
- **Resep (Recipe)**: Definisikan bahan yang digunakan per porsi
- **Cost Calculation**: Hitung HPP (Harga Pokok Penjualan) otomatis

### 3. Simulasi Pesanan 🎯
Fitur unggulan prototipe ini:
- Form untuk menambah multiple item pesanan
- **Pengurangan stok otomatis** berdasarkan resep
- **Logika FIFO/FEFO**: Prioritas batch dengan expiry terdekat
- **Validasi stok**: Error jika stok tidak mencukupi
- **Transaction rollback**: Tidak ada perubahan jika ada error
- **Detail notification**: Tampilkan ringkasan pengurangan stok

## Struktur Penting

```
app/
├── Filament/
│   ├── Pages/
│   │   └── SimulateOrder.php          # Custom page untuk simulasi
│   └── Resources/
│       ├── CategoryResource.php
│       ├── IngredientResource.php     # Dengan BatchesRelationManager
│       ├── MenuResource.php           # Dengan IngredientsRelationManager
│       ├── UserResource.php
│       └── WasteRecordResource.php
├── Models/                             # 8 Models dengan relasi lengkap
└── Services/
    └── InventoryService.php           # Logika FIFO/FEFO

database/
├── migrations/                         # 8 migration files
└── seeders/
    ├── RoleSeeder.php
    ├── UserSeeder.php
    └── CategorySeeder.php

planning/                               # Dokumentasi arsitektur
├── 01_requirements.md
├── 02_database_schema.dbml
├── 03_class_diagram.mmd
└── 04_indexeddb_schema.md
```

## Logika FIFO/FEFO

```php
// InventoryService::decreaseStockForOrder()

// 1. Ambil semua batch dengan stok > 0
// 2. Sort berdasarkan:
//    - expiry_date ASC (FEFO - First Expired First Out)
//    - received_at ASC (FIFO - First In First Out)
// 3. Loop batch dari yang pertama:
//    - Kurangi stok dari batch ini
//    - Jika masih kurang, lanjut ke batch berikutnya
// 4. Gunakan DB Transaction untuk atomicity
```

## Database Schema

PostgreSQL dengan 8 tabel utama:
- `roles`
- `users` (SoftDeletes)
- `categories` (SoftDeletes)
- `ingredients` (SoftDeletes)
- `ingredient_batches`
- `menu` (SoftDeletes)
- `menu_ingredients` (pivot)
- `waste_records`

Lihat `planning/02_database_schema.dbml` untuk detail lengkap.

## Testing Workflow

1. **Setup Bahan & Batch**
   - Tambah ingredient: Kopi, Susu, Gula
   - Tambah beberapa batch dengan expiry date berbeda

2. **Buat Menu & Resep**
   - Menu: Kopi Susu
   - Resep: 15g Kopi + 100ml Susu + 10g Gula

3. **Simulasi Pesanan**
   - Buka halaman "Simulasi Pesanan"
   - Pilih "Kopi Susu" x 5
   - Klik "Simulasikan Pesanan"
   - Cek perubahan stok di batch (batch dengan expiry terdekat berkurang dulu)

## Dokumentasi

- **[SETUP_GUIDE.md](SETUP_GUIDE.md)** - Setup lengkap & troubleshooting
- **planning/** - Requirements, database schema, class diagram

## 🛠 Tech Stack

- **Backend**: PHP 8.4, Laravel 12
- **Admin Panel**: Filament 3
- **Database**: PostgreSQL 18
- **Frontend**: Vite, Tailwind CSS (via Filament)

## 📝 License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
