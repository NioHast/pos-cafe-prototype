# Persiapan Upgrade Filament v4 (Capstone2)

Dokumen ini menyiapkan langkah yang dibutuhkan sebelum, saat, dan setelah upgrade Filament dari v3 ke v4.

## 1. Baseline Saat Ini (hasil audit)

- PHP requirement di project: `^8.2` (sudah sesuai untuk v4).
- Laravel requirement di project: `^12.0` (sudah di atas syarat minimal v4).
- Filament requirement di `composer.json`: `^3.2`.
- Filament terpasang di `composer.lock`: `filament/filament v3.3.50`.
- Livewire terpasang di `composer.lock`: `livewire/livewire v3.7.15`.
- Paket Filament terpasang: `filament/actions`, `filament/filament`, `filament/forms`, `filament/infolists`, `filament/notifications`, `filament/support`, `filament/tables`, `filament/widgets`.
- Tidak ditemukan plugin Filament pihak ketiga.
- Tidak ditemukan custom view/theme Filament di:
  - `resources/views/**/filament/**`
  - `resources/css/filament/**`

## 2. Dampak Upgrade yang Perlu Diantisipasi

### 2.1 Touchpoint terbesar di kode admin (Filament)

- 16 signature form gaya v3 (`function form(Form $form): Form`).
- 17 signature table gaya v3 (`function table(Table $table): Table`).
- 16 pemakaian `->actions(...)` pada table.
- 16 pemakaian `->bulkActions(...)` pada table.

Catatan:
- Upgrade script Filament v4 biasanya mengerjakan banyak perubahan mekanis ini, tetapi tetap perlu review manual.

### 2.2 Hal yang sudah dicek dan aman

- Tidak ditemukan pemakaian parameter URL lama (`activeRelationManager`, `tableSearch`, dst).
- Tidak ditemukan metode table lama yang sudah dihapus (`getTableRecordUrlUsing`, dst).

### 2.3 Hal yang tetap wajib review manual

- Behavior filter table (di v4, filter cenderung deferred secara default).
- Behavior default sorting table (v4 menambahkan default key sort).
- Signature metode authorization kustom pada resource tertentu.
- Perubahan semantik validasi `unique()` (abaikan record aktif by default di v4).

## 3. Checklist Sebelum Menjalankan Upgrade

- [ ] Pastikan branch khusus upgrade dibuat.
- [ ] Pastikan worktree bersih atau perubahan saat ini sudah di-commit.
- [ ] Ambil backup database runtime (misalnya DB `pos_cafe`).
- [ ] Jalankan baseline test supaya punya pembanding sebelum upgrade.
- [ ] Siapkan waktu untuk fix manual setelah auto-upgrade selesai.

Contoh baseline test minimal:

```powershell
php artisan test tests/Feature/Inventory/DailyIngredientUsageAggregationTest.php tests/Feature/Admin/WasteRecordFlowTest.php --testdox
```

## 4. Runbook Upgrade v4 (Windows PowerShell)

Jalankan dari root `Capstone2`.

```powershell
# 1) Install helper upgrade script untuk v4
composer require filament/upgrade:"~4.0" -W --dev

# 2) Jalankan auto-upgrade pada direktori app
vendor/bin/filament-v4 app

# 3) Apply versi paket Filament v4
composer require filament/filament:"~4.0" -W --no-update
composer update

# 4) Bersihkan helper upgrade (opsional, setelah selesai)
composer remove filament/upgrade --dev
```

Opsional untuk migrasi struktur direktori resource/cluster ke gaya v4:

```powershell
php artisan filament:upgrade-directory-structure-to-v4 --dry-run
php artisan filament:upgrade-directory-structure-to-v4
```

## 5. Checklist Setelah Upgrade

- [ ] Jalankan `php artisan filament:upgrade`.
- [ ] Jalankan `php artisan optimize:clear`.
- [ ] Jalankan test suite utama.
- [ ] Uji manual panel admin:
  - [ ] login admin
  - [ ] list/filter/search/sort tiap resource utama
  - [ ] create/edit/delete data master
  - [ ] relation manager (menu-ingredients, ingredient-batches, order-items)
  - [ ] resource read-only (`daily-ingredient-usages`)

Contoh verifikasi cepat:

```powershell
php artisan test --testsuite=Feature --stop-on-failure
php artisan test --testsuite=Unit --stop-on-failure
```

## 6. Risk Register Ringkas

- Risiko sedang: perubahan perilaku filter/sort table bisa mengubah ekspektasi UI admin.
- Risiko sedang: beberapa file mungkin butuh penyesuaian manual walau auto-upgrade sukses.
- Risiko rendah: custom theme Filament (saat ini belum ada, jadi dampak lebih kecil).

## 7. Rollback Plan

Jika ada masalah besar setelah upgrade:

1. Kembali ke commit sebelum upgrade.
2. Jalankan `composer install` ulang.
3. Bersihkan cache Laravel.
4. Pulihkan database dari backup jika diperlukan.

```powershell
composer install
php artisan optimize:clear
```
