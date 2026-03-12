# CLAUDE.md — POS Cafe W9 STIE Totalwin
# FASE AKTIF: Modul Transaksi (Pelanggan + Kasir)

## Gambaran Proyek

Sistem Point of Sale (POS) berbasis web PWA untuk W9 Cafe STIE Totalwin Semarang.
Fase ini mencakup modul Pelanggan (mobile PWA) & Kasir (desktop web).
Modul Admin, Inventori, Data Mining dikerjakan di fase terpisah.

- **Dokumen Referensi:** C100.S2T25K09 (Proposal Capstone)
- **Tim:** Ruben (Data Mining), Ivan (Fullstack Transaction), Nio (Fullstack Inventory)
- **Pembimbing:** Yudi Eko Windarto, S.T., M.Kom. & Rinta Kridalukmana, S.Kom., M.T., Ph.D.

---

## Tech Stack

| Layer | Teknologi |
|---|---|
| Framework Backend | Laravel 11 |
| Bahasa | PHP 8.2+ |
| Database Utama | PostgreSQL 16 |
| Database Offline | IndexedDB (browser, via `idb`) |
| ORM | Eloquent |
| Auth | Laravel Sanctum (session-based, multi-role) |
| SPA Bridge | Inertia.js v2 |
| UI Library | React 18 |
| CSS Framework | Bootstrap 5 |
| State Management | Zustand (cart) |
| Build Tool | Vite |
| Web Server | Nginx |
| Payment | Midtrans API (QRIS, E-Wallet, Transfer) |

> **Data Mining (FastAPI + Colab):** Di-skip untuk fase ini.

---

## Arsitektur Inertia.js — Aturan Wajib

```
Browser (React 18) ←→ Inertia.js ←→ Laravel Controller → PostgreSQL
                                                         ↘ IndexedDB (offline cart)
```

- Controller **selalu** return `Inertia::render('Page', $data)` — TIDAK ada JSON endpoint
- Navigasi: `<Link href={route('name')}>` — BUKAN `<a href>`
- Form submit: `useForm()` dari `@inertiajs/react` — BUKAN fetch/axios manual
- Partial reload: `router.reload({ only: ['orders'] })`
- Validasi error: `usePage().props.errors` — otomatis dari Laravel

---

## Design System

### Palet Warna

```css
/* Kasir (Desktop) */
--navy-sidebar:   #1A2332   /* sidebar background */
--navy-logo:      #0F1621   /* logo area sidebar, lebih gelap */
--blue-active:    #3B6FD4   /* active nav item, tombol utama kasir */
--blue-text:      #3B6FD4   /* harga menu, total, link */

/* Pelanggan (Mobile) */
--orange-primary: #E8692A   /* tombol utama, chip aktif, harga, accent */
--orange-light:   #FFF0E8   /* background info box mahasiswa */
--orange-badge:   #E8692A   /* badge "Diproses" */

/* Umum */
--white:          #FFFFFF
--gray-bg:        #F8F9FA   /* background halaman, input read-only */
--gray-border:    #E9ECEF   /* border card, divider, tabel */
--gray-text:      #6C757D   /* teks sekunder, placeholder, label kecil */
--text-dark:      #1A1A2E   /* teks utama */
--green-success:  #28A745   /* ● Selesai, ● Disetujui */
--yellow-pending: #FFC107   /* ● Pending, ● Menunggu */
--blue-dibayar:   #17A2B8   /* ● Dibayar */
--red-danger:     #DC3545   /* ● Ditolak, tombol Keluar dari Akun */
```

### Typography

```
Font: Inter / system-ui / sans-serif
Heading halaman:  24px, font-weight 700
Sub-heading:      18px, font-weight 600
Body:             14px, font-weight 400
Label kecil:      12px, font-weight 400, color #6C757D
Harga kasir:      14px, color #3B6FD4
Harga pelanggan:  14px, color #E8692A, font-weight 600
```

### Border Radius

```
Card konten:    12px
Card kasir:     12px
Tombol kasir:   8px
Tombol pelanggan: 50px (pill/capsule)
Input:          8px
Category chip kasir:   50px
Category chip pelanggan: 50px
Badge status:   50px
```

### Helper Functions (gunakan di semua komponen)

```js
// Format Rupiah: "Rp 45.000"
export const formatRupiah = (amount) =>
  new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(amount);

// Format Tanggal: "22 Feb 2026"
export const formatDate = (date) =>
  new Intl.DateTimeFormat('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  }).format(new Date(date));

// Format Waktu: "10:25"
export const formatTime = (date) =>
  new Intl.DateTimeFormat('id-ID', {
    hour: '2-digit', minute: '2-digit'
  }).format(new Date(date));

// Ringkas item order: "2x Kopi Robusta, 1x Roti Bakar"
export const summarizeItems = (items) =>
  items.map(i => `${i.quantity}x ${i.menu.name}`).join(', ');
```

---

## Desain Halaman KASIR (Desktop)

### Layout Global Kasir

```
┌──────────────────────────────────────────────────────────────────┐
│  SIDEBAR (210px fixed)        │  KONTEN UTAMA (flex-grow)        │
│  background: #1A2332          │  background: #F8F9FA             │
│                               │  padding: 24px                   │
│  ┌─────────────────────────┐  │  ┌──────────────────────────┐    │
│  │ [w9] W9 Cafe            │  │  │ white card               │    │
│  │ (logo area, #0F1621)    │  │  │ border-radius: 12px      │    │
│  ├─────────────────────────┤  │  │ padding: 24px            │    │
│  │ • Dashboard             │  │  │                          │    │
│  │ • Pesanan Baru          │  │  │ [konten halaman]         │    │
│  │ • Pesanan Aktif         │  │  │                          │    │
│  │ • Riwayat Pesanan       │  │  └──────────────────────────┘    │
│  │ • Verifikasi Akun       │  │                                  │
│  │ • Profil                │  │                                  │
│  ├─────────────────────────┤  │                                  │
│  │ [→ Keluar] (merah)      │  │                                  │
│  └─────────────────────────┘  │                                  │
└──────────────────────────────────────────────────────────────────┘
```

**Sidebar detail:**
- Background: `#1A2332`
- Logo area background: `#0F1621` (lebih gelap), padding 20px 16px, border-bottom `#2A3441`
- Logo: kotak rounded 32px bg `#2A3441` + teks "w9" putih bold 12px, diikuti "W9 Cafe" putih 15px semibold
- Nav item default: color `#9AA3AF`, padding 10px 12px, border-radius 8px, font 14px
- Nav item **active**: background `#3B6FD4`, color white
- Nav item hover: background `rgba(255,255,255,0.05)`
- Icon: 18px dari lucide-react, margin-right 10px
- Tombol Keluar: color `#E85454`, icon LogOut, di bagian paling bawah sidebar, border-top `#2A3441`

---

### K1 — Login Kasir

**Referensi desain:** Image 7

**Layout:** Split screen 50/50, full viewport height
```
┌──────────────────────┬───────────────────────────────┐
│   KIRI (#1A2332)     │   KANAN (#FFFFFF)              │
│   50vw               │   50vw                         │
│                      │                                │
│   [logo W9]          │   Masuk ke Akun Anda          │
│   W9 Cafe            │   subtitle gray                │
│   Sistem Point of Sale│                               │
│                      │   Email [_______________]      │
│                      │   Kata Sandi [___________]     │
│                      │   [Tombol Masuk - biru]        │
│                      │   [Error box jika gagal]       │
└──────────────────────┴───────────────────────────────┘
```

**Detail kiri:**
- Background `#1A2332`, flex center
- Logo: kotak rounded 16px (80px × 80px), bg `#2A3441`, icon "w9" script putih
- "W9 Cafe" — putih, 28px bold, margin-top 16px
- "Sistem Point of Sale" — `#9AA3AF`, 14px

**Detail kanan:**
- Background `#FFFFFF`, flex center
- Form max-width 380px, centered
- Heading "Masuk ke Akun Anda" — 24px bold, `#1A1A2E`
- Subtitle — 14px `#6C757D`, margin-bottom 28px
- Label "Email" — 13px semibold, margin-bottom 6px
- Input Email: icon amplop kiri (bi-envelope dari Bootstrap icons / lucide Mail), border 1px `#E9ECEF`, border-radius 8px, padding 11px 12px 11px 40px, font 14px
- Label "Kata Sandi" — sama
- Input Password: icon gembok kiri (lucide Lock), style sama
- Tombol "Masuk": full-width, height 48px, background `#3B6FD4`, border-radius 8px, font-weight 600, margin-top 20px
- **Error state**: div background `#FEF2F2`, border `#FCA5A5`, border-radius 8px, padding 12px, icon ⊗ merah + teks "Email atau kata sandi salah"

**File:** `resources/js/Pages/Auth/Login.jsx`

---

### K2 — Dashboard Kasir

**Referensi desain:** Image 8

**Struktur dalam white card:**

**A. Header row (space-between):**
- Kiri: "Dashboard" (24px bold) di atas "Selamat datang, Kasir! Berikut ringkasan hari ini." (14px gray)
- Kanan: icon kalender + tanggal waktu "22 Feb 2026, 10:30" (14px gray)

**B. Stat Bar (3 kolom dengan divider vertikal):**
```
┌──────────────────┬────────────────┬──────────────┐
│  Rp 2.450.000    │      48        │      5       │
│  (28px bold)     │  (28px bold)   │  (28px bold) │
│  Total Penjualan │  Jumlah        │  Pesanan     │
│  Hari Ini        │  Transaksi     │  Aktif       │
│  (12px gray)     │  (12px gray)   │  (12px gray) │
└──────────────────┴────────────────┴──────────────┘
```
- Container: border 1px `#E9ECEF`, border-radius 8px, padding 20px 24px per kolom
- Divider: border-right 1px `#E9ECEF` di col 1 dan 2

**C. Quick Action Row:**
- "+ Pesanan Baru" — background `#3B6FD4`, putih, border-radius 8px, padding 8px 20px, icon +
- "📋 Lihat Pesanan" — border 1px `#E9ECEF`, bg putih, border-radius 8px
- "🕐 Riwayat" — border 1px `#E9ECEF`, bg putih, border-radius 8px
- Semua tombol: font 14px, height 38px

**D. Tabel Transaksi Terbaru:**
- Header: "Transaksi Terbaru" (16px semibold) + "Lihat Semua →" (link biru `#3B6FD4`, kanan)
- Tabel Bootstrap striped-rows, margin-top 12px
- Header kolom: bg `#F8F9FA`, font 12px uppercase gray, padding 10px 16px
- Kolom: **ID Pesanan** | Item | Total | Pembayaran | Status
- ID Pesanan: font-weight 700, "#ORD-048"
- Item: 14px normal, text-truncate max 400px
- Total: font-weight 600
- Pembayaran: teks (QRIS / Tunai / Kartu)
- Status: `<StatusBadge>` component
- Row hover: bg `#F8F9FA`
- Border bawah tiap row: 1px `#E9ECEF`

**File:** `resources/js/Pages/Cashier/Dashboard.jsx`

---

### K3 — Pesanan Baru (POS Interface)

**Referensi desain:** Image 9

**Layout 3-panel (tanpa inner white card — langsung panels):**
```
┌──────────┬────────────────────────────────────────┬───────────────────┐
│ Sidebar  │  PANEL TENGAH                          │  PANEL KANAN      │
│          │  bg: #F8F9FA, padding: 20px            │  bg: #FFFFFF      │
│          │                                        │  width: 280px     │
│          │  [Search bar full-width]               │  border-left:     │
│          │  [Category chips scroll horizontal]    │   1px #E9ECEF     │
│          │                                        │                   │
│          │  [Grid menu 4 kolom]                   │  Keranjang ●3     │
│          │   KOPI  KOPI  KOPI  KOPI               │                   │
│          │   Nama  Nama  Nama  Nama               │  [list items]     │
│          │   Rp X  Rp X  Rp X  Rp X              │                   │
│          │                                        │  ─────────────    │
│          │                                        │  Subtotal  Rp X   │
│          │                                        │  Total     Rp X   │
│          │                                        │  [BAYAR Rp X]     │
└──────────┴────────────────────────────────────────┴───────────────────┘
```

**Panel Tengah — Menu:**
- Search: full-width, border 1px `#E9ECEF`, border-radius 8px, padding 10px 16px, placeholder "Cari menu..."
- Category chips (horizontal scroll, margin-bottom 16px):
  - Active: bg `#3B6FD4`, color white, border `#3B6FD4`
  - Inactive: bg white, color `#6C757D`, border `#E9ECEF`
  - Border-radius 50px, padding 6px 16px, font 13px
- Menu grid — 4 kolom, gap 12px:
  - Card: bg white, border 1px `#E9ECEF`, border-radius 8px, padding 16px, cursor pointer
  - Label kategori: 11px uppercase, color `#6C757D`, letter-spacing 0.5px
  - Nama menu: 14px semibold, margin-top 4px
  - Harga: 14px, color `#3B6FD4`
  - Hover: box-shadow `0 2px 8px rgba(0,0,0,0.08)`
  - Klik card → tambah ke keranjang

**Panel Kanan — Keranjang:**
- Header: "Keranjang Pesanan" (16px semibold) + badge count (lingkaran biru `#3B6FD4`, 24px, warna putih)
- List items (scrollable, flex-grow):
  - Per item: nama (14px bold) + harga satuan (12px gray) di kiri | [-] qty [+] + subtotal di kanan
  - Tombol - dan +: 24px × 24px, bg `#3B6FD4`, border-radius 4px, warna putih
  - Qty: 14px bold, min-width 24px, text-center
  - Subtotal: 14px bold
- Footer (mt-auto):
  - Divider 1px `#E9ECEF`
  - "Subtotal" + nilai (gray, 14px)
  - "Total" bold + nilai bold (14px)
  - Tombol BAYAR: full-width, bg `#3B6FD4`, color white, border-radius 8px, height 48px, font 15px 600
    - Teks: "BAYAR {formatRupiah(total)}"
    - Disabled & opacity 0.5 jika cart kosong

**File:** `resources/js/Pages/Cashier/PesananBaru.jsx`

---

### K4 — Pesanan Aktif

**Referensi desain:** Image 6

**Struktur dalam white card:**

**A. Header:**
- "Pesanan Aktif" (24px bold)
- "Kelola semua pesanan yang sedang diproses" (14px gray)

**B. Filter Tabs (pill):**
```
[Semua (5)]  [Pending (2)]  [Dibayar (2)]  [Selesai (1)]
```
- Active: bg `#3B6FD4`, color white, border-radius 50px
- Inactive: bg `#F0F0F0`, color `#6C757D`, border-radius 50px
- Padding: 6px 16px, font 13px

**C. Grid Order Cards — 3 kolom (responsive: xl=3, md=2, sm=1):**

Per card:
```
┌──────────────────────────────────┐
│ #ORD-048              ● Status   │  ← order_code bold + StatusBadge kanan
│ 10:25 - 22 Feb 2026              │  ← gray 12px
│ 2x Kopi Robusta, 1x Roti Bakar   │  ← items summary 14px, truncate
│                                  │
│ Rp 45.000          [Detail]      │  ← harga bold kiri, tombol kanan
└──────────────────────────────────┘
```
- Card: bg white, border 1px `#E9ECEF`, border-radius 12px, padding 16px, margin-bottom 12px
- Tombol "Detail": border 1px `#D1D5DB`, bg white, color `#374151`, border-radius 6px, padding 5px 14px, font 13px

**File:** `resources/js/Pages/Cashier/PesananAktif.jsx`

---

### K5 — Riwayat Pesanan

**Referensi desain:** Image 5

**Struktur dalam white card:**

**A. Header:**
- "Riwayat Pesanan" (24px bold)
- "Lihat semua transaksi yang telah selesai" (14px gray)

**B. Filter Bar (1 row, gap 12px):**
- Search input (flex-grow): icon kaca pembesar + "Cari transaksi...", border-radius 8px, border `#E9ECEF`
- Date input: icon kalender + "22 Feb 2026", border-radius 8px, border `#E9ECEF`, width 160px
- Dropdown "Semua Metode ▾": border-radius 8px, border `#E9ECEF`, width 160px

**C. Tabel:**
- Header row: bg `#F8F9FA`, font 12px gray, padding 10px 16px
- Kolom: **ID Pesanan** | Tanggal | Waktu | Total | Pembayaran | Kasir | Status | Aksi
- ID: bold
- Total: bold
- Status: `<StatusBadge>` (semuanya Selesai = hijau)
- Aksi: `<Link>` teks "Detail", color `#3B6FD4`, no underline
- Row: border-bottom 1px `#E9ECEF`
- Row hover: bg `#F8F9FA`

**File:** `resources/js/Pages/Cashier/RiwayatPesanan.jsx`

---

### K6 — Detail Pesanan

**Referensi desain:** Image 4

**Struktur dalam white card:**

**A. Header:**
- Back arrow `←` (Link ke halaman sebelumnya) + "Detail Pesanan #ORD-048" (22px bold) — dalam 1 row
- Subtitle: "22 Februari 2026, 10:25 WIB" (14px gray)
- Status badge kanan: `● Selesai` (hijau)

**B. Layout 2 kolom (col-8 + col-4):**

**Kiri — "Daftar Item Pesanan" (card border 1px `#E9ECEF`, border-radius 12px, padding 20px):**
- Header "Daftar Item Pesanan" (16px semibold), margin-bottom 16px
- Tabel:
  - Header: Nama Item | Harga | Jumlah | Subtotal (12px gray)
  - Rows: border-bottom 1px `#E9ECEF`, padding 12px 0
  - Nama item: 14px normal
  - Harga, Jumlah: 14px
  - Subtotal: 14px
- Footer row (bold): "Total Pembayaran" | "Rp 47.000" (18px bold, color `#3B6FD4`)

**Kanan — "Informasi Pesanan" (card border 1px `#E9ECEF`, border-radius 12px, padding 20px):**
- Header "Informasi Pesanan" (16px semibold), margin-bottom 16px
- Rows info (space-between, border-bottom `#F3F4F6`, padding 10px 0):
  - ID Pesanan: label gray | nilai bold (#ORD-048)
  - Tanggal: label gray | nilai normal
  - Waktu: label gray | nilai normal
  - Metode Pembayaran: label gray | nilai bold (QRIS (Midtrans))
  - Kasir: label gray | nilai normal
  - Status: label gray | `<StatusBadge>`

**File:** `resources/js/Pages/Cashier/Order/Show.jsx`

---

### K7 — Verifikasi Akun Mahasiswa

**Referensi desain:** Image 2

**Struktur dalam white card:**

**A. Header row (space-between):**
- Kiri:
  - "Verifikasi Akun Mahasiswa" (24px bold)
  - "Kelola dan verifikasi pendaftaran akun pelanggan mahasiswa" (14px gray)
- Kanan:
  - "🕐 5 Menunggu" — icon kuning + teks orange, font 14px semibold
  - "✓ 12 Disetujui" — icon hijau + teks hijau, font 14px semibold

**B. Filter Bar:**
- Search input (kiri): "Cari nama atau NIM...", width 300px, border-radius 8px
- Tab pills (kanan): Semua | Menunggu | Disetujui | Ditolak
  - Active "Semua": bg `#3B6FD4`, white
  - Inactive: bg `#F0F0F0`, gray

**C. Tabel:**
- Header: No | Nama | NIM | Tgl Daftar | Status | Aksi (12px gray bg `#F8F9FA`)
- Row data: border-bottom 1px `#E9ECEF`
- No: gray 14px
- Nama: bold 14px
- NIM: monospace 14px
- Tgl Daftar: 14px gray
- Status badges:
  - `● Menunggu` — dot `#FFC107`, text `#FFC107`
  - `● Disetujui` — dot `#28A745`, text `#28A745`
  - `● Ditolak` — dot `#DC3545`, text `#DC3545`
- **Aksi jika Menunggu:** tombol teks "Setujui" (color `#28A745`, no bg, no border) + "Tolak" (color `#DC3545`) — gap 8px
- **Aksi lainnya:** link teks "Detail" (color `#3B6FD4`)

**File:** `resources/js/Pages/Cashier/VerifikasiAkun.jsx`

---

### K8 — Profil Kasir

**Referensi desain:** Image 3

**Judul halaman:** "Profil Saya" (24px bold) + "Kelola informasi akun Anda" (gray)

**Layout 2 kolom (col-4 + col-8):**

**Kiri — Card Profil (bg white, border 1px `#E9ECEF`, border-radius 12px, padding 24px, text-center):**
- Avatar: lingkaran 80px, bg `#3B6FD4`, icon User putih 40px (lucide-react)
- Nama kasir: 18px bold, margin-top 12px
- Badge role: border 1px `#D1D5DB`, bg white, color `#6C757D`, border-radius 50px, padding 3px 12px, font 13px — teks "Kasir"
- Tombol "Keluar dari Akun":
  - Full-width, bg `#DC3545`, color white, border-radius 8px, height 44px, margin-top 20px
  - Icon LogOut kiri (lucide-react), teks "→ Keluar dari Akun"

**Kanan — "Informasi Akun" (bg white, border 1px `#E9ECEF`, border-radius 12px, padding 24px):**
- Header "Informasi Akun" (16px semibold), margin-bottom 20px
- 4 field groups (masing-masing label di atas, input di bawah):
  - Label: 13px semibold, color `#374151`, margin-bottom 6px
  - Input: bg `#F8F9FA`, border 1px `#E9ECEF`, border-radius 8px, padding 11px 16px, font 14px, disabled/read-only
  - Fields: Nama Lengkap | Email | Peran / Role | Terdaftar Sejak

**File:** `resources/js/Pages/Cashier/Profil.jsx`

---

## Desain Halaman PELANGGAN (Mobile PWA)

**Target device:** Portrait mobile, max-width 430px, centered di desktop dengan bg `#F5F5F5`

### Layout Global Pelanggan

```
┌──────────────────────────┐
│  KONTEN (flex-grow)      │  bg: #FAFAFA
│  padding-bottom: 72px    │  (ruang untuk bottom nav)
│                          │
│  [halaman aktif]         │
│                          │
├──────────────────────────┤
│  BOTTOM NAV (fixed)      │  bg: white, shadow atas, height 60px
│  🍽 Menu                 │
│  🛒 Keranjang            │
│  📋 Riwayat              │
│  👤 Akun                 │
└──────────────────────────┘
```

**Bottom Nav detail:**
- Height: 60px, fixed bottom, width 100% (max 430px), bg white
- Shadow: `0 -2px 8px rgba(0,0,0,0.08)`
- 4 tab equal width
- Active: icon + label color `#E8692A`
- Inactive: icon + label color `#9AA3AF`
- Label: 11px, margin-top 4px

---

### C1 — Menu Pelanggan

**Referensi desain:** Image 1 (kolom paling kiri)

**Background halaman:** `#FAFAFA`

**Header area (bg white, padding 16px, shadow-sm):**
- Row: Avatar lingkaran 40px (bg `#F0F0F0`, icon user abu) + teks kanan:
  - "Hello Guest" (12px gray)
  - "selamat Datang" (20px bold, `#1A1A2E`)
- Search bar (margin-top 12px): full-width, border-radius 50px, border 1px `#E9ECEF`, bg white, padding 10px 16px, icon kaca pembesar abu kiri, placeholder "Cari kopi, teh, snack..."

**Section Kategori (padding 16px, bg white, margin-top 8px):**
- Row header: "Kategori" (14px bold) + "Lihat Semua" (13px, color `#E8692A`) — space-between
- Chip row (horizontal scroll, gap 8px, margin-top 10px):
  - Active chip: bg `#E8692A`, color white, border-radius 50px, padding 7px 18px, font 13px semibold
  - Inactive chip: bg white, border 1px `#E9ECEF`, color `#6C757D`, border-radius 50px, padding 7px 18px, font 13px
  - Items: Kopi | Teh | Coklat | Snack (sesuai desain)

**Section Menu Populer (padding 16px, bg white, margin-top 8px):**
- "Menu Populer" (14px bold), margin-bottom 12px
- Grid 2 kolom, gap 12px:

Per card:
```
┌──────────────────────┐
│  [placeholder image] │  bg #F0F0F0, aspect-ratio 4:3, border-radius 8px top
│                      │
│  Kopi Robusta        │  14px semibold, padding 10px 12px 0
│  Rp 12.000           │  14px, color #E8692A, font-weight 600
│  [+ Tambah]          │  full-width, bg #E8692A, color white,
│                      │  border-radius 50px, padding 8px, font 13px
└──────────────────────┘
```
- Card: bg white, border-radius 12px, border 1px `#E9ECEF`, overflow hidden

**File:** `resources/js/Pages/Customer/Menu/Index.jsx`

---

### C2 — Keranjang Pelanggan

**Referensi desain:** Image 1 (kolom kedua)

**Background:** `#FAFAFA`

**Header (bg white, padding 16px, text-center, border-bottom 1px `#E9ECEF`):**
- "Keranjang" (18px bold, center)
- Sub-info: "3 item" (gray 13px) + "Rp 64.000" (color `#E8692A`, 13px semibold) — inline, gap 8px

**List items (padding 0 16px):**
Per item (bg white, border-bottom 1px `#F3F4F6`, padding 16px 0):
```
Nama item (14px bold)                    [−]  2  [+]
Rp 12.000 (gray 12px)
```
- Tombol `−` dan `+`: lingkaran 32px, bg `#E8692A`, color white, font 18px bold
- Quantity: 16px bold, min-width 32px, text-center
- Layout: space-between, align-center

**Footer Summary (fixed/sticky bottom di atas bottom nav, bg white, padding 16px, border-top 1px `#E9ECEF`):**
- Subtotal row: "Subtotal" (gray 14px) + "Rp 64.000" (14px, kanan)
- Diskon row: "Diskon" (color `#E8692A`, 14px) + "- Rp 0" (color `#E8692A`, 14px, kanan)
- Divider 1px `#E9ECEF`
- Total row: "Total" (16px bold) + "Rp 64.000" (16px bold color `#E8692A`, kanan)
- Tombol "🎴 Bayar Sekarang": full-width, bg `#E8692A`, color white, border-radius 50px, height 52px, font 15px semibold, margin-top 12px, icon kartu kiri

**File:** `resources/js/Pages/Customer/Cart/Index.jsx`

---

### C3 — Riwayat Pesanan Pelanggan

**Referensi desain:** Image 1 (kolom ketiga)

**Background:** `#FAFAFA`

**Header (bg white, padding 16px, text-center):**
- "Riwayat Pesanan" (18px bold, center)

**Filter tabs (3 tab, padding 16px, bg white, margin-bottom 8px):**
- Container: bg `#F0F0F0`, border-radius 50px, padding 4px, display flex
- Tab: flex 1, text-center, border-radius 50px, font 13px
- Active: bg white, box-shadow `0 1px 4px rgba(0,0,0,0.1)`, color `#1A1A2E`
- Inactive: color `#6C757D`
- Tabs: Semua | Diproses | Selesai

**List cards (padding 12px 16px):**

Per card (bg white, border-radius 12px, padding 16px, margin-bottom 10px, box-shadow `0 1px 4px rgba(0,0,0,0.06)`):
```
┌──────────────────────────────────────┐
│ Pesanan 1                 Diproses   │  ← nama pesanan bold + badge kanan
│ 24 Feb - 25 Feb 2026                 │  ← tanggal, gray 12px
│ 2x Kopi Robusta, 1x Kopi Latte       │  ← items summary, gray 13px
│                                      │
│ Rp 44.000               [Detail]     │  ← harga bold + tombol kanan
└──────────────────────────────────────┘
```
- Badge "Diproses": bg `#E8692A`, color white, border-radius 50px, padding 3px 10px, font 12px
- Badge "Selesai": bg `#E8F5E9`, color `#28A745`, border-radius 50px
- Tombol "Detail": bg `#E8692A`, color white, border-radius 8px, padding 6px 16px, font 13px

**File:** `resources/js/Pages/Customer/Riwayat/Index.jsx`

---

### C4 — Login Pelanggan (Mahasiswa)

**Referensi desain:** Image 1 (kolom paling kanan)

**Background:** `#FAFAFA`, padding 24px, centered

**Logo area (text-center, margin-bottom 24px):**
- Kotak rounded 16px (80px × 80px), bg `#1A2332`, teks script "w9" putih — atau gunakan gambar logo
- "W9 Cafe" — 22px bold, margin-top 12px
- "Pemesanan Online" — 14px gray

**Info box mahasiswa (bg `#FFF0E8`, border-radius 12px, padding 14px 16px, margin-bottom 20px):**
- "Login sebagai Mahasiswa" — 14px semibold, color `#E8692A`
- "Dapatkan diskon 10% untuk semua menu!" — 12px gray

**Form:**
- Label "Username (Nama Lengkap)" — 13px semibold, margin-bottom 6px
- Input: icon User kiri, bg white, border 1px `#E9ECEF`, border-radius 12px, padding 12px 16px 12px 44px, placeholder "Masukkan nama lengkap..."
- Label "Password (NIM)" — 13px semibold, margin-top 14px
- Input: icon Lock kiri, style sama, placeholder "Masukkan NIM..."
- Tombol "→ Masuk": full-width, bg `#E8692A`, color white, border-radius 8px, height 50px, font 16px semibold, margin-top 20px

**Cara Login info (margin-top 20px):**
```
Cara Login:
• Username: Gunakan nama lengkap Anda
• Password: Gunakan NIM Anda
• Tunjukkan KTM pada Kasir untuk memverifikasi akun.
```
- Teks 12px gray
- Baris terakhir: color `#E8692A`

**File:** `resources/js/Pages/Customer/Auth/Login.jsx`

---

## Struktur Direktori Lengkap

```
pos-cafe/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   └── AuthController.php
│   │   │   ├── Customer/
│   │   │   │   ├── CustomerMenuController.php
│   │   │   │   ├── CustomerOrderController.php
│   │   │   │   ├── CustomerPaymentController.php
│   │   │   │   └── CustomerAuthController.php
│   │   │   ├── Cashier/
│   │   │   │   ├── CashierDashboardController.php
│   │   │   │   ├── CashierPesananBaruController.php
│   │   │   │   ├── CashierPesananAktifController.php
│   │   │   │   ├── CashierRiwayatController.php
│   │   │   │   ├── CashierOrderController.php
│   │   │   │   └── CashierVerifikasiController.php
│   │   │   └── Api/
│   │   │       └── MidtransWebhookController.php
│   │   ├── Middleware/
│   │   │   └── RoleMiddleware.php
│   │   └── Requests/
│   │       ├── StoreOrderRequest.php
│   │       ├── UpdateOrderStatusRequest.php
│   │       └── CustomerLoginRequest.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   ├── Menu.php
│   │   ├── CafeTable.php          ← protected $table = 'cafe_tables'
│   │   ├── Order.php
│   │   ├── OrderItem.php
│   │   └── Payment.php
│   └── Services/
│       └── MidtransService.php
│
├── resources/
│   ├── css/
│   │   └── app.css                ← CSS variables global
│   └── js/
│       ├── app.jsx                ← Inertia entry point
│       ├── helpers.js             ← formatRupiah, formatDate, formatTime, summarizeItems
│       ├── Pages/
│       │   ├── Auth/
│       │   │   └── Login.jsx                    ← K1: login kasir (split screen)
│       │   ├── Customer/
│       │   │   ├── Auth/
│       │   │   │   └── Login.jsx                ← C4: login mahasiswa
│       │   │   ├── Menu/
│       │   │   │   └── Index.jsx                ← C1: menu pelanggan
│       │   │   ├── Cart/
│       │   │   │   └── Index.jsx                ← C2: keranjang
│       │   │   └── Riwayat/
│       │   │       └── Index.jsx                ← C3: riwayat pesanan
│       │   └── Cashier/
│       │       ├── Dashboard.jsx                ← K2: dashboard
│       │       ├── PesananBaru.jsx              ← K3: POS interface
│       │       ├── PesananAktif.jsx             ← K4: pesanan aktif
│       │       ├── RiwayatPesanan.jsx           ← K5: riwayat
│       │       ├── VerifikasiAkun.jsx           ← K7: verifikasi mahasiswa
│       │       ├── Profil.jsx                   ← K8: profil kasir
│       │       └── Order/
│       │           └── Show.jsx                 ← K6: detail pesanan
│       ├── Components/
│       │   ├── Common/
│       │   │   ├── StatusBadge.jsx              ← dot + teks per status
│       │   │   └── LoadingSpinner.jsx
│       │   ├── Customer/
│       │   │   ├── BottomNav.jsx                ← 4-tab fixed bottom nav
│       │   │   ├── CategoryChip.jsx             ← pill chip kategori
│       │   │   ├── MenuCard.jsx                 ← card menu 2-col
│       │   │   ├── CartItem.jsx                 ← row item keranjang
│       │   │   └── RiwayatCard.jsx              ← card riwayat pesanan
│       │   └── Cashier/
│       │       ├── StatBar.jsx                  ← 3 stat di dashboard
│       │       ├── MenuGridItem.jsx             ← item di POS grid
│       │       ├── KeranjangItem.jsx            ← item di panel keranjang POS
│       │       └── OrderCard.jsx                ← card di pesanan aktif
│       ├── Layouts/
│       │   ├── CashierLayout.jsx                ← sidebar navy + white card content
│       │   └── CustomerLayout.jsx               ← mobile wrapper + bottom nav
│       ├── Hooks/
│       │   ├── useCart.js                       ← cart logic + IndexedDB sync
│       │   └── useOrderPolling.js               ← polling tiap 5-10 detik
│       └── Store/
│           └── cartStore.js                     ← Zustand cart store
│
├── resources/views/
│   └── app.blade.php              ← satu-satunya Blade file
│
├── routes/
│   ├── web.php
│   └── api.php
│
├── public/
│   ├── manifest.json
│   └── sw.js
│
└── config/
    └── midtrans.php
```

---

## Database Schema (Fase Transaksi)

**users** *(modifikasi default Laravel)*
```sql
id, name, email, password,
role                 ENUM('customer','cashier','admin') DEFAULT 'customer',
nim                  VARCHAR(20) NULL,
phone                VARCHAR(20) NULL,
is_student_verified  BOOLEAN DEFAULT false,
remember_token, timestamps
```

**categories** — `id, name(100), slug UNIQUE, is_active BOOL DEFAULT true, timestamps`

**menus**
```sql
id, category_id FK→categories CASCADE,
name, slug UNIQUE, description TEXT NULL,
price DECIMAL(10,2), image VARCHAR NULL,
is_available BOOL DEFAULT true,
is_student_discount BOOL DEFAULT false,
student_price DECIMAL(10,2) NULL,
timestamps
```

**cafe_tables**
```sql
id, table_number INT UNIQUE,
qr_code VARCHAR(500) UNIQUE,
is_available BOOL DEFAULT true, timestamps
```

**orders**
```sql
id, order_code VARCHAR(50) UNIQUE,
table_id     FK→cafe_tables NULL RESTRICT,
customer_id  FK→users NULL SET NULL,
cashier_id   FK→users NULL SET NULL,
status       ENUM('pending','confirmed','preparing','ready','completed','cancelled') DEFAULT 'pending',
order_type   ENUM('qr','cashier') DEFAULT 'qr',
payment_status ENUM('unpaid','paid') DEFAULT 'unpaid',
total_amount DECIMAL(15,2) DEFAULT 0,
notes TEXT NULL, timestamps
INDEX: (status), (created_at)
```

**order_items** — `id, order_id FK CASCADE, menu_id FK RESTRICT, quantity INT, unit_price DECIMAL(15,2), subtotal DECIMAL(15,2), notes VARCHAR NULL, timestamps`

**payments**
```sql
id, order_id FK CASCADE,
payment_method  ENUM('qris','ewallet','cash','transfer'),
payment_gateway ENUM('midtrans','manual'),
transaction_id  VARCHAR NULL UNIQUE,
amount DECIMAL(15,2),
status ENUM('pending','success','failed') DEFAULT 'pending',
paid_at TIMESTAMP NULL, timestamps
```

---

## Konvensi Koding

### PHP / Laravel
- Controller: selalu `return Inertia::render('Path/Page', compact(...))`
- TIDAK ada `return response()->json()` kecuali di `Api/` folder
- Eager load eksplisit: `->with(['items.menu', 'cafeTable', 'payment'])`
- Form Request untuk semua validasi
- `CafeTable` model: wajib `protected $table = 'cafe_tables'`

### React / Inertia
- Props Inertia: destructure dari parameter `function Page({ categories, table })`
- `useState` hanya untuk UI state (tab aktif, modal open)
- `useForm()` dari `@inertiajs/react` untuk semua form submit
- Import helper dari `@/helpers.js` untuk format angka & tanggal
- Gunakan `lucide-react` untuk semua icons

### Component StatusBadge
```jsx
// resources/js/Components/Common/StatusBadge.jsx
const statusMap = {
  pending:    { dot: '#FFC107', text: '#FFC107',  label: 'Pending' },
  confirmed:  { dot: '#3B6FD4', text: '#3B6FD4',  label: 'Dikonfirmasi' },
  preparing:  { dot: '#17A2B8', text: '#17A2B8',  label: 'Diproses' },
  ready:      { dot: '#28A745', text: '#28A745',  label: 'Siap' },
  completed:  { dot: '#28A745', text: '#28A745',  label: 'Selesai' },
  cancelled:  { dot: '#DC3545', text: '#DC3545',  label: 'Dibatalkan' },
  menunggu:   { dot: '#FFC107', text: '#FFC107',  label: 'Menunggu' },
  disetujui:  { dot: '#28A745', text: '#28A745',  label: 'Disetujui' },
  ditolak:    { dot: '#DC3545', text: '#DC3545',  label: 'Ditolak' },
  dibayar:    { dot: '#17A2B8', text: '#17A2B8',  label: 'Dibayar' },
};
export default function StatusBadge({ status }) {
  const s = statusMap[status] || { dot: '#6C757D', text: '#6C757D', label: status };
  return (
    <span style={{ color: s.text, fontSize: 13, fontWeight: 500 }}>
      <span style={{ color: s.dot, marginRight: 4 }}>●</span>{s.label}
    </span>
  );
}
```

---

## Environment Variables

```env
APP_NAME="W9 Cafe POS"
APP_ENV=local
APP_KEY=
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_cafe
DB_USERNAME=postgres
DB_PASSWORD=

MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/snap.js

VITE_MIDTRANS_CLIENT_KEY="${MIDTRANS_CLIENT_KEY}"
VITE_MIDTRANS_SNAP_URL="${MIDTRANS_SNAP_URL}"
VITE_APP_URL="${APP_URL}"
```

---

## Catatan Penting

1. **Kasir**: desktop only, sidebar navy `#1A2332`, konten dalam white card `border-radius 12px`
2. **Pelanggan**: mobile-first, max-width 430px, bottom nav fixed, warna orange `#E8692A`
3. **Login kasir**: email + password → tombol biru `#3B6FD4`
4. **Login pelanggan**: nama lengkap (username) + NIM (password) → tombol orange, khusus mahasiswa dengan diskon 10%
5. **Verifikasi mahasiswa**: kasir approve sebelum diskon aktif di `is_student_verified`
6. **POS K3**: kasir pilih menu dari grid → keranjang panel kanan → modal bayar → selesai
7. **Cart pelanggan**: Zustand (runtime) + IndexedDB (offline persistence), sync via `useCart.js`
8. **Polling**: halaman status & kanban kasir → `router.reload({ only: ['...'] })` tiap 5-10 detik
9. **formatRupiah**: gunakan dari `@/helpers.js` — TIDAK boleh hardcode format angka manual
10. **QR Code**: statis per meja, format `{APP_URL}/order?table={id}`

11. Untuk UI dapat dilihata dari "C:\Users\sitor\OneDrive\Documents\capstone.pen"