# Spesifikasi Kebutuhan Perangkat Lunak (SRS)

**Proyek:** Sistem POS Cerdas untuk Kafe  
**Versi Dokumen:** 1.0  
**Tanggal:** 29 Oktober 2025  

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
   Aplikasi PWA (Progressive Web App) untuk tablet/PC yang dapat beroperasi penuh secara offline dengan IndexedDB dan melakukan sinkronisasi otomatis saat online.

3. **Portal Pelanggan**  
   Antarmuka web interaktif berbasis Livewire yang memungkinkan pelanggan melakukan *self-order* dan pembayaran online dengan QR code.

### 1.3 Target Pengguna (Aktor)

- **Admin:** Pemilik atau manajer kafe. Mengelola sistem, stok, dan laporan analitik.  
- **Kasir/Staf:** Karyawan yang mengoperasikan POS, termasuk pemrosesan pesanan dan transaksi.  
- **Pelanggan:** Pengunjung umum kafe yang melakukan pemesanan mandiri.  
- **Pelanggan (Mahasiswa):** Pelanggan dengan akun khusus mahasiswa yang mendapat harga diskon.

---

## 2. Deskripsi Umum dan Arsitektur

### 2.1 Arsitektur Sistem

Sistem menggunakan arsitektur *decoupled* dengan beberapa komponen utama:

- **Backend API Utama (Laravel):** Mengatur logika bisnis, autentikasi, dan komunikasi antar layanan.  
- **Layanan Analitik (Python/FastAPI):** Menjalankan pemrosesan data mining dan machine learning secara terpisah.  
- **Database Pusat (PostgreSQL):** Menyimpan semua data utama sebagai *single source of truth*.  
- **Frontend:** Terdiri dari tiga aplikasi terpisah — Filament (admin), React PWA (kasir), dan Livewire (pelanggan).

### 2.2 Tumpukan Teknologi

- **Backend API:** PHP 8.x, Laravel  
- **Panel Admin:** Laravel Filament  
- **UI Pelanggan:** Laravel Livewire + Bootstrap 5 (Tabler.io)
- **UI Kasir (PWA):** React.js + Bootstrap 5 (Tabler.io) + Dexie.js + Workbox  
- **Layanan Analitik:** Python, FastAPI  
- **Database:** PostgreSQL 15+ (pusat) dan IndexedDB (offline)  
- **Server:** Nginx (reverse proxy)  
- **Lingkungan:** Docker (development & deployment)  
- **Deployment Target:** VPS Linux

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

### 3.3 Fungsionalitas Kasir (Offline-First PWA)

- Antarmuka kasir berupa PWA yang bisa diinstal dan dijalankan offline.  
- Saat online, aplikasi melakukan *sync down* data referensi ke IndexedDB (menu, kategori, promosi, stok, user).  
- Saat offline, kasir dapat:
  - Melihat menu dan kategori dari cache.  
  - Menerapkan promosi dari cache.  
  - Mengecek stok lokal.  
  - Melakukan transaksi tunai penuh.  
- Setiap transaksi offline mengurangi stok lokal dan disimpan di `transaction_queue`.  
- Saat koneksi kembali, transaksi disinkronkan ke server secara otomatis.

### 3.4 Fungsionalitas Pelanggan (Self-Order Online)

- Pelanggan mengakses antarmuka melalui QR code di meja.  
- UI berbasis Livewire dengan interaksi tanpa *page reload*.  
- Harga otomatis disesuaikan untuk mahasiswa (student_price).  
- Pembayaran online dilakukan melalui Midtrans.

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
- Database lokal menggunakan IndexedDB sesuai skema di `planning/05_indexeddb_schema.md`.

---

## 6. Kebutuhan Deployment

- Aplikasi dibundle menggunakan Docker Compose.  
- CI/CD (misalnya GitHub Actions) harus men-deploy branch `staging` ke server staging dan `main` ke server produksi secara otomatis.

---

**Dokumen ini bersifat versi awal (v1.0) dan akan diperbarui setiap kali terjadi perubahan besar dalam desain arsitektur atau kebutuhan sistem.**
