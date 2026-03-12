# W9 Cafe POS — Sistem Point of Sale

Sistem POS berbasis web PWA untuk W9 Cafe STIE Totalwin Semarang.  
**Fase aktif:** Modul Transaksi (Kasir + Pelanggan)

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | Laravel 11, PHP 8.2+ |
| Database | PostgreSQL 16 |
| Frontend | React 18 + Inertia.js v2 |
| CSS | Bootstrap 5 |
| Build | Vite |
| Auth | Laravel Sanctum (session-based) |
| Payment | Midtrans Snap (QRIS, E-Wallet, Transfer) |
| State | Zustand (cart) + IndexedDB (offline) |

---

## Cara Install

```bash
# 1. Clone & install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# Isi DB_* dan MIDTRANS_* di .env

# 3. Database
php artisan migrate:fresh --seed

# 4. Jalankan
npm run build          # production
# atau
npm run dev &          # development (hot reload)
php artisan serve
```

---

## Akun Default (setelah seeding)

| Role | Email | Password |
|---|---|---|
| Kasir | `kasir@w9cafe.com` | `password` |
| Admin | `admin@w9cafe.com` | `password` |
| Customer | `budi@student.com` | `password` |

> Login pelanggan menggunakan **Nama Lengkap** sebagai username dan **NIM** sebagai password.  
> NIM Budi: `21120122140001`

---

## Variabel Environment Penting

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_cafe
DB_USERNAME=postgres
DB_PASSWORD=

MIDTRANS_SERVER_KEY=SB-Mid-server-...
MIDTRANS_CLIENT_KEY=SB-Mid-client-...
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/snap.js
```

---

## Halaman Kasir (Desktop)

| Route | Halaman |
|---|---|
| `/login` | Login kasir |
| `/cashier/dashboard` | Dashboard + stat harian |
| `/cashier/pesanan-baru` | POS interface (grid menu + keranjang) |
| `/cashier/pesanan-aktif` | Kanban pesanan aktif |
| `/cashier/riwayat` | Riwayat transaksi |
| `/cashier/order/{id}` | Detail pesanan |
| `/cashier/verifikasi` | Verifikasi akun mahasiswa |
| `/cashier/profil` | Profil kasir |

## Halaman Pelanggan (Mobile PWA)

| Route | Halaman |
|---|---|
| `/customer/login` | Login mahasiswa |
| `/customer/menu` | Menu + kategori chips |
| `/customer/cart` | Keranjang belanja |
| `/customer/riwayat` | Riwayat pesanan |
| `/customer/order/{id}/status` | Status pembayaran |

---

## Struktur Tim

- **Ivan** — Fullstack Transaction (modul ini)
- **Nio** — Fullstack Inventory (fase berikutnya)
- **Ruben** — Data Mining & FastAPI (fase terakhir)
