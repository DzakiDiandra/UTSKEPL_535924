# Toko HP UTSKEPL_535924

Aplikasi toko online ponsel yang terdiri dari API Laravel dan antarmuka Vue 3. Tahap U1 menyediakan backend untuk katalog produk, autentikasi admin, dan pengelolaan stok.

## Fitur backend

- Katalog produk publik berisi HP beserta harga, stok, dan spesifikasi.
- Pencarian nama/merek, filter merek, dan pengurutan harga.
- Autentikasi token dengan Laravel Sanctum.
- CRUD produk khusus admin, termasuk produk yang sedang nonaktif.
- Status stok otomatis: `habis`, `menipis`, atau `tersedia`.
- Data contoh 12 HP dari 6 merek.

## Teknologi

- PHP 8.2
- Laravel 12 + Laravel Sanctum
- SQLite
- PHPUnit dan Laravel Pint

## Menjalankan backend lokal

Jalankan dari PowerShell pada folder root proyek:

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate:fresh --seed
php artisan serve
```

API tersedia di `http://localhost:8000/api`. Cek kesehatan aplikasi di `http://localhost:8000/up`.

CORS mengizinkan `http://localhost:5173` (Vite) dan `http://localhost:8080` (frontend hasil build).

## Akun demo

| Peran | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` atau nilai `ADMIN_PASSWORD` |
| Pelanggan | `pelanggan@example.com` | `password` |

Ganti password admin melalui variabel `ADMIN_PASSWORD` di `.env` sebelum menjalankan seeder.

## Endpoint utama

| Metode | Endpoint | Akses | Keterangan |
| --- | --- | --- | --- |
| POST | `/api/login` | Publik | Mendapatkan token dan data user |
| GET | `/api/me` | Bearer token | Profil user aktif |
| POST | `/api/logout` | Bearer token | Menghapus token aktif |
| GET | `/api/produk` | Publik | Daftar produk aktif; query `q`, `merek`, `urut` |
| GET | `/api/produk/{id}` | Publik | Detail produk aktif |
| GET | `/api/merek` | Publik | Daftar merek unik dari produk aktif |
| GET | `/api/admin/produk` | Admin | Semua produk, termasuk nonaktif |
| POST | `/api/produk` | Admin | Menambah produk |
| PUT | `/api/produk/{id}` | Admin | Mengubah produk |
| DELETE | `/api/produk/{id}` | Admin | Menghapus produk |

Nilai `urut` yang tersedia: `terbaru`, `harga_asc`, dan `harga_desc`.

## Pengujian

```powershell
cd backend
php artisan test
vendor\bin\pint --test
```

Koleksi Postman ada di `docs/postman/toko-hp-api.postman_collection.json`. Jalankan request **Login Admin** terlebih dahulu agar variabel `token` terisi otomatis.

## Catatan data contoh

Harga dan spesifikasi pada seeder merupakan perkiraan untuk kebutuhan demonstrasi, bukan klaim spesifikasi resmi. Data produk dapat diubah melalui panel admin.
