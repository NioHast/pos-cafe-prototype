# Project Structure - POS Cafe Inventory Prototype

## 📁 Directory Overview

```
POSCafeInventoryPrototype/
│
├── 📂 app/
│   ├── Filament/                           # Filament Admin Panel
│   │   ├── Pages/
│   │   │   └── SimulateOrder.php          # Custom page untuk simulasi pesanan
│   │   └── Resources/
│   │       ├── CategoryResource.php        # CRUD Kategori
│   │       │   └── Pages/                  # List, Create, Edit pages
│   │       ├── IngredientResource.php      # CRUD Bahan Baku
│   │       │   ├── Pages/
│   │       │   └── RelationManagers/
│   │       │       └── BatchesRelationManager.php  # Manage batch stok
│   │       ├── MenuResource.php            # CRUD Menu
│   │       │   ├── Pages/
│   │       │   └── RelationManagers/
│   │       │       └── IngredientsRelationManager.php  # Manage resep
│   │       ├── UserResource.php            # CRUD User
│   │       │   └── Pages/
│   │       └── WasteRecordResource.php     # CRUD Waste Record
│   │           └── Pages/
│   │
│   ├── Http/
│   │   └── Controllers/                    # (Kosong untuk prototype ini)
│   │
│   ├── Models/                             # Eloquent Models
│   │   ├── Category.php                    # Model Kategori (SoftDeletes)
│   │   ├── Ingredient.php                  # Model Bahan (SoftDeletes, getTotalStock())
│   │   ├── IngredientBatch.php             # Model Batch Stok
│   │   ├── Menu.php                        # Model Menu (SoftDeletes, calculateCost())
│   │   ├── MenuIngredient.php              # Model Resep (Pivot)
│   │   ├── Role.php                        # Model Role
│   │   ├── User.php                        # Model User (SoftDeletes)
│   │   └── WasteRecord.php                 # Model Waste
│   │
│   └── Services/
│       └── InventoryService.php            # 🎯 Core business logic (FIFO/FEFO)
│
├── 📂 database/
│   ├── migrations/                         # Database Schema
│   │   ├── 2024_10_29_000001_create_roles_table.php
│   │   ├── 2024_10_29_000002_create_users_table.php
│   │   ├── 2024_10_29_000003_create_categories_table.php
│   │   ├── 2024_10_29_000004_create_ingredients_table.php
│   │   ├── 2024_10_29_000005_create_ingredient_batches_table.php
│   │   ├── 2024_10_29_000006_create_menu_table.php
│   │   ├── 2024_10_29_000007_create_menu_ingredients_table.php
│   │   └── 2024_10_29_000008_create_waste_records_table.php
│   │
│   └── seeders/                            # Database Seeders
│       ├── DatabaseSeeder.php              # Main seeder
│       ├── RoleSeeder.php                  # Seed roles (admin, cashier, student)
│       ├── UserSeeder.php                  # Seed admin user
│       ├── CategorySeeder.php              # Seed categories
│       └── DemoDataSeeder.php              # 🌱 Demo data (ingredients, menu)
│
├── 📂 planning/                            # 📋 Project Documentation
│   ├── 01_requirements.md                  # Spesifikasi lengkap
│   ├── 02_database_schema.dbml             # Schema database (DBML)
│   ├── 03_class_diagram.mmd               # Class diagram (Mermaid)
│   └── 04_indexeddb_schema.md             # Schema offline DB (future)
│
├── 📂 resources/
│   └── views/
│       └── filament/
│           └── pages/
│               └── simulate-order.blade.php  # View untuk SimulateOrder page
│
├── 📂 routes/
│   ├── web.php                             # Web routes (default Laravel)
│   └── console.php                         # Console commands
│
├── 📄 README.md                            # 📖 Project overview
├── 📄 SETUP_GUIDE.md                       # 🚀 Panduan setup lengkap
├── 📄 QUICK_REFERENCE.md                   # ⚡ Quick commands & tips
├── 📄 CHANGELOG.md                         # 📝 Version history
├── 📄 PROJECT_STRUCTURE.md                 # 📁 This file
├── 📄 setup.ps1                            # 🤖 Automated setup script
│
├── .env.example                            # Environment template
├── composer.json                           # PHP dependencies
├── package.json                            # NPM dependencies
└── artisan                                 # Laravel CLI

```

## 🎯 Key Components Explained

### 1. Models (`app/Models/`)

#### Core Models
- **User**: Authentication, roles, soft deletes
- **Role**: User roles (admin, cashier, student)
- **Category**: Menu categories, soft deletes
- **Ingredient**: Bahan baku dengan tracking stok
- **IngredientBatch**: Batch stok dengan expiry date
- **Menu**: Menu items dengan harga
- **MenuIngredient**: Resep (pivot table)
- **WasteRecord**: Pencatatan bahan terbuang

#### Model Relationships
```
Role 1---* User
User 1---* WasteRecord (as recorder)
Category 1---* Menu
Ingredient 1---* IngredientBatch
Ingredient *---* Menu (through MenuIngredient)
Menu 1---* MenuIngredient
Ingredient 1---* MenuIngredient
Ingredient 1---* WasteRecord
```

### 2. Services (`app/Services/`)

#### InventoryService
**Purpose**: Core business logic untuk manajemen stok

**Methods**:
- `decreaseStockForOrder(array $items)`: Kurangi stok dengan FIFO/FEFO
- `canFulfillOrder(array $items)`: Cek ketersediaan stok

**Features**:
- Database transactions
- FIFO/FEFO logic
- Exception handling
- Detailed result logging

### 3. Filament Resources

#### Resource Structure
```
Resource/
├── ResourceName.php            # Main resource file
├── Pages/
│   ├── ListResourceName.php   # Index page
│   ├── CreateResourceName.php # Create page
│   └── EditResourceName.php   # Edit page
└── RelationManagers/          # Optional relation managers
    └── RelationName.php
```

#### Key Resources
1. **CategoryResource**: Simple CRUD
2. **IngredientResource**: With BatchesRelationManager
3. **MenuResource**: With IngredientsRelationManager
4. **WasteRecordResource**: Auto-fill recorded_by
5. **UserResource**: Password hashing, role selection

### 4. Custom Filament Page

#### SimulateOrder Page
**Location**: `app/Filament/Pages/SimulateOrder.php`

**Components**:
- Form with Repeater (multiple items)
- Select for menu selection
- TextInput for quantity
- Action buttons (Simulate, Reset)

**Integration**:
- Calls InventoryService
- Shows notifications
- Auto-reset on success

### 5. Migrations

**Naming Convention**: `YYYY_MM_DD_HHMMSS_create_tablename_table.php`

**Features**:
- Foreign key constraints
- Soft deletes where needed
- Proper data types
- Indexes on foreign keys

### 6. Seeders

#### Execution Order
1. RoleSeeder → Creates roles
2. UserSeeder → Creates admin (needs roles)
3. CategorySeeder → Creates categories
4. DemoDataSeeder (optional) → Creates full demo data

### 7. Planning Documents (`planning/`)

#### 01_requirements.md
- Full SRS (Software Requirements Specification)
- Functional & non-functional requirements
- Technology stack
- Deployment requirements

#### 02_database_schema.dbml
- Complete database schema in DBML format
- All tables, columns, relationships
- Constraints and indexes
- Can be visualized at dbdiagram.io

#### 03_class_diagram.mmd
- Mermaid class diagram
- All models with methods
- Relationships
- Can be visualized with Mermaid Live Editor

#### 04_indexeddb_schema.md
- Schema for offline database (future feature)
- PWA implementation reference

## 🔄 Data Flow

### Stock Deduction Flow
```
1. User fills SimulateOrder form
   ↓
2. SimulateOrder calls InventoryService.decreaseStockForOrder()
   ↓
3. Service validates stock availability
   ↓
4. Service queries batches ordered by FEFO/FIFO
   ↓
5. Service deducts from batches in order
   ↓
6. Service commits transaction
   ↓
7. Service returns success result
   ↓
8. SimulateOrder shows notification
```

### FIFO/FEFO Logic
```sql
-- Batches are ordered by:
ORDER BY 
    expiry_date ASC,     -- FEFO: First Expired First Out
    received_at ASC      -- FIFO: First In First Out

-- Deduction loop:
WHILE remaining_quantity > 0:
    - Take quantity from first batch
    - If batch depleted, move to next batch
    - Update batch quantity
    - Record change
```

## 🎨 UI Navigation

### Admin Panel Menu Structure
```
📦 Inventory
├── Categories
├── Ingredients
│   └── [Edit] → Tab: Batch Stok
├── Menu
│   └── [Edit] → Tab: Resep (Bahan)
├── Pencatatan Waste
└── ✨ Simulasi Pesanan

👤 Admin
└── Users
```

## 🔐 Security Layers

1. **Authentication**: Filament auth
2. **Password Hashing**: Laravel's Hash facade
3. **SQL Injection**: Eloquent ORM (parameterized queries)
4. **CSRF Protection**: Laravel middleware
5. **XSS Protection**: Blade template escaping
6. **Soft Deletes**: Data recovery capability

## 📊 Database Indexes

Auto-indexed by Laravel:
- All `id` columns (primary key)
- All foreign key columns
- All `unique` columns

## 🚀 Performance Considerations

1. **Eager Loading**: Use `with()` to prevent N+1 queries
2. **Query Optimization**: Filament tables use pagination
3. **Caching**: Not implemented (future enhancement)
4. **Database Indexes**: Automatic on foreign keys

## 🧪 Testing Hooks

### Tinker Commands
```php
// Test getTotalStock()
Ingredient::find(1)->getTotalStock()

// Test calculateCost()
Menu::find(1)->calculateCost()

// Test InventoryService
app(InventoryService::class)->decreaseStockForOrder([
    ['menu_id' => 1, 'quantity' => 5]
])
```

### Database Queries
```sql
-- Check stock per batch
SELECT * FROM ingredient_batches WHERE ingredient_id = 1;

-- Check recipe
SELECT * FROM menu_ingredients WHERE menu_id = 1;
```

## 📝 Code Style

- **PSR-12**: PHP coding standard
- **Laravel**: Follow Laravel conventions
- **Filament**: Follow Filament best practices
- **Comments**: PHPDoc for all public methods

## 🔮 Extension Points

Future features can extend:

1. **Models**: Add new relationships
2. **Services**: Add new business logic methods
3. **Resources**: Add new Filament resources
4. **Migrations**: Add new tables
5. **Seeders**: Add new demo data

## 📚 External References

- Laravel Docs: https://laravel.com/docs
- Filament Docs: https://filamentphp.com/docs
- PostgreSQL Docs: https://www.postgresql.org/docs/
- DBML Spec: https://dbml.dbdiagram.io/docs/

---

**Last Updated**: October 29, 2025  
**Version**: 1.0.0
