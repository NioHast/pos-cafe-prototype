# Testing Guide: Admin Panel Features (Tanpa Frontend)

## 📖 Overview

Panduan ini untuk **testing fitur admin panel** yang berhubungan dengan kasir dan pelanggan, **tanpa menunggu frontend Vue.js selesai**.

Tim lain mengerjakan:
- ✅ Frontend Kasir (Vue.js PWA)
- ✅ Frontend Pelanggan (Vue.js SPA)
- ✅ Python Analytics Service

Kamu fokus testing di:
- ✅ Admin Panel (Filament)
- ✅ API Endpoints (Laravel)
- ✅ Database Logic (Models & Migrations)

---

## 🛠️ Tools untuk Testing

### Option 1: **Thunder Client** (Recommended - Built-in VS Code)
1. Install extension: [Thunder Client](https://marketplace.visualstudio.com/items?itemName=rangav.vscode-thunder-client)
2. Buka Thunder Client tab di VS Code
3. Import collection dari file `thunder-client-collection.json`

### Option 2: **Postman**
1. Download: https://www.postman.com/downloads/
2. Import collection dari file `postman-collection.json`

### Option 3: **Insomnia**
1. Download: https://insomnia.rest/download
2. Import collection dari file `insomnia-collection.json`

---

## 🚀 Setup Laravel Sanctum (untuk API Authentication)

### Step 1: Install Sanctum (jika belum)

```bash
php artisan install:api
```

### Step 2: Update User Model

File `app/Models/User.php` harus punya trait `HasApiTokens`:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    // ...
}
```

### Step 3: Run Migration

```bash
php artisan migrate
```

---

## 📋 Test Scenarios

### Scenario 1: **Testing Student Registration**

**Goal:** Test fitur register mahasiswa tanpa frontend pelanggan

#### Via Thunder Client/Postman:

**Request:**
```
POST http://localhost/api/auth/register-student
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john.doe@student.ac.id",
  "password": "password123",
  "nim": "2024001",
  "faculty": "Engineering",
  "major": "Computer Science",
  "enrollment_year": 2024
}
```

**Expected Response:**
```json
{
  "message": "Student account created. Waiting for admin verification.",
  "user": {
    "id": 5,
    "name": "John Doe",
    "email": "john.doe@student.ac.id",
    "role_id": 3,
    "created_at": "2026-01-19T12:00:00.000000Z"
  }
}
```

#### Test di Admin Panel:

1. Login ke `/admin`
2. Buka **Users** resource
3. Cari user baru: "John Doe"
4. Verify: role = student ✅
5. Edit user kalau perlu

---

### Scenario 2: **Testing Kasir Create Order**

**Goal:** Test order creation tanpa frontend kasir

#### Step 1: Get Kasir Token

**Request:**
```
POST http://localhost/api/auth/login
Content-Type: application/json

{
  "email": "kasir@cafe.com",
  "password": "password"
}
```

**Response:**
```json
{
  "token": "1|abcd1234...",
  "user": {
    "id": 2,
    "name": "Kasir 1",
    "email": "kasir@cafe.com",
    "role": {
      "name": "cashier"
    }
  }
}
```

**Save token untuk request selanjutnya!**

#### Step 2: Get Menu List

**Request:**
```
GET http://localhost/api/menu
```

**Response:**
```json
[
  {
    "id": 1,
    "name": "Espresso",
    "regular_price": 15000,
    "student_price": 12000,
    "category": {
      "name": "Coffee"
    }
  },
  ...
]
```

#### Step 3: Create Order

**Request:**
```
POST http://localhost/api/orders
Content-Type: application/json

{
  "cashier_id": 2,
  "customer_name": "Walk-in Customer",
  "table_number": null,
  "payment_method": "cash",
  "items": [
    {
      "menu_id": 1,
      "quantity": 2,
      "handled_by": 2
    },
    {
      "menu_id": 3,
      "quantity": 1,
      "handled_by": 2
    }
  ]
}
```

**Expected Response:**
```json
{
  "message": "Order created successfully",
  "order": {
    "id": 10,
    "order_number": "ORD-20260119120530",
    "cashier_id": 2,
    "customer_name": "Walk-in Customer",
    "total_amount": 45000,
    "payment_status": "paid",
    "status": "completed",
    "items": [
      {
        "menu_id": 1,
        "quantity": 2,
        "price": 15000,
        "subtotal": 30000,
        "menu": {
          "name": "Espresso"
        }
      },
      {
        "menu_id": 3,
        "quantity": 1,
        "price": 15000,
        "subtotal": 15000,
        "menu": {
          "name": "Cappuccino"
        }
      }
    ]
  }
}
```

#### Step 4: Verify di Admin Panel

1. Login ke `/admin`
2. Buka **Orders** resource
3. Lihat order baru: "ORD-20260119120530"
4. Check:
   - Cashier = Kasir 1 ✅
   - Customer Name = "Walk-in Customer" ✅
   - Total = Rp 45,000 ✅
   - Payment Status = "Paid" ✅
   - Order Items = 2 items ✅

---

### Scenario 3: **Testing Customer Self-Order**

**Goal:** Test self-order pelanggan tanpa frontend pelanggan

#### Request:

```
POST http://localhost/api/customer/orders
Content-Type: application/json

{
  "customer_name": "Jane Smith",
  "customer_phone": "081234567890",
  "table_number": "A5",
  "payment_method": "qris",
  "items": [
    {
      "menu_id": 1,
      "quantity": 1
    },
    {
      "menu_id": 2,
      "quantity": 2
    }
  ]
}
```

**Expected Response:**
```json
{
  "message": "Order created. Please complete payment.",
  "order": {
    "id": 11,
    "order_number": "SELF-20260119121500",
    "cashier_id": null,
    "customer_name": "Jane Smith",
    "customer_phone": "081234567890",
    "table_number": "A5",
    "total_amount": 50000,
    "payment_status": "pending",
    "status": "pending"
  },
  "payment_url": "https://midtrans.com/snap/..."
}
```

#### Verify di Admin Panel:

1. Buka **Orders** resource
2. Lihat order: "SELF-20260119121500"
3. Check:
   - Cashier = NULL (self-order) ✅
   - Customer Phone = "081234567890" ✅
   - Table Number = "A5" ✅
   - Payment Status = "Pending" ✅
4. Manual action: Update payment_status ke "paid" (simulate payment success)

---

### Scenario 4: **Testing Student Price Logic**

**Goal:** Test harga mahasiswa vs harga normal

#### Step 1: Create Student User via API

```
POST http://localhost/api/auth/register-student
Content-Type: application/json

{
  "name": "Alice Student",
  "email": "alice@student.ac.id",
  "password": "password",
  "nim": "2024002",
  "faculty": "Science",
  "major": "Mathematics",
  "enrollment_year": 2024
}
```

#### Step 2: Verify di Admin Panel

1. Buka **Users** resource
2. Cari "Alice Student"
3. Check role = student ✅

#### Step 3: Test Order dengan Student Price (Manual di Admin)

1. Buka **Orders** resource → Create
2. Pilih customer = "Alice Student"
3. Add menu item: "Espresso"
4. **TODO:** Price harus auto-switch ke student_price (Rp 12,000) instead of regular (Rp 15,000)
5. **Fitur ini perlu enhancement di OrderResource!**

---

### Scenario 5: **Testing Order Detail**

**Goal:** Lihat detail order via API

#### Request:

```
GET http://localhost/api/orders/10
```

**Response:**
```json
{
  "id": 10,
  "order_number": "ORD-20260119120530",
  "cashier": {
    "name": "Kasir 1"
  },
  "customer_name": "Walk-in Customer",
  "items": [
    {
      "menu": {
        "name": "Espresso"
      },
      "quantity": 2,
      "price": 15000,
      "handler": {
        "name": "Kasir 1"
      }
    }
  ],
  "total_amount": 45000,
  "payment_method": "cash",
  "payment_status": "paid"
}
```

---

## 🔧 Testing Utilities

### Get All Users (untuk referensi ID)

```
GET http://localhost/api/test/users
```

Returns semua users dengan role untuk testing.

### Get Recent Orders

```
GET http://localhost/api/test/orders
```

Returns 10 order terbaru untuk quick check.

---

## 📊 Test Checklist

### ✅ Phase 0: Basic Setup
- [ ] Laravel Sanctum installed (`php artisan install:api`)
- [ ] API routes working (`routes/api.php` loaded)
- [ ] Thunder Client/Postman installed
- [ ] Seeder run untuk sample data users & menu

### ✅ Phase 1: Student Registration
- [ ] POST `/api/auth/register-student` → Success
- [ ] User muncul di Admin Panel → Users
- [ ] Role = student ✅
- [ ] Email unique validation works

### ✅ Phase 2: Kasir Create Order
- [ ] POST `/api/auth/login` → Get token
- [ ] GET `/api/menu` → Get menu list
- [ ] POST `/api/orders` → Order created
- [ ] Order muncul di Admin Panel → Orders
- [ ] Order items accurate
- [ ] Total calculation correct

### ✅ Phase 3: Customer Self-Order
- [ ] POST `/api/customer/orders` → Order created
- [ ] Order muncul di Admin Panel
- [ ] Cashier_id = NULL ✅
- [ ] Payment status = pending ✅
- [ ] Table number saved ✅

### ✅ Phase 4: Admin Panel Verification
- [ ] Bisa edit order di OrderResource
- [ ] Bisa assign staf ke order items
- [ ] Bisa update payment status
- [ ] Bisa soft delete order

---

## 🐛 Common Issues & Fixes

### Issue 1: "Route not found"

**Problem:** API routes tidak ke-load

**Fix:**
```bash
php artisan route:clear
php artisan route:cache
php artisan optimize:clear
```

### Issue 2: "Unauthenticated" di protected routes

**Problem:** Token tidak ter-pass

**Fix di Thunder Client:**
- Tab "Auth" → Type: Bearer Token
- Paste token dari login response

**Fix di Postman:**
- Tab "Authorization" → Type: Bearer Token
- Paste token

### Issue 3: "CORS Error" di browser

**Problem:** CORS not configured

**Fix:**
```bash
php artisan install:api
```

Edit `config/cors.php`:
```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_origins' => ['http://localhost:5173', 'http://localhost:5174'],
```

### Issue 4: "Undefined type 'Hash'"

**Problem:** Hash facade tidak di-import

**Fix:** Sudah fixed di `routes/api.php` line 4:
```php
use Illuminate\Support\Facades\Hash;
```

---

## 🎯 Next Steps

### Setelah Frontend Team Selesai:

1. **Replace API Test Routes dengan Real API Controllers:**
   - Move logic dari `routes/api.php` ke `app/Http/Controllers/Api/`
   - Add proper validation dengan FormRequest
   - Add API Resources untuk consistent JSON response

2. **Add API Documentation:**
   - Install Scribe: `composer require knuckleswtf/scribe`
   - Generate docs: `php artisan scribe:generate`

3. **Add API Tests:**
   - Create Feature tests di `tests/Feature/Api/`
   - Test semua endpoints dengan PHPUnit

---

## 📝 Notes

- **Sementara frontend belum selesai**, gunakan API routes ini untuk testing
- **Data yang dibuat via API** akan terlihat di Admin Panel (same database)
- **Admin Panel updates** akan terlihat via API response (real-time sync)
- **Student profile feature** (Sprint 1.2) belum ada table-nya, commented out di code

---

**Happy Testing!** 🚀

Kalau ada issue, check console/logs:
```bash
tail -f storage/logs/laravel.log
```
