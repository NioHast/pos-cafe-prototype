# Spesifikasi Kebutuhan Perangkat Lunak (SRS)

**Proyek:** Sistem POS Cerdas untuk Kafe  
**Versi Dokumen:** 1.3  
**Tanggal:** 1 Maret 2026  
**Perubahan v1.3:**
- Keputusan arsitektural major: Soft delete strategy, denormalisasi snapshot, optional recipe input
- Larangan retroactive stock adjustment untuk menjaga integritas data
- 100% Filament native untuk MVP, no custom Livewire components
- Rencana implementasi Order Management (7 langkah sistematis)
- Small but critical UX features (low stock badge, void reason, global search)
- Smart Reconciliation deferred ke Phase 2 (post-MVP)

**Perubahan v1.2:**
- UUID optimization: Hanya 3 tabel yang menggunakan UUID untuk offline sync

**Perubahan v1.1:**
- Update teknologi: PHP 8.4, Laravel 12, Filament 4.x, Vue.js 3
- **100% Code Reuse:** UI Kasir dan Pelanggan menggunakan source code Vue yang IDENTIK
- Semua interface menggunakan Bootstrap 5 (Tabler.io) untuk konsistensi UI/UX
- UI Kasir & Pelanggan menggunakan Vue.js 3 dengan build flag berbeda (offline mode on/off)  

---

## 1. Pendahuluan

### 1.1 Tujuan

Dokumen ini bertujuan untuk mendefinisikan secara rinci semua kebutuhan fungsional, non-fungsional, arsitektural, dan deployment untuk *Sistem POS Cerdas*.  
Sistem ini merupakan aplikasi Point of Sale (POS) berbasis web yang dirancang khusus untuk kafe, dengan arsitektur *hybrid* (offline-first dan cloud).  
Sistem mendukung manajemen inventori berbasis resep dan integrasi analitik data mining (Machine Learning) untuk membantu pengambilan keputusan bisnis.

### 1.2 Ruang Lingkup Proyek

Sistem mencakup tiga antarmuka pengguna utama:

1. **Panel Admin**  
   Antarmuka manajemen berbasis Filament untuk mengelola inventori, menu, pengguna, serta menampilkan hasil analitik.

2. **Antarmuka Kasir**  
   Aplikasi PWA (Progressive Web App) berbasis Vue.js 3 + Bootstrap 5 (Tabler.io) untuk tablet/PC yang dapat beroperasi penuh secara offline dengan IndexedDB dan melakukan sinkronisasi otomatis saat online. **Menggunakan source code Vue yang 100% IDENTIK dengan UI Pelanggan**, dengan fitur offline diaktifkan via environment variable (`VITE_OFFLINE_MODE=true`).

3. **Portal Pelanggan**  
   Antarmuka web SPA berbasis Vue.js 3 + Bootstrap 5 (Tabler.io) yang memungkinkan pelanggan melakukan *self-order* dan pembayaran online dengan QR code. **Menggunakan SEMUA komponen Vue yang SAMA PERSIS dengan UI Kasir** - tidak ada file terpisah, hanya build dengan flag `VITE_OFFLINE_MODE=false` untuk menonaktifkan fitur offline.

### 1.3 Target Pengguna (Aktor)

- **Admin:** Pemilik atau manajer kafe. Mengelola sistem, stok, dan laporan analitik.  
- **Kasir/Staf:** Karyawan yang mengoperasikan POS, termasuk pemrosesan pesanan dan transaksi.  
- **Pelanggan:** Pengunjung umum kafe yang melakukan pemesanan mandiri.  
- **Pelanggan (Mahasiswa):** Pelanggan dengan akun khusus mahasiswa yang mendapat harga diskon.

---

## 2. Deskripsi Umum dan Arsitektur

### 2.1 Arsitektur Sistem

Sistem menggunakan arsitektur *decoupled* dengan beberapa komponen utama:

- **Backend API Utama (Laravel):** Mengatur logika bisnis, autentikasi (Sanctum), dan komunikasi antar layanan.  
- **Layanan Analitik (Python/FastAPI):** Menjalankan pemrosesan data mining dan machine learning secara terpisah.  
- **Database Pusat (PostgreSQL):** Menyimpan semua data utama sebagai *single source of truth*.  
- **Frontend:** Terdiri dari dua interface — Filament (admin) dan Vue.js SPA (kasir + pelanggan dengan source code 100% identik).

### 2.2 Tumpukan Teknologi

- **Backend API:** PHP 8.4, Laravel 12  
- **Panel Admin:** Laravel Filament 4.x + Bootstrap 5 (Tabler.io)
- **UI Kasir (PWA):** Vue.js 3 + Bootstrap 5 (Tabler.io) + Pinia + Vue Router + Axios + Vue I18n + IndexedDB (Dexie.js) + Service Workers (Workbox)
- **UI Pelanggan:** Vue.js 3 + Bootstrap 5 (Tabler.io) + Pinia + Vue Router + Axios + Vue I18n - **Source code SAMA PERSIS dengan Kasir, tanpa IndexedDB & Service Workers**
- **Layanan Analitik:** Python, FastAPI  
- **Database:** PostgreSQL 15+ (pusat) dan IndexedDB (offline untuk kasir saja)  
- **Server:** Nginx (reverse proxy)  
- **Lingkungan:** Docker (development & deployment)  
- **Deployment Target:** VPS Linux

#### 2.2.1 Penyederhanaan UI Framework & 100% Code Reuse

**Keputusan Arsitektur:**
- **UI Kasir dan Pelanggan menggunakan SOURCE CODE yang IDENTIK 100%:** Vue.js 3 + Bootstrap 5 (Tabler.io).
- **Satu-satunya perbedaan:** Environment variable untuk mengaktifkan/menonaktifkan fitur offline.
  - Kasir: `VITE_OFFLINE_MODE=true` → Enable IndexedDB + Service Workers
  - Pelanggan: `VITE_OFFLINE_MODE=false` → Disable offline features
- **UI Admin** menggunakan Filament dengan Tabler.io theme untuk konsistensi visual.

**Tujuan:**
- **100% Code Reuse:** Tidak ada duplikasi code sama sekali antara Kasir dan Pelanggan.
- **Single Codebase:** Satu set komponen Vue untuk dua interface.
- **Conditional Features:** Fitur offline hanya aktif jika environment variable enabled.
- **Identical UI/UX:** Tampilan dan behavior kasir & pelanggan benar-benar sama.

**Implementasi:**
- **UI Admin (Filament):** 
  - Dikustomisasi dengan Tabler.io theme untuk konsistensi visual.
  - Halaman terpisah di `/admin`.
  
- **UI Kasir (Vue.js PWA):** 
  - Single Page Application (SPA) dengan Vue Router.
  - Komponen Vue reusable dengan Tabler.io Bootstrap styling.
  - State management dengan Pinia stores.
  - API communication via Axios dengan Laravel Sanctum authentication.
  - **Offline capability:**
    - IndexedDB (Dexie.js) untuk local storage.
    - Service Workers (Workbox) untuk caching dan background sync.
    - Offline transaction queue dengan auto-sync saat online.
  - Build command: `npm run build:cashier` → `VITE_OFFLINE_MODE=true`
  - Deploy ke `/pos` route.
  
- **UI Pelanggan (Vue.js SPA):**
  - **Menggunakan SEMUA komponen Vue yang SAMA dengan Kasir.**
  - **Tidak ada file Vue terpisah** - reuse 100% dari folder kasir.
  - Conditional rendering: `v-if="isOfflineEnabled"` untuk fitur khusus kasir.
  - API communication sama via Axios + Sanctum.
  - **Tidak ada offline features:**
    - Tidak load Dexie.js
    - Tidak register Service Workers
    - Langsung fail jika koneksi terputus (graceful error message)
  - Build command: `npm run build:customer` → `VITE_OFFLINE_MODE=false`
  - Deploy ke `/customer` atau `/order` route.

**Struktur Folder:**
```
resources/js/pos/
├── main.js                    # Entry point dengan conditional offline setup & i18n
├── App.vue                    # Root component
├── router/
│   └── index.js              # Vue Router - shared routes
├── stores/                    # Pinia stores - shared state
│   ├── cart.js
│   ├── menu.js
│   ├── auth.js
│   └── offline.js            # Only loaded if VITE_OFFLINE_MODE=true
├── locales/                   # i18n untuk perbedaan teks UI
│   ├── cashier.json          # Teks spesifik untuk kasir
│   └── customer.json         # Teks spesifik untuk pelanggan
├── components/               # 100% reusable components
│   ├── MenuGrid.vue
│   ├── CartSidebar.vue
│   ├── CheckoutForm.vue
│   ├── TransactionList.vue   # Reusable dengan $t() untuk dynamic text
│   └── ...
├── composables/              # Shared logic
│   ├── useApi.js
│   ├── useAppMode.js         # Detect cashier vs customer mode
│   ├── useOffline.js         # Conditional composable
│   └── ...
├── services/
│   ├── api.js               # Axios instance
│   ├── db.js                # Dexie.js (conditional import)
│   └── sync.js              # Background sync (conditional)
└── utils/
    └── config.js            # import.meta.env.VITE_APP_MODE & VITE_OFFLINE_MODE
```

**Build Process:**
```json
// package.json
{
  "scripts": {
    "dev:cashier": "VITE_APP_MODE=cashier VITE_OFFLINE_MODE=true vite --port 5173",
    "dev:customer": "VITE_APP_MODE=customer VITE_OFFLINE_MODE=false vite --port 5174",
    "build:cashier": "VITE_APP_MODE=cashier VITE_OFFLINE_MODE=true vite build --outDir public/build/cashier",
    "build:customer": "VITE_APP_MODE=customer VITE_OFFLINE_MODE=false vite build --outDir public/build/customer"
  }
}
```

**Dynamic Text/Content Strategy (i18n untuk perbedaan UI):**

Untuk mengubah teks atau konten tertentu antara kasir dan pelanggan tanpa duplikasi code, gunakan **Vue I18n** dengan locale files berbeda:

```
resources/js/pos/locales/
├── cashier.json          # Teks untuk UI Kasir
└── customer.json         # Teks untuk UI Pelanggan
```

**Contoh Locale Files:**
```json
// locales/cashier.json
{
  "transactions": {
    "title": "Daftar Transaksi Shift Anda",
    "subtitle": "Transaksi yang Anda proses hari ini",
    "empty": "Belum ada transaksi di shift ini"
  },
  "dashboard": {
    "welcome": "Selamat datang, {name}!",
    "shift_info": "Shift Anda dimulai pada {time}"
  }
}

// locales/customer.json
{
  "transactions": {
    "title": "Riwayat Pesanan Anda",
    "subtitle": "Pesanan yang sudah Anda buat",
    "empty": "Belum ada pesanan"
  },
  "dashboard": {
    "welcome": "Halo, {name}!",
    "shift_info": ""  // Kosong karena tidak relevan untuk customer
  }
}
```

**Setup di main.js:**
```javascript
import { createI18n } from 'vue-i18n'
import cashierMessages from './locales/cashier.json'
import customerMessages from './locales/customer.json'

const appMode = import.meta.env.VITE_APP_MODE // 'cashier' atau 'customer'

const i18n = createI18n({
  locale: appMode, // 'cashier' atau 'customer'
  fallbackLocale: 'cashier',
  messages: {
    cashier: cashierMessages,
    customer: customerMessages
  }
})

app.use(i18n)
```

**Usage di Vue Component:**
```vue
<template>
  <div>
    <h1>{{ $t('transactions.title') }}</h1>
    <p>{{ $t('transactions.subtitle') }}</p>
    
    <!-- Conditional content jika perlu -->
    <div v-if="appMode === 'cashier'">
      <button>Print Receipt</button>
      <button>Refund</button>
    </div>
    
    <div v-else>
      <button>Track Order</button>
      <button>Rate Service</button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const { t, locale } = useI18n()
const appMode = computed(() => locale.value) // 'cashier' atau 'customer'
</script>
```

**Untuk Perbedaan Layout/Component:**
Gunakan composable untuk reusable logic:

```javascript
// composables/useAppMode.js
import { computed } from 'vue'

export function useAppMode() {
  const mode = import.meta.env.VITE_APP_MODE
  
  return {
    isCashier: computed(() => mode === 'cashier'),
    isCustomer: computed(() => mode === 'customer'),
    mode
  }
}
```

**Usage:**
```vue
<script setup>
import { useAppMode } from '@/composables/useAppMode'

const { isCashier, isCustomer } = useAppMode()
</script>

<template>
  <div>
    <!-- Kasir: Tampilkan tombol print -->
    <button v-if="isCashier" @click="printReceipt">
      {{ $t('actions.print') }}
    </button>
    
    <!-- Customer: Tampilkan tombol share -->
    <button v-if="isCustomer" @click="shareOrder">
      {{ $t('actions.share') }}
    </button>
  </div>
</template>
```

**Keuntungan Strategi Ini:**
- ✅ **Single Source of Code:** Komponen Vue tetap satu file
- ✅ **Easy Text Management:** Semua teks di satu tempat (locale files)
- ✅ **Type-Safe:** Bisa gunakan TypeScript untuk locale keys
- ✅ **Easy Translation:** Tinggal ubah JSON, tidak perlu touch Vue files
- ✅ **Conditional Rendering:** `v-if="isCashier"` untuk fitur khusus
- ✅ **No Code Duplication:** Tidak ada file Vue terpisah
- ✅ **Build Time Optimization:** Unused locale di-tree-shake otomatis

**Keuntungan:**
- **100% Code Reuse:** Tidak ada duplikasi file Vue sama sekali.
- **Identik UI/UX:** Kasir dan pelanggan dijamin tampil sama persis dengan layout dan flow yang konsisten.
- **Single Source of Truth:** Bug fix atau feature baru otomatis berlaku di kedua interface.
- **Flexible Text Content:** Vue I18n untuk perbedaan teks tanpa duplikasi component.
- **Easy Customization:** Ubah teks UI cukup edit JSON locale files, tidak perlu touch Vue files.
- **Conditional Features:** `v-if="isCashier"` untuk fitur khusus kasir (print, refund, stock management).
- **Faster Development:** Develop sekali, deploy dua kali dengan build flag berbeda.
- **Easier Maintenance:** Hanya satu codebase untuk di-maintain.
- **Easier Testing:** Test suite sama untuk kasir dan pelanggan.
- **Smaller Bundle Size:** Tree-shaking otomatis remove offline code di build customer.
- **Offline-First for Cashier:** Vue.js mendukung PWA dengan service workers untuk offline capability.
- **Better Performance:** SPA dengan client-side routing lebih responsif dari Livewire.
- **Type-Safe i18n:** Bisa gunakan TypeScript untuk autocomplete locale keys.

---

## 3. Kebutuhan Fungsional (Functional Requirements)

### 3.1 Manajemen Pengguna & Autentikasi

- Sistem harus menyediakan tiga peran pengguna: admin, cashier, dan student.  
- Manajemen peran dinormalisasi dengan tabel `roles` dan `users` berelasi melalui `role_id`.  
- Admin dapat login dengan email dan password, melakukan reset password kasir, dan reset via email.  
- Kasir login menggunakan email dan password. Reset hanya dapat dilakukan oleh admin.  
- Mahasiswa login dengan email dan password serta data tambahan (NIM, Fakultas) di `student_profiles`.  
- Sistem mencatat sesi kerja kasir (login/logout) di `cashier_sessions`.

### 3.2 Manajemen Inventori & Stok (Admin)

- Admin dapat melakukan CRUD pada bahan baku (`ingredients`).  
- Admin mencatat stok masuk di `ingredient_batches` termasuk `quantity`, `cost_per_unit`, dan `expiry_date`.  
- Admin dapat CRUD menu, termasuk harga normal dan harga mahasiswa.  
- Resep didefinisikan melalui tabel `menu_ingredients`.  
- Pencatatan bahan terbuang dilakukan di `waste_records`.  
- Penghapusan data master menggunakan *soft delete*.  
- Tersedia halaman simulasi pesanan untuk uji pengurangan stok.

### 3.2.1 Standard Operating Procedure (SOP) Pembuatan Menu

- Admin dapat membuat SOP step-by-step untuk setiap menu di tabel `menu_procedures`.
- SOP mencakup urutan langkah pembuatan (step order), deskripsi langkah, durasi estimasi, dan gambar ilustrasi (opsional).
- Setiap langkah dapat ditandai sebagai "critical step" (langkah penting yang tidak boleh dilewati).
- Kasir/staf dapat melihat SOP saat memproses pesanan untuk memastikan konsistensi kualitas.
- SOP dapat mencakup informasi:
  - **Step Number:** Urutan langkah (1, 2, 3, ...)
  - **Title:** Judul singkat langkah (e.g., "Grind Coffee Beans")
  - **Description:** Deskripsi detail langkah (e.g., "Giling 15g biji kopi dengan grinder, setting medium-fine")
  - **Duration:** Estimasi waktu (e.g., "30 detik")
  - **Is Critical:** Apakah langkah ini wajib/critical (e.g., "Suhu air harus 92-96°C")
  - **Image URL:** Link gambar ilustrasi (opsional)
  - **Notes:** Catatan tambahan (e.g., "Pastikan grinder bersih sebelum digunakan")
- Admin dapat mengaktifkan/menonaktifkan tampilan SOP per menu.
- Staf dapat memberikan feedback atau melaporkan masalah pada SOP tertentu.

### 3.3 Fungsionalitas Kasir (Offline-First PWA)

**Teknologi:** Vue.js 3 + Pinia + Vue Router + Axios + Bootstrap 5 (Tabler.io) + Dexie.js + Service Workers

- Antarmuka kasir berupa Single Page Application (SPA) Vue.js yang dapat diinstal sebagai PWA dan berjalan offline.
- **Menggunakan template Bootstrap Tabler.io yang sama dengan Admin dan Pelanggan** untuk konsistensi UI.
- **Menggunakan source code Vue yang 100% identik dengan UI Pelanggan** - build dengan `VITE_OFFLINE_MODE=true`.
- Autentikasi menggunakan Laravel Sanctum dengan bearer token.
- State management dengan Pinia stores (cart, menu, auth, offline).
- Client-side routing dengan Vue Router untuk navigasi tanpa reload.
- API communication via Axios untuk fetch data dari Laravel backend.

**Fitur Offline (hanya aktif di build Kasir):**
- Saat online, aplikasi melakukan *sync down* data referensi ke IndexedDB via Dexie.js:
  - Menu items dengan harga, gambar, kategori
  - Kategori menu
  - Promosi aktif
  - Stok ingredients current
  - User data kasir
  - **SOP pembuatan menu (step-by-step procedures)**
- Service Workers (Workbox) cache static assets dan API responses.
- Saat offline, kasir dapat:
  - Melihat menu dan kategori dari IndexedDB cache.
  - Menerapkan promosi dari cache.
  - Mengecek stok lokal (last synced).
  - Melakukan transaksi tunai penuh.
  - **Membuka dan mengikuti SOP pembuatan menu dari cache.**
  - Menambah items ke cart dan process checkout.
- Setiap transaksi offline disimpan ke IndexedDB di tabel `transaction_queue`.
- Transaksi offline mengurangi stok lokal (optimistic update).
- Saat koneksi kembali, background sync otomatis:
  - POST transactions dari queue ke server.
  - Sync ulang stok terbaru.
  - Update status transaksi.
  - Clear queue setelah berhasil.
- Kasir dapat menampilkan SOP step-by-step untuk menu yang sedang dibuat:
  - Vue component `<MenuProcedure>` dengan step list.
  - Checklist (checkbox) untuk menandai langkah selesai.
  - Timer countdown untuk langkah dengan durasi.
  - Modal zoom untuk gambar ilustrasi.
  - Badge highlight untuk critical steps.
  - Progress bar untuk tracking completion.

**Fitur Khusus Kasir (conditional rendering dengan `v-if`):**
- Dashboard shift kasir (jam masuk/keluar).
- Laporan transaksi harian.
- Manajemen stok ingredients (update quantity, waste records).
- Multiple payment methods (cash, card, e-wallet).
- Print receipt (thermal printer integration).

### 3.4 Fungsionalitas Pelanggan (Self-Order Online)

**Teknologi:** Vue.js 3 + Pinia + Vue Router + Axios + Bootstrap 5 (Tabler.io) - **SOURCE CODE IDENTIK dengan Kasir**

- Pelanggan mengakses antarmuka melalui QR code di meja (URL: `/customer` atau `/order?table=X`).
- **Menggunakan SEMUA komponen Vue yang SAMA dengan Kasir** - build dengan `VITE_OFFLINE_MODE=false`.
- UI SPA Vue.js dengan Tabler.io Bootstrap styling yang identik dengan kasir.
- Autentikasi menggunakan Laravel Sanctum (optional login untuk mahasiswa, guest untuk umum).
- State management dengan Pinia stores yang sama (cart, menu, auth).
- Client-side routing dengan Vue Router yang sama.
- API communication via Axios yang sama untuk fetch menu dan create order.

**Perbedaan dengan Kasir:**
- **Tidak ada fitur offline:** Dexie.js dan Service Workers tidak di-load (tree-shaking).
- **Tidak ada fitur khusus kasir:** Conditional `v-if="user.role === 'cashier'"` hide fitur manajemen.
- Harga otomatis disesuaikan untuk mahasiswa (`student_price` vs `regular_price`).
- Pembayaran online dilakukan melalui Midtrans API.
- Setelah order, redirect ke halaman tracking order atau struk digital.

**Fitur yang SAMA dengan Kasir (shared components):**
- `<MenuGrid>` - display menu items dengan card layout Tabler.io
- `<CartSidebar>` - shopping cart dengan add/remove/update quantity
- `<CheckoutForm>` - form data customer dan pilih payment method
- `<CategoryFilter>` - filter menu by category
- `<SearchBar>` - search menu items
- Navigation dengan Vue Router tanpa page reload

### 3.5 Sistem Transaksi & Pembayaran

- Sistem melacak dua staf dalam satu transaksi:  
  - `orders.cashier_id`: staf kasir.  
  - `order_items.handled_by`: staf pembuat menu.  
- Integrasi penuh dengan Midtrans API untuk pembayaran online.  
- Setelah pembayaran sukses, struk digital dikirim ke pelanggan via WhatsApp Gateway API.

### 3.6 Layanan Analitik & Data Mining (Python)

- Layanan analitik berjalan sebagai proses FastAPI terpisah.  
- Admin dapat mengatur jadwal eksekusi model ML melalui UI Filament.  
- Cron job memicu Laravel Scheduler untuk menjalankan perintah analitik.  
- Layanan Python membaca data dari PostgreSQL dan menulis hasil ke tabel analitik.  
- Minimal lima model diterapkan:  
  - Association (FP-Growth)  
  - Estimation (Random Forest)  
  - Prediction (Random Forest)  
  - Clustering (K-Means)  
  - Classification (Random Forest)

### 3.7 Pelaporan

- Laporan keuangan dihitung secara *pre-calculated* untuk performa cepat.  
- Hasil analitik ditampilkan di Filament menggunakan *Chart Widget* kustom.

---

## 3.8 Keputusan Arsitektural & Strategi Data

### 3.8.1 Strategi Soft Delete

**Prinsip:** Soft delete hanya diterapkan pada data master/referensi yang bisa digunakan kembali. Data transaksional TIDAK PERNAH dihapus.

**Tabel dengan Soft Delete (5 tabel):**
- `users` - User bisa dinonaktifkan lalu diaktifkan kembali
- `categories` - Kategori bisa dihapus sementara lalu direstore
- `menu` - Menu item bisa dinonaktifkan (sold out permanen) lalu dijual lagi
- `ingredients` - Bahan baku bisa tidak dijual lagi lalu dijual lagi
- `promotions` - Promo bisa dihapus lalu diaktifkan kembali

**Tabel TANPA Soft Delete (7 tabel):**
- `orders` - Transaksi adalah audit trail, tidak boleh dihapus
- `order_items` - Detail transaksi harus permanen untuk laporan keuangan
- `cashier_sessions` - Log sesi kasir adalah audit trail
- `ingredient_batches` - Histori pembelian bahan harus permanen
- `waste_records` - Catatan waste untuk audit
- `applied_promotions` - Histori penggunaan promo untuk analitik
- `financial_reports` - Laporan keuangan pre-calculated

**Tabel dengan Hard Delete (3 tabel):**
- `student_profiles` - Data mahasiswa bisa dihapus permanen jika lulus/keluar
- `analytics_runs` - Log eksekusi analitik bisa dibersihkan
- `user_reset_tokens` - Token reset password bisa dihapus setelah digunakan

**Implementasi:**
- Tabel dengan soft delete menggunakan Laravel `SoftDeletes` trait
- Kolom `deleted_at` (timestamp nullable) untuk menandai penghapusan
- UI Filament menampilkan `RestoreAction` dan `ForceDeleteAction` (hanya untuk super admin)
- Filter `TernaryFilter` untuk melihat data yang dihapus
- Scope `withTrashed()`, `onlyTrashed()` untuk query builder

### 3.8.2 Kolom is_active untuk Status Aktif/Nonaktif

**Prinsip:** Kolom `is_active` digunakan untuk pengaturan visibility/availability tanpa menghapus data.

**Tabel dengan is_active (4 tabel):**
- `users` - Admin bisa menonaktifkan user tanpa menghapus
- `categories` - Kategori bisa disembunyikan dari menu kasir
- `menu` - Menu item bisa dinonaktifkan (berbeda dari sold_out)
- `ingredients` - Bahan baku bisa dinonaktifkan dari sistem

**Perbedaan is_active vs status vs soft delete:**
- `is_active = false` → Data tidak tampil di UI tapi masih di database (reversible, instant)
- `status = 'sold_out'` → Menu habis sementara (stok kosong), bisa diubah otomatis oleh sistem
- `deleted_at IS NOT NULL` → Data dihapus secara soft (perlu restore action untuk mengembalikan)

**Implementasi:**
- Kolom `is_active` boolean dengan default `true`
- Model Laravel menambahkan scope: `scopeActive($query)` → `$query->where('is_active', true)`
- UI Filament:
  - Form: `Toggle::make('is_active')->label('Aktif')->default(true)`
  - Table: `IconColumn::make('is_active')->boolean()->label('Status')`
  - Filter: `TernaryFilter::make('is_active')->label('Status Aktif')`

### 3.8.3 Integritas Data Transaksi: Snapshot Denormalisasi

**Keputusan:** Menggunakan denormalisasi (snapshot) untuk menjaga integritas histori transaksi.

**Alasan:**
- Order adalah audit trail yang harus immutable (tidak berubah setelah dibuat)
- Jika menu/promo berubah, transaksi lama harus tetap menampilkan data saat transaksi terjadi
- Versioning (SCD Type 2) terlalu kompleks dan over-engineering untuk use case POS
- Denormalisasi lebih mudah di-query dan performant untuk laporan

**Implementasi di tabel orders (customer snapshot):**
```sql
-- Snapshot data customer saat transaksi
customer_id BIGINT REFERENCES users(id) ON DELETE SET NULL,  -- FK ke users, nullable
customer_name VARCHAR(255) NULL,       -- Snapshot nama (auto/manual)
customer_type VARCHAR(20) DEFAULT 'guest',  -- 'student' atau 'guest'
```

**Aturan Pengisian Customer Snapshot:**

| Skenario | customer_id | customer_name | customer_type | price |
|---|---|---|---|---|
| Mahasiswa (pilih dari dropdown) | FK ke users | Auto-snapshot dari users.name | 'student' | student_price |
| Guest dengan nama (kasir input) | NULL | Input manual kasir | 'guest' | base_price |
| Guest anonim (tanpa nama) | NULL | NULL | 'guest' | base_price |

**Business Rules:**
- Jika `customer_id` diisi → `customer_type` otomatis 'student', `customer_name` auto-snapshot dari `users.name`
- Jika `customer_id` kosong → `customer_type` = 'guest', `customer_name` bisa diisi manual atau NULL
- Snapshot `customer_name` TIDAK berubah meskipun user mengganti nama di kemudian hari
- Dropdown customer hanya menampilkan user dengan role 'student'

**Implementasi di tabel order_items (menu & promo snapshot):**
```sql
-- Snapshot data menu saat transaksi
product_name VARCHAR(255) NOT NULL,  -- Copy dari menu.name
price DECIMAL(10,2) NOT NULL,        -- Copy dari menu.price atau menu.student_price
base_price DECIMAL(10,2) NOT NULL,   -- Harga asli sebelum diskon

-- Snapshot data promo (jika ada)
discount_amount DECIMAL(10,2) DEFAULT 0,
discount_name VARCHAR(255),  -- Copy dari promotions.name

-- Calculated field
line_total DECIMAL(10,2) NOT NULL,   -- (price × quantity) - discount_amount

-- Referensi FK tetap ada untuk analitik (nullable jika menu deleted)
menu_id BIGINT REFERENCES menu(id) ON DELETE SET NULL
```

**Keuntungan:**
- ✅ Transaksi lama tetap akurat meskipun menu berubah/dihapus
- ✅ Query laporan lebih mudah (tidak perlu JOIN ke tabel history)
- ✅ Performa laporan lebih cepat (data sudah ter-denormalisasi)
- ✅ Tidak perlu migrasi data lama saat menu berubah
- ✅ Audit trail jelas: "Customer bayar berapa saat transaksi?"
- ✅ Segmentasi laporan per customer_type (student vs guest)

**Trade-off:**
- ❌ Kehilangan tracking: "Menu ini pernah berubah harga berapa kali?"
- ❌ Analitik terbatas: "Berapa revenue menu X per versi harga?"
- **Keputusan:** Accept loss - untuk POS, akurasi transaksi > tracking perubahan menu

### 3.8.4 Strategi Input Resep Menu (Optional with Warnings)

**Keputusan:** Admin TIDAK dipaksa memasukkan resep saat membuat menu baru.

**Alasan:**
- Admin mungkin membuat menu dulu, resep ditambahkan kemudian
- Resep kompleks bisa memakan waktu lama untuk di-input
- Tidak semua menu butuh tracking ingredients (menu jadi/beli dari luar)
- Forcing input resep = bad UX, menghambat workflow admin

**Implementasi:**
- Relasi `menu_ingredients` boleh kosong saat menu dibuat
- UI Filament menampilkan warning di:
  - **Form Create/Edit Menu:** Infolist banner kuning jika `menuIngredients->isEmpty()`
    ```
    ⚠️ Menu ini belum memiliki resep. Stock tidak akan terpantau otomatis.
    ```
  - **Table Menu Resource:** BadgeColumn status "No Recipe" dengan warna kuning
  - **Dashboard Widget:** Card "Menu Tanpa Resep" dengan counter dan link
- Kasir tetap bisa menjual menu tanpa resep (tidak ada blocking)
- Order tanpa resep TIDAK mengurangi stock ingredients (no retroactive adjustment)

**SOP Operasional:**
1. Admin buat menu baru → Save tanpa resep (allowed)
2. Sistem tampilkan warning di UI
3. Menu bisa dijual, tapi stock tidak tracked
4. Admin input resep di lain waktu via IngredientsRelationManager
5. **PENTING:** Stock mulai di-track DARI SEKARANG, bukan retroaktif
6. Order lama (sebelum resep dibuat) tetap tidak mengurangi stock

**Keuntungan:**
- ✅ Workflow admin lebih fleksibel
- ✅ Menu bisa dijual segera (tidak terhambat input resep)
- ✅ Admin bisa batch-input resep di waktu senggang
- ✅ Warning system memberi reminder tanpa blocking

**Trade-off:**
- ❌ Stock tidak tracked untuk menu tanpa resep
- ❌ Order lama tidak terhitung dalam stock reduction
- **Keputusan:** Accept loss - data akurat DARI SEKARANG > data korup SELAMANYA

### 3.8.5 Larangan Retroactive Stock Adjustment

**Prinsip:** Data transaksi lama TIDAK BOLEH diubah atau di-adjust secara retroaktif.

**Alasan Risiko Retroactive Adjustment:**
1. **Double Counting:** Order lama bisa ter-count dua kali (saat dibuat + saat adjustment)
2. **Stock Negatif:** Adjust order lama bisa bikin stock ingredient jadi negatif
3. **Laporan Keuangan Rusak:** Laporan bulan lalu berubah, tidak match dengan bank statement
4. **Audit Trail Rusak:** Transaksi yang sudah di-audit berubah datanya
5. **Kompleksitas Tinggi:** Butuh transaction log, rollback mechanism, version history
6. **Bug Risk:** Satu bug di adjustment logic bisa corrupt seluruh database

**Contoh Kasus Bahaya:**
```
Skenario:
- 1 Jan: Beli 10kg kopi, stock = 10kg
- 5 Jan: Jual Espresso 5x (belum ada resep), stock = 10kg (tidak berubah)
- 10 Jan: Admin input resep Espresso (15g kopi per cup)
- Admin coba "adjust" order 5 Jan secara retroaktif

Hasil jika retroactive diizinkan:
- Stock reduction: 5 cup × 15g = 75g = 0.075kg
- Stock sekarang: 10kg - 0.075kg = 9.925kg

MASALAH:
- Order 5 Jan sudah di-charge ke customer, sudah di-laporan keuangan
- Stock opname 6 Jan mencatat 10kg (belum dikurangi)
- Laporan financial 5 Jan tidak match dengan stock report
- Jika ada bug, semua transaksi lama ter-corrupt
```

**Keputusan:**
- ❌ TIDAK ada fitur retroactive adjustment
- ❌ TIDAK ada re-calculate stock untuk order lama
- ❌ TIDAK ada "apply recipe to old orders"
- ✅ Accept loss: Order sebelum resep = stock tidak tracked
- ✅ Prinsip: Data akurat MULAI SEKARANG > corrupt selamanya

**Implementasi:**
- Observer `OrderObserver` hanya kurangi stock jika menu punya resep
- Jika menu tidak ada resep saat order dibuat → tidak kurangi stock (skip)
- Jika admin menambah resep nanti → order lama tetap tidak di-adjust
- UI menampilkan badge "Stock Not Tracked" di order detail untuk order tanpa resep

### 3.8.6 UI/UX Framework: 100% Filament Native untuk MVP

**Keputusan:** Admin panel menggunakan 100% Filament native components, TIDAK ada custom Livewire components untuk MVP.

**Alasan:**
- Solo developer dengan waktu terbatas
- Filament sudah provide 90% kebutuhan CRUD out-of-the-box
- Custom component = maintenance overhead
- Faster time-to-market lebih penting dari fancy UI
- Filament terus update dengan best practices

**Pengecualian (boleh custom setelah MVP launch):**
- Chart.js untuk advanced analytics (Widget)
- Real-time order tracking (Livewire polling)
- Stock reconciliation UI (complex workflow)

**Yang BISA dilakukan dengan 100% Filament Native:**
- ✅ CRUD resources (Forms, Tables, Filters, Actions)
- ✅ Relation Managers untuk menu ingredients
- ✅ Modals untuk void reason, stock adjustment notes
- ✅ Inline create untuk ingredients di recipe input
- ✅ Widgets untuk dashboard (stats, charts, lists)
- ✅ Notifications untuk low stock alerts, daily summary
- ✅ Global search dengan command palette
- ✅ Badges dan IconColumns untuk status indicators
- ✅ Custom actions dengan forms untuk complex workflows

**Implementasi:**
- Setup Filament Panel dengan Tabler.io theme
- Gunakan Filament builders: `Forms\`, `Tables\`, `Actions\`, `Infolists\`, `Widgets\`
- Manfaatkan Filament plugins untuk fitur advanced (FilamentExcel, FilamentSpatie)
- Custom logic di Model observers dan Service classes
- Defer custom Livewire components ke Phase 2 (post-MVP)

### 3.8.7 Smart Reconciliation: Deferred to Phase 2

**Keputusan:** Smart Stock Reconciliation ditunda sampai post-MVP (Phase 2).

**Alasan:**
- Kompleksitas tinggi: 9+ components needed (tables, models, services, resources, observers, widgets, tests)
- Estimasi implementasi: 1.5-2 minggu full-time
- Use case terbatas: Hanya untuk existing business dengan historical data
- MVP priority: Get basic POS working first
- Risk tinggi: Complex calculation logic prone to bugs

**Yang Dibutuhkan untuk Smart Reconciliation:**
1. Tabel `stock_movements` - Audit trail all stock changes
2. Tabel `stock_adjustments` - Manual corrections (waste, opname)
3. Kolom `is_stock_calculated` di `menu` - Flag apakah resep sudah dibuat
4. Service class `StockReconciliationService` - Business logic
5. Resource `StockAdjustmentResource` - UI untuk manual adjustment
6. Observer `OrderObserver` - Auto-create stock_movements
7. Observer `WasteRecordObserver` - Auto-create stock_movements
8. Widget `ShrinkageWidget` - Dashboard shrinkage percentage
9. Action `ReconcileStockAction` - Button di MenuResource
10. Unit tests untuk semua calculation logic

**Alternatif untuk MVP:**
- Admin input stock ingredients secara manual di `IngredientResource`
- Waste tracking di `WasteRecordResource` (manual input)
- Simple low stock alerts (threshold-based)
- Monthly stock opname dengan manual adjustment
- Accept shrinkage sebagai normal business operation

**Roadmap:**
- **Phase 1 (MVP):** Basic POS + Manual Stock Management
- **Phase 2 (Post-Launch):** Smart Reconciliation + Advanced Analytics
- **Phase 3 (Scale):** Machine Learning untuk demand forecasting

### 3.8.8 Small But Critical UX Features (High Priority)

Fitur-fitur kecil dengan impact besar yang bisa diimplementasi dengan 100% Filament native:

**1. Low Stock Badge (Priority: High)**
```php
// Implementasi: BadgeColumn di MenuResource
BadgeColumn::make('stock_status')
    ->label('Stock')
    ->formatStateUsing(fn ($record) => $record->isLowStock() ? 'Low Stock' : 'Available')
    ->color(fn ($record) => $record->isLowStock() ? 'warning' : 'success')
```

**2. Void Reason Modal (Priority: High)**
```php
// Implementasi: Action dengan form di OrderResource
Action::make('void')
    ->requiresConfirmation()
    ->modalHeading('Void Transaction')
    ->form([
        Select::make('void_reason')
            ->options([
                'customer_cancel' => 'Customer Cancel',
                'wrong_order' => 'Wrong Order',
                'payment_failed' => 'Payment Failed',
                'other' => 'Other',
            ])
            ->required(),
        Textarea::make('void_notes'),
    ])
    ->action(fn (Order $record, array $data) => $record->void($data))
```

**3. Global Search Configuration (Priority: Medium)**
```php
// Implementasi: getGloballySearchableAttributes di Resource
public static function getGloballySearchableAttributes(): array
{
    return ['name', 'category.name', 'ingredients.name'];
}

// Aktivasi di Filament Panel Provider
->globalSearchKeyBindings(['command+k', 'ctrl+k'])
```

**4. Unit Converter Helper (Priority: Medium)**
```php
// Implementasi: Placeholder di TextInput
TextInput::make('quantity')
    ->numeric()
    ->suffix('gram')
    ->helperText('1kg = 1000g, 1L = 1000ml')
    ->live()
```

**5. Daily Summary Notification (Priority: Low)**
```php
// Implementasi: Laravel Scheduled Command
protected function schedule(Schedule $schedule): void
{
    $schedule->command('reports:daily-summary')
        ->dailyAt('23:00')
        ->when(fn () => CashierSession::today()->exists());
}
```

**6. Stock Opname Reminder Widget (Priority: Low)**
```php
// Implementasi: Filament Widget
class StockOpnameReminderWidget extends Widget
{
    protected static string $view = 'filament.widgets.stock-opname-reminder';
    
    public function getLastOpnameDate(): ?Carbon
    {
        return WasteRecord::where('type', 'opname')
            ->latest('created_at')
            ->value('created_at');
    }
}
```

---

## 3.9 Rencana Implementasi Order Management

Order Management adalah jantung (core) sistem POS. Berikut langkah-langkah implementasi sistematis:

### Step 1: StudentProfile Model & Migration (Foundation)
**Tujuan:** Enable student discount pricing logic

**Tasks:**
- Create migration `create_student_profiles_table`
  - Kolom: id, user_id (unique FK), student_id (NIM, unique), faculty, major, year
  - Timestamps: created_at, updated_at
- Create model `StudentProfile` dengan relasi `belongsTo(User::class)`
- Update model `User` dengan relasi `hasOne(StudentProfile::class)`
- Add method `isStudent()` di User model: `return $this->studentProfile()->exists()`
- Create seeder `StudentProfileSeeder` dengan 10 sample students
- UI Admin: Create `StudentProfileResource` untuk CRUD data mahasiswa

**Estimasi:** 30-60 menit

### Step 2: AppliedPromotion Model (Promo Tracking)
**Tujuan:** Track which promotions used in orders

**Tasks:**
- Create migration `create_applied_promotions_table`
  - Kolom: id, order_id (FK), promotion_id (FK nullable), discount_type, discount_value, discount_amount
  - Timestamps: created_at, updated_at
- Create model `AppliedPromotion` dengan relasi:
  - `belongsTo(Order::class)`
  - `belongsTo(Promotion::class)`
- Update model `Order` dengan relasi `hasMany(AppliedPromotion::class)`

**Estimasi:** 30 menit

### Step 3: Order & OrderItem Denormalization Columns
**Tujuan:** Snapshot data untuk immutable transaction history

**Tasks:**
- Create migration `add_snapshot_columns_to_orders_table` (ALTER, bukan modify existing):
  - `customer_name` varchar(255) nullable - snapshot nama customer (auto dari users.name jika student, manual input jika guest)
  - `customer_type` varchar(20) default 'guest' - 'student' jika customer_id diisi, 'guest' jika tidak
  - `subtotal` decimal(12,2) - sum of all order_items.line_total
  - `discount_total` decimal(12,2) default 0 - total semua diskon order-level
  - `tax_amount` decimal(12,2) default 0 - pajak (jika ada)
  - `grand_total` decimal(12,2) - subtotal - discount_total + tax_amount
  - `void_reason` varchar(255) nullable - alasan void (enum: customer_cancel, wrong_order, payment_failed, other)
  - `void_notes` text nullable - catatan void detail dari kasir
  - `voided_at` timestamp nullable - waktu void dilakukan
  - `voided_by` bigint FK nullable → users(id) - user yang melakukan void
  - Index: customer_type, voided_at

- Create migration `add_snapshot_columns_to_order_items_table` (ALTER):
  - `product_name` varchar(255) NOT NULL - snapshot menu.name saat transaksi
  - `price` decimal(10,2) NOT NULL - harga jual (student_price jika student, price jika guest)
  - `base_price` decimal(10,2) NOT NULL - harga asli (menu.price) sebelum diskon item
  - `discount_amount` decimal(10,2) default 0 - diskon per item dari promo
  - `discount_name` varchar(255) nullable - nama promo yang diterapkan
  - `line_total` decimal(10,2) NOT NULL - (price × quantity) - discount_amount

**Customer Snapshot Business Logic:**
- Jika `customer_id` diisi (student): auto-set `customer_name` = user.name, `customer_type` = 'student'
- Jika `customer_id` kosong + kasir input nama: `customer_name` = input, `customer_type` = 'guest'
- Jika `customer_id` kosong + tanpa nama: `customer_name` = null, `customer_type` = 'guest'
- Dropdown customer di UI hanya menampilkan users dengan role 'student'

**Estimasi:** 45 menit

### Step 4: Order Calculation Logic
**Tujuan:** Auto-calculate prices berdasarkan customer type dan promotions

**Tasks:**
- Create service class `app/Services/OrderCalculationService.php`:
  ```php
  class OrderCalculationService
  {
      public function calculateOrderItem(Menu $menu, int $quantity, ?User $customer): array
      {
          // Tentukan harga berdasarkan customer type
          $price = $customer?->isStudent() ? $menu->student_price : $menu->price;
          $basePrice = $menu->price;
          
          // Apply item-level discounts (jika ada)
          $discountAmount = 0; // Logic untuk promo per item
          
          return [
              'product_name' => $menu->name,
              'price' => $price,
              'base_price' => $basePrice,
              'discount_amount' => $discountAmount,
              'line_total' => ($price * $quantity) - $discountAmount,
          ];
      }
      
      public function calculateOrderTotal(Order $order): array
      {
          $subtotal = $order->items->sum('line_total');
          $discountTotal = 0; // Apply order-level promotions
          $taxAmount = 0; // Hitung pajak jika ada
          $grandTotal = $subtotal - $discountTotal + $taxAmount;
          
          return [
              'subtotal' => $subtotal,
              'discount_total' => $discountTotal,
              'tax_amount' => $taxAmount,
              'grand_total' => $grandTotal,
          ];
      }
  }
  ```

- Create observer `app/Observers/OrderObserver.php`:
  ```php
  class OrderObserver
  {
      public function creating(Order $order): void
      {
          // Snapshot customer data
          if ($order->customer) {
              $order->customer_name = $order->customer->name;
              $order->customer_type = $order->customer->isStudent() ? 'student' : 'guest';
          }
      }
      
      public function created(Order $order): void
      {
          // Calculate totals after items created
          $calculations = app(OrderCalculationService::class)->calculateOrderTotal($order);
          $order->update($calculations);
          
          // Reduce stock for menu with recipes
          foreach ($order->items as $item) {
              if ($item->menu && $item->menu->menuIngredients->isNotEmpty()) {
                  app(InventoryService::class)->reduceStock($item->menu, $item->quantity);
              }
          }
      }
  }
  ```

**Estimasi:** 2-3 jam

### Step 5: OrderResource (Filament Admin UI)
**Tujuan:** CRUD interface untuk manage orders

**Tasks:**
- Create `app/Filament/Resources/OrderResource.php`:
  - **Form:** Read-only untuk view order details (tidak bisa edit transaksi)
  - **Table:** List orders dengan filters (status, payment, date range, customer type)
  - **Actions:**
    - `ViewAction` - Lihat detail order
    - `VoidAction` - Void transaction dengan modal form (reason + notes)
    - `PrintReceiptAction` - Print thermal receipt
  - **Infolists:** Display order summary dengan sections:
    - Order Info (order number, date, cashier, customer)
    - Items (table of order items)
    - Pricing (subtotal, discounts, tax, grand total)
    - Payment (method, status, payment date)
    - Void Info (if voided: reason, notes, voided by)
  - **Filters:**
    - SelectFilter status (pending, completed, cancelled, voided)
    - SelectFilter payment_method
    - SelectFilter customer_type
    - Filter tanggal (today, this week, this month, custom range)
  - **Badges:**
    - Status badge dengan color coding
    - Payment status badge
    - Customer type badge (student/guest)

- Create `app/Filament/Resources/OrderResource/Pages/`:
  - `ListOrders.php` - Table page dengan filters & bulk actions
  - `ViewOrder.php` - Detail page dengan Infolist
  - (Tidak ada Create/Edit page - orders created by Kasir UI)

**Estimasi:** 3-4 jam

### Step 6: Testing & Validation
**Tujuan:** Ensure calculation accuracy dan data integrity

**Tasks:**
- Create feature test `tests/Feature/OrderManagementTest.php`:
  - Test create order guest (regular price)
  - Test create order student (student price)
  - Test apply promotion (discount calculation)
  - Test void order (reason + notes)
  - Test stock reduction (with/without recipe)
  - Test order total calculation (subtotal, discount, tax, grand total)
  - Test immutability (snapshot data tidak berubah saat menu diubah)

- Manual testing:
  - Create order via Kasir UI
  - Verify stock reduction jika menu ada resep
  - Verify no stock reduction jika menu tidak ada resep
  - Change menu price/name
  - Verify order lama tetap tampil data lama (snapshot)
  - Void order dan verify audit trail

**Estimasi:** 2-3 jam

### Step 7: Menu Ingredients System (Optional - Bisa Parallel)
**Tujuan:** Enable recipe-based stock tracking

**Tasks:**
- Already covered in section 3.2 (Manajemen Inventori)
- Create `MenuIngredient` model & migration
- Create `IngredientsRelationManager` di `MenuResource`
- Implement inline create untuk ingredients
- Show warning badge jika menu tidak ada resep
- Create dashboard widget "Menu Tanpa Resep"

**Estimasi:** 4-6 jam

**Total Estimasi Keseluruhan:** 1-2 hari development (8-16 jam)

**Dependencies:**
- Step 1 (StudentProfile) harus selesai dulu sebelum Step 4 (Calculation Logic)
- Step 2-3 bisa parallel
- Step 5 (UI) butuh Step 1-4 selesai
- Step 6 (Testing) paling akhir
- Step 7 (Menu Ingredients) bisa parallel atau deferred

**Priority Order:**
1. **Step 1** (StudentProfile) - Foundation
2. **Step 2-3** (Models & Migrations) - Data structure
3. **Step 4** (Calculation Logic) - Business logic
4. **Step 5** (OrderResource UI) - Admin interface
5. **Step 6** (Testing) - Quality assurance
6. **Step 7** (Menu Ingredients) - Optional/parallel

---

## 4. Kebutuhan Non-Fungsional (Non-Functional Requirements)

### 4.1 Performa

- PWA kasir harus dimuat dalam waktu kurang dari 3 detik dan respons < 500ms.  
- Laporan admin harus dimuat dalam < 5 detik.  
- Proses data mining dijadwalkan di luar jam operasional.

### 4.2 Keamanan

- Semua komunikasi menggunakan HTTPS.  
- Password di-hash dengan hash bawaan laravel.  
- Layanan Python hanya memiliki akses terbatas (read/write sesuai kebutuhan).  

### 4.3 Keandalan (Autopilot)

- Semua layanan berjalan di Docker dengan kebijakan `restart: always`.  
- Backup database otomatis terjadwal.  
- Pemantauan uptime eksternal aktif.  
- Server melakukan pembaruan keamanan otomatis.

### 4.4 Lingkungan

- Harus dapat berjalan di VPS Linux dengan 1–2 vCPU dan 2–4 GB RAM.  
- Mendukung browser modern untuk PWA.  
- Lingkungan *staging* identik dengan *production*.

### 4.5 Pemeliharaan

- Semua dokumentasi (DBML, Mermaid, dan SRS ini) disimpan dalam repository Git.  
- Setiap perubahan kode harus disertai pembaruan dokumentasi.

---

## 5. Kebutuhan Database

- Database pusat menggunakan PostgreSQL sesuai skema di `planning/02_database_schema.dbml`.  
- Database lokal menggunakan IndexedDB sesuai skema di `planning/04_indexeddb_schema.md`.

### 5.1 Strategi Primary Key: UUID untuk Offline Sync

**Hanya 3 tabel yang membutuhkan UUID** - yaitu tabel yang client (kasir) bisa CREATE secara offline:

- **Alasan:** UUID hanya diperlukan jika client harus generate ID secara lokal tanpa koneksi server. Tabel lain yang hanya di-read oleh client tetap menggunakan auto-increment untuk efisiensi storage.
- **Format:** Ordered UUID (timestamp-based, sortable) menggunakan Laravel built-in `HasUuids` trait.
- **Storage Efficiency:** UUID = 16 bytes vs BIGINT = 8 bytes. Dengan hanya 3 tabel, overhead storage minimal.

**Tabel dengan UUID (3 tabel offline-critical):**
- `orders` - Kasir membuat transaksi offline
- `order_items` - Dibuat bersama orders
- `cashier_sessions` - Kasir memulai shift offline

**Tabel dengan Auto-Increment Serial (semua tabel lainnya):**
- **User & Access:** `roles`, `users`, `student_profiles`, `user_reset_tokens`
- **Menu & Inventory:** `categories`, `menu`, `ingredients`, `ingredient_batches`, `menu_ingredients`, `waste_records`
- **Promotions:** `promotions`, `promotion_rules`, `applied_promotions`
- **Reports:** `debts`, `financial_reports`
- **Analytics:** `analytics_runs`, `analytics_schedules`, `association_rules`, `kmeans_clusters`, `menu_cluster_assignments`, `classification_results`, `estimation_results`, `prediction_models`, `predictions`

**Catatan:** 
- Tabel referensi (menu, categories, promotions) di-sync ke client sebagai read-only data dengan ID integer dari server.
- Hanya tabel yang kasir CREATE offline yang butuh UUID untuk menghindari ID collision saat sync.

### 5.2 Tabel Tambahan

- **Tabel `menu_procedures`** ditambahkan untuk menyimpan SOP pembuatan menu dengan struktur:
  - `id` (primary key)
  - `menu_id` (foreign key to menu)
  - `step_number` (integer, urutan langkah)
  - `title` (varchar, judul langkah)
  - `description` (text, deskripsi detail)
  - `duration_seconds` (integer, estimasi durasi dalam detik)
  - `is_critical` (boolean, apakah langkah critical)
  - `image_url` (varchar, link gambar ilustrasi - nullable)
  - `notes` (text, catatan tambahan - nullable)
  - `is_active` (boolean, status aktif/nonaktif)
  - `created_at`, `updated_at` (timestamps)
- Relasi: Satu menu dapat memiliki banyak procedure steps (one-to-many).

---

## 6. Kebutuhan Deployment

- Aplikasi dibundle menggunakan Docker Compose.  
- CI/CD (misalnya GitHub Actions) harus men-deploy branch `staging` ke server staging dan `main` ke server produksi secara otomatis.

---

## 7. Riwayat Perubahan Dokumen

### Version 1.3 (1 Maret 2026)
- **Keputusan Arsitektural Major:**
  
  - **Strategi Soft Delete Terdefinisi:**
    - 5 tabel dengan soft delete (users, categories, menu, ingredients, promotions)
    - 7 tabel tanpa soft delete (orders, order_items, cashier_sessions, ingredient_batches, waste_records, applied_promotions, financial_reports)
    - 3 tabel dengan hard delete (student_profiles, analytics_runs, user_reset_tokens)
    - Prinsip: Data transaksional adalah audit trail, tidak boleh dihapus
  
  - **Kolom is_active untuk Status Aktif/Nonaktif:**
    - 4 tabel implement is_active (users, categories, menu, ingredients)
    - Perbedaan is_active vs status vs soft delete dijelaskan
    - Implementasi: scopeActive(), Toggle, IconColumn, TernaryFilter di Filament
  
  - **Integritas Data: Snapshot Denormalisasi:**
    - Keputusan: Gunakan denormalisasi (snapshot) untuk order history
    - Tabel order_items menyimpan snapshot product_name, price, discount_name
    - Alasan: Transaksi harus immutable, tidak terpengaruh perubahan menu
    - Trade-off accepted: Kehilangan tracking perubahan menu, prioritas akurasi transaksi
  
  - **Strategi Input Resep Menu:**
    - Keputusan: Optional with warnings (tidak memaksa)
    - UI menampilkan warning jika menu belum ada resep
    - Menu tanpa resep tetap bisa dijual, stock tidak tracked
    - Admin bisa input resep nanti, stock tracking mulai dari saat resep dibuat
  
  - **Larangan Retroactive Stock Adjustment:**
    - Keputusan: TIDAK ADA retroactive adjustment untuk order lama
    - Alasan: Risiko double counting, stock negatif, laporan rusak
    - Prinsip: Data akurat MULAI SEKARANG > corrupt selamanya
    - Order sebelum resep dibuat = stock tidak tracked (accept loss)
  
  - **UI/UX Framework: 100% Filament Native untuk MVP:**
    - Keputusan: Tidak ada custom Livewire components untuk MVP
    - Alasan: Solo developer, faster time-to-market, easier maintenance
    - Filament native sudah provide 90% kebutuhan CRUD
    - Custom components deferred ke Phase 2 (post-MVP)
  
  - **Smart Reconciliation: Deferred to Phase 2:**
    - Kompleksitas tinggi (9+ components, 1.5-2 minggu implementasi)
    - MVP fokus pada basic POS + manual stock management
    - Alternative: Manual input stock, threshold-based alerts
    - Roadmap: Phase 1 (MVP) → Phase 2 (Smart Reconciliation) → Phase 3 (ML Forecasting)
  
  - **Small But Critical UX Features (High Priority):**
    - Low stock badge di MenuResource (BadgeColumn)
    - Void reason modal dengan Select + Textarea (Action with form)
    - Global search configuration (command palette)
    - Unit converter helper (helperText)
    - Daily summary notification (Scheduled command)
    - Stock opname reminder widget
    - Semua implementable dengan 100% Filament native

### Version 1.2 (28 Januari 2026)
- **UUID untuk Offline Sync (Optimized):**
  - Hanya 3 tabel yang menggunakan UUID: `orders`, `order_items`, `cashier_sessions`
  - Alasan: Hanya tabel yang client CREATE offline yang butuh UUID
  - Semua tabel lain tetap auto-increment untuk efisiensi storage (UUID 16 bytes vs BIGINT 8 bytes)
  - Menggunakan Laravel built-in `HasUuids` trait (ordered UUID, timestamp-based)
  - IndexedDB menyimpan data referensi dengan server ID (integer), transaksi dengan client UUID

### Version 1.1 (8 November 2025)
- **Update Stack Teknologi:**
  - PHP 8.4, Laravel 12, Filament 4.x
  - Vue.js 3, Pinia, Vue Router, Axios
  - Laravel Sanctum untuk API authentication
  - PostgreSQL 15+
  
- **100% Code Reuse Strategy:**
  - **UI Kasir dan Pelanggan menggunakan source code Vue.js yang IDENTIK 100%**
  - Tidak ada file Vue terpisah untuk kasir vs pelanggan
  - Satu codebase Vue di-build dua kali dengan environment variable berbeda:
    - Build Kasir: `VITE_OFFLINE_MODE=true` → Enable IndexedDB + Service Workers
    - Build Customer: `VITE_OFFLINE_MODE=false` → Disable offline features
  - Conditional rendering dengan `v-if` untuk fitur khusus kasir
  
- **Unified UI Framework:**
  - Semua interface (Admin, Kasir, Pelanggan) menggunakan Bootstrap 5 dengan template Tabler.io
  - UI Admin (Filament) dikustomisasi dengan Tabler.io theme
  - UI Kasir & Pelanggan (Vue.js SPA) menggunakan Tabler.io Bootstrap components
  - Konsistensi visual dan UX di seluruh aplikasi
  
- **Alasan Perubahan ke Vue.js:**
  - Livewire tidak support offline capability (butuh koneksi server)
  - Vue.js + Service Workers enable true offline-first PWA
  - 100% code reuse lebih mudah dicapai dengan Vue SPA + build flags
  - Performa lebih baik dengan client-side rendering
  - State management lebih powerful dengan Pinia
  
- **Dynamic Content Strategy (Vue I18n):**
  - Untuk membedakan teks/konten antara kasir dan pelanggan:
    - Gunakan Vue I18n dengan locale files terpisah (`cashier.json` dan `customer.json`)
    - Build dengan `VITE_APP_MODE=cashier` atau `VITE_APP_MODE=customer`
    - Komponen Vue tetap satu file, hanya teks yang berbeda via `$t('key')`
    - Conditional rendering dengan `v-if="isCashier"` untuk fitur khusus
  - Contoh: Halaman transaksi menampilkan "Daftar Transaksi Shift" untuk kasir vs "Riwayat Pesanan" untuk pelanggan
  - Easy maintenance: Edit JSON locale files tanpa touch Vue components
  
- **Keuntungan Arsitektur:**
  - **Zero Code Duplication:** Satu file Vue untuk dua interface
  - **Identical UI/UX:** Kasir dan pelanggan dijamin tampil sama persis
  - **Flexible Content:** Vue I18n untuk custom text per interface tanpa duplikasi
  - **Single Source of Truth:** Bug fix sekali, berlaku di semua tempat
  - **Faster Development:** Develop sekali, build dua kali
  - **Easier Maintenance:** Satu codebase untuk di-maintain, teks di locale files
  - **Easier Testing:** Test suite dapat di-reuse
  - **Smaller Bundle:** Tree-shaking remove unused code di build customer
  - **Better Offline Support:** Service Workers + IndexedDB untuk PWA kasir

### Version 1.0 (29 Oktober 2025)
- Rilis awal dokumen requirements
- Definisi arsitektur sistem hybrid (offline-first + cloud)
- Spesifikasi tiga interface utama: Admin (Filament), Kasir (PWA), Pelanggan (Livewire)
- Integrasi data mining dengan Python/FastAPI

---

**Dokumen ini akan diperbarui setiap kali terjadi perubahan signifikan dalam desain arsitektur atau kebutuhan sistem.**
