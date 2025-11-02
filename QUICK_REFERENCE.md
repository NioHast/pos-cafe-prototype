# Quick Reference - POS Cafe Inventory Prototype

## 🚀 Setup Cepat

```powershell
# Option 1: Gunakan script otomatis
.\setup.ps1

# Option 2: Manual
composer install
npm install
Copy-Item .env.example .env
# Edit .env untuk PostgreSQL config
php artisan key:generate
php artisan migrate:fresh --seed
php artisan db:seed --class=DemoDataSeeder
```

## 🔧 Perintah Berguna

### Development Servers
```powershell
# Terminal 1 - Laravel
php artisan serve

# Terminal 2 - Vite
npm run dev
```

### Database
```powershell
# Reset database + seed data awal
php artisan migrate:fresh --seed

# Tambah data demo (ingredients & menu)
php artisan db:seed --class=DemoDataSeeder

# Lihat database di Tinker
php artisan tinker
>>> User::all()
>>> Ingredient::with('batches')->get()
>>> Menu::with('menuIngredients')->get()
```

### Filament
```powershell
# Buat user admin baru (jika diperlukan)
php artisan make:filament-user

# Clear cache Filament
php artisan filament:cache-components
```

## 📋 Data Default Setelah Seeding

### Roles
- admin
- cashier
- student

### User Admin
- Email: `admin@example.com`
- Password: `password`

### Categories
- Minuman
- Makanan
- Snack
- Dessert

### Demo Data (jika run DemoDataSeeder)

**Ingredients:**
- Kopi Bubuk (2 batches, expiry berbeda untuk demo FIFO/FEFO)
- Susu (2 batches)
- Gula (1 batch)
- Matcha Powder (1 batch)
- Coklat Bubuk (1 batch)

**Menu:**
- Kopi Susu (15g kopi + 150ml susu + 10g gula) - Rp 15.000 / Rp 12.000 (student)
- Matcha Latte (8g matcha + 200ml susu + 12g gula) - Rp 25.000 / Rp 20.000 (student)
- Coklat Panas (20g coklat + 180ml susu + 15g gula) - Rp 18.000 / Rp 15.000 (student)

## 🎯 Testing Scenarios

### Skenario 1: FIFO/FEFO Testing
1. Login ke admin panel
2. Buka **Ingredients** > **Kopi Bubuk**
3. Lihat tab **Batch Stok** - ada 2 batch dengan expiry berbeda
4. Catat quantity masing-masing batch
5. Buka **Simulasi Pesanan**
6. Pesan **Kopi Susu x 10**
7. Klik **Simulasikan Pesanan**
8. Kembali ke Kopi Bubuk > Batch Stok
9. **Verifikasi**: Batch dengan expiry terdekat berkurang dulu

### Skenario 2: Insufficient Stock
1. Buka **Simulasi Pesanan**
2. Pesan **Kopi Susu x 200** (quantity sangat besar)
3. Klik **Simulasikan Pesanan**
4. **Expected**: Error "Stok tidak mencukupi"
5. **Verifikasi**: Tidak ada perubahan stok (rollback)

### Skenario 3: Multiple Items
1. Buka **Simulasi Pesanan**
2. Klik **Tambah Item** beberapa kali
3. Item 1: Kopi Susu x 5
4. Item 2: Matcha Latte x 3
5. Item 3: Coklat Panas x 2
6. Klik **Simulasikan Pesanan**
7. **Verifikasi**: Semua bahan berkurang sesuai resep

### Skenario 4: Waste Recording
1. Buka **Pencatatan Waste**
2. Klik **New**
3. Pilih bahan: Susu
4. Quantity: 100
5. Reason: "Kadaluarsa"
6. Submit
7. **Verifikasi**: Recorded by otomatis terisi user login

## 🔍 Inspecting Data

### Via Tinker
```php
php artisan tinker

// Check total stock
$ingredient = Ingredient::find(1);
$ingredient->getTotalStock();

// Check menu cost
$menu = Menu::find(1);
$menu->calculateCost();

// Check batches ordered by FEFO
IngredientBatch::where('ingredient_id', 1)
    ->orderBy('expiry_date', 'asc')
    ->orderBy('received_at', 'asc')
    ->get();

// Simulate order programmatically
$service = new \App\Services\InventoryService();
$result = $service->decreaseStockForOrder([
    ['menu_id' => 1, 'quantity' => 5]
]);
```

### Via Database
```sql
-- Check total stock per ingredient
SELECT 
    i.name,
    i.unit,
    SUM(ib.quantity) as total_stock
FROM ingredients i
LEFT JOIN ingredient_batches ib ON i.id = ib.ingredient_id
GROUP BY i.id, i.name, i.unit;

-- Check menu with recipe
SELECT 
    m.name as menu_name,
    i.name as ingredient_name,
    mi.quantity_used,
    i.unit
FROM menu m
JOIN menu_ingredients mi ON m.id = mi.menu_id
JOIN ingredients i ON mi.ingredient_id = i.id;
```

## 🐛 Troubleshooting

### "SQLSTATE[42P01]: Undefined table"
```powershell
php artisan migrate:fresh --seed
```

### "Class 'Filament\...' not found"
```powershell
composer require filament/filament:"^3.0"
php artisan filament:install --panels
```

### Login tidak bisa
```powershell
php artisan tinker
>>> User::where('email', 'admin@example.com')->first()
# Jika null:
>>> exit
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
```

### Error "getTotalStock() on null"
Pastikan ingredient punya relasi ke batches:
```powershell
php artisan tinker
>>> $ing = Ingredient::with('batches')->first()
>>> $ing->batches
```

### Vite manifest not found
```powershell
npm install
npm run build
# Atau untuk dev:
npm run dev
```

## 📂 File Locations

### Models
- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/Category.php`
- `app/Models/Ingredient.php`
- `app/Models/IngredientBatch.php`
- `app/Models/Menu.php`
- `app/Models/MenuIngredient.php`
- `app/Models/WasteRecord.php`

### Services
- `app/Services/InventoryService.php`

### Filament Resources
- `app/Filament/Resources/CategoryResource.php`
- `app/Filament/Resources/IngredientResource.php`
- `app/Filament/Resources/MenuResource.php`
- `app/Filament/Resources/UserResource.php`
- `app/Filament/Resources/WasteRecordResource.php`

### Filament Pages
- `app/Filament/Pages/SimulateOrder.php`
- `resources/views/filament/pages/simulate-order.blade.php`

### Migrations
- `database/migrations/2024_10_29_000001_create_roles_table.php`
- `database/migrations/2024_10_29_000002_create_users_table.php`
- `database/migrations/2024_10_29_000003_create_categories_table.php`
- `database/migrations/2024_10_29_000004_create_ingredients_table.php`
- `database/migrations/2024_10_29_000005_create_ingredient_batches_table.php`
- `database/migrations/2024_10_29_000006_create_menu_table.php`
- `database/migrations/2024_10_29_000007_create_menu_ingredients_table.php`
- `database/migrations/2024_10_29_000008_create_waste_records_table.php`

### Seeders
- `database/seeders/RoleSeeder.php`
- `database/seeders/UserSeeder.php`
- `database/seeders/CategorySeeder.php`
- `database/seeders/DemoDataSeeder.php`

## 🎨 Navigasi Admin Panel

```
Admin Panel (http://localhost:8000/admin)
│
├── 📦 Inventory
│   ├── Categories (Kategori)
│   ├── Ingredients (Bahan Baku)
│   │   └── [Edit] → Tab: Batch Stok
│   ├── Menu
│   │   └── [Edit] → Tab: Resep (Bahan)
│   ├── Pencatatan Waste
│   └── ✨ Simulasi Pesanan
│
└── 👤 Admin
    └── Users
```

## 🔐 Security Notes

**PENTING untuk Production:**
1. Ganti `APP_KEY` di `.env`
2. Ganti password default admin
3. Set `APP_DEBUG=false`
4. Set `APP_ENV=production`
5. Konfigurasi CORS jika diperlukan
6. Enable HTTPS
7. Review & secure database credentials

## 📖 Next Steps

Setelah prototipe berjalan, development selanjutnya:
1. ✅ Orders & Order Items (real transactions)
2. ✅ Promotions module
3. ✅ React PWA for Cashier
4. ✅ Livewire UI for Customer
5. ✅ Payment Gateway integration
6. ✅ Financial Reports
7. ✅ Data Mining & Analytics
