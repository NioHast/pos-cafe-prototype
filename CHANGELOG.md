# Changelog - POS Cafe Inventory Prototype

## [1.0.0] - 2025-10-29

### ✨ Features Implemented

#### Database & Migrations
- ✅ 8 migration files sesuai schema database (DBML)
- ✅ Relational database dengan foreign keys
- ✅ Soft deletes untuk: users, categories, ingredients, menu
- ✅ Proper indexing dan constraints

#### Models
- ✅ 8 Eloquent models dengan relasi lengkap
  - Role
  - User (SoftDeletes, relasi ke Role)
  - Category (SoftDeletes)
  - Ingredient (SoftDeletes, method getTotalStock())
  - IngredientBatch
  - Menu (SoftDeletes, method calculateCost())
  - MenuIngredient (pivot)
  - WasteRecord
- ✅ Semua relasi: belongsTo, hasMany, belongsToMany
- ✅ Custom methods untuk business logic

#### Service Layer
- ✅ InventoryService dengan method:
  - `decreaseStockForOrder()` - Pengurangan stok dengan FIFO/FEFO
  - `canFulfillOrder()` - Validasi ketersediaan stok
- ✅ Database transaction untuk data integrity
- ✅ Exception handling untuk insufficient stock

#### Filament Resources
- ✅ CategoryResource
  - List, Create, Edit pages
  - Soft delete management
  - Menu count column
  
- ✅ IngredientResource
  - List, Create, Edit pages
  - Total stock calculation (live)
  - Low stock indicator (badge coloring)
  - **BatchesRelationManager** untuk manage batch stok
  
- ✅ MenuResource
  - List, Create, Edit pages
  - Price & student_price fields
  - Status (available/sold_out)
  - **IngredientsRelationManager** untuk manage resep
  
- ✅ WasteRecordResource
  - List, Create, Edit pages
  - Auto-fill recorded_by
  
- ✅ UserResource
  - List, Create, Edit pages
  - Password hashing
  - Role management

#### Custom Filament Page
- ✅ SimulateOrder Page
  - Form dengan Repeater untuk multiple items
  - Select menu & quantity input
  - Action button untuk simulasi
  - Integration dengan InventoryService
  - Success/error notifications dengan detail
  - Auto-reset form setelah sukses

#### Seeders
- ✅ RoleSeeder (admin, cashier, student)
- ✅ UserSeeder (admin@example.com)
- ✅ CategorySeeder (Minuman, Makanan, Snack, Dessert)
- ✅ DemoDataSeeder (ingredients, batches, menu dengan resep)

#### Documentation
- ✅ README.md - Project overview
- ✅ SETUP_GUIDE.md - Detailed setup instructions
- ✅ QUICK_REFERENCE.md - Quick commands & scenarios
- ✅ setup.ps1 - Automated setup script
- ✅ CHANGELOG.md - This file

### 🎯 Core Features

1. **Inventory Management**
   - Track ingredients with units (gram, ml, pcs)
   - Multiple batch tracking per ingredient
   - Low stock threshold alerts
   - Expiry date monitoring

2. **Recipe Management**
   - Define ingredients per menu item
   - Quantity per portion
   - Cost calculation (HPP)

3. **FIFO/FEFO Stock Management**
   - Automatic stock deduction based on expiry date (FEFO)
   - Falls back to received date (FIFO)
   - Multi-batch deduction
   - Transaction rollback on error

4. **Waste Tracking**
   - Record wasted ingredients
   - Reason logging
   - User accountability (recorded_by)

5. **Order Simulation**
   - Test stock deduction without creating real orders
   - Multiple items per simulation
   - Real-time stock updates
   - Detailed feedback

### 📦 Tech Stack

- **Backend**: PHP 8.2, Laravel 11
- **Admin Panel**: Filament 3
- **Database**: PostgreSQL 15+
- **Frontend**: Vite, Tailwind CSS (via Filament)
- **Others**: Composer, NPM

### 🚀 Installation

See [SETUP_GUIDE.md](SETUP_GUIDE.md) for detailed instructions.

Quick start:
```powershell
.\setup.ps1
```

### 🔧 Configuration

Required `.env` settings:
- Database: PostgreSQL connection
- APP_KEY: Auto-generated
- APP_ENV: development
- APP_DEBUG: true

### 📝 Default Credentials

- Email: `admin@example.com`
- Password: `password`

### 🎨 UI Features

- Responsive admin panel
- Dark mode support (via Filament)
- Searchable & sortable tables
- Inline editing
- Bulk actions
- Form validation
- Real-time notifications

### ⚠️ Known Limitations

This is a **prototype** focusing on inventory management only.

**Not Implemented:**
- Real order transactions (orders & order_items tables)
- Cashier UI (React PWA)
- Customer UI (Livewire)
- Promotions & discounts
- Financial reports
- Data mining & analytics
- Payment gateway integration
- Student profiles
- Cashier sessions

### 🔮 Future Development

Priority features for next iteration:
1. Orders & Order Items implementation
2. Promotions module
3. React PWA for Cashier (offline-first)
4. Livewire Customer Portal
5. Payment integration (Midtrans)
6. Financial reporting
7. ML Analytics (Python/FastAPI)

### 🐛 Bug Fixes

None reported yet (initial release).

### 🙏 Credits

- Laravel Framework
- Filament PHP
- PostgreSQL
- Planning documents in `/planning` folder

---

**Version**: 1.0.0  
**Release Date**: October 29, 2025  
**Status**: Prototype - Inventory Module Only
