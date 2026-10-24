# LokaHP - Toko Online HP

Aplikasi toko online ponsel dengan API Laravel 12 di `backend/` dan antarmuka Vue 3 di `frontend/`. Katalog berisi data HP, merek, spesifikasi, harga, dan stok. Admin dapat mengelola produk melalui panel, sedangkan pelanggan melihat katalog dan detail produk setelah masuk.

## Fitur

- Login token Laravel Sanctum dan logout yang menghapus token aktif.
- Seluruh endpoint selain `POST /api/login` wajib memakai Bearer token.
- CRUD produk lengkap dengan validasi dan pembatasan admin.
- Frontend login-first: semua rute selain `/login` memerlukan sesi.
- Token disimpan di `localStorage` dan dikirim otomatis oleh interceptor Axios.
- Respons `401` membersihkan sesi dan kembali ke halaman login.
- Docker Compose menjalankan MySQL, backend Laravel, dan frontend Nginx.
- Data MySQL disimpan pada named volume `db_data`.

## Menjalankan dengan Docker Compose

Prasyarat: Docker Desktop dengan Docker Compose v2.

```powershell
Copy-Item .env.example .env
php backend/artisan key:generate --show
```

Isi `APP_KEY` di `.env` dengan hasil perintah terakhir. Ganti `DB_PASSWORD` dengan nilai acak yang kuat, lalu jalankan:

```powershell
docker compose up -d --build
```

Layanan yang tersedia:

| Layanan | Alamat | Keterangan |
| --- | --- | --- |
| Frontend | http://localhost:8080 | Aplikasi Vue, proxy `/api/` ke service `backend` |
| Backend | http://localhost:8000 | API Laravel, health check `/up` |
| Database | jaringan internal `db:3306` | MySQL 8.4, tanpa port publik |

Perintah backend menjalankan migrasi dan seeder setiap start. Seeder bersifat idempoten sehingga aman dijalankan berulang. Data tetap ada setelah `docker compose down` dan `docker compose up -d` karena memakai volume `db_data`.

## Akun demo

| Peran | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | nilai `ADMIN_PASSWORD` (default `password`) |
| Pelanggan | `pelanggan@example.com` | nilai `PELANGGAN_PASSWORD` (default `password`) |

## Endpoint API

Semua permintaan dan balasan memakai JSON. Endpoint selain login memerlukan header `Authorization: Bearer <token>`.

| Metode | Endpoint | Akses | Keterangan |
| --- | --- | --- | --- |
| POST | `/api/login` | Publik | Mendapatkan token dan data user |
| GET | `/api/me` | Token | Profil user aktif |
| POST | `/api/logout` | Token | Menghapus token aktif |
| GET | `/api/produk` | Token | Daftar produk aktif; query `q`, `merek`, `urut` |
| GET | `/api/produk/{id}` | Token | Detail produk aktif |
| GET | `/api/merek` | Token | Daftar merek unik dari produk aktif |
| GET | `/api/admin/produk` | Admin | Semua produk, termasuk nonaktif |
| POST | `/api/produk` | Admin | Menambah produk, balasan `201` |
| PUT | `/api/produk/{id}` | Admin | Mengubah produk, balasan `200` |
| DELETE | `/api/produk/{id}` | Admin | Menghapus produk, balasan `204` |

Kode status penting: `401` saat token tidak ada atau sudah tidak berlaku, `403` saat akun non-admin mencoba menulis, `422` saat validasi gagal.

Nilai `urut` yang tersedia: `terbaru`, `harga_asc`, dan `harga_desc`.

## Koleksi Postman

Koleksi berada di `backend/postman/toko-hp-api.postman_collection.json`. Impor ke Postman atau jalankan dengan Newman:

```powershell
npx --yes newman run backend/postman/toko-hp-api.postman_collection.json --env-var base_url=http://localhost:8000/api
```

Request login admin dan pelanggan menyimpan token ke variabel koleksi. Koleksi mencakup request tanpa token (`401`), CRUD admin, validasi (`422`), akses pelanggan (`403`), logout, dan token lama yang ditolak.

## Pengembangan lokal

Backend:

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate:fresh --seed
php artisan serve
```

Frontend:

```powershell
cd frontend
npm ci
Copy-Item .env.example .env
npm run dev
```

`frontend/.env.example` mengarahkan `VITE_API_URL` ke `http://localhost:8000/api`. Alamat API hanya dibaca dari `import.meta.env.VITE_API_URL`; tidak ada URL API yang ditulis di komponen.

## Pengujian

```powershell
cd backend
php artisan test
vendor\bin\pint --test

cd ..\frontend
npm run lint
npm run test:unit -- --run
npm run build
```

## CI/CD

Workflow `.github/workflows/ci.yml` berjalan pada push ke `main`, `dev`, `feat/**`, `fix/**`, serta Pull Request ke `main` dan `dev`.

- `backend-test`: PHP 8.2, `composer install`, `php artisan test`, dan `vendor/bin/pint --test`.
- `frontend-build`: Node.js 24, `npm ci`, lint, unit test, dan `npm run build`.
- `deploy`: hanya untuk push ke `main`. Job ini membangun image backend dan frontend, mengunggahnya ke GHCR dengan tag nomor commit, lalu menjalankan smoke test memakai `APP_KEY` dan `DB_PASSWORD` dari GitHub Secrets.

Nama image:

- `ghcr.io/dzakidiandra/uts-pl-535924-backend`
- `ghcr.io/dzakidiandra/uts-pl-535924-frontend`

## Struktur repository

```text
backend/              API Laravel 12
  postman/            Koleksi Postman
  Dockerfile          Image backend multi-stage
frontend/             Aplikasi Vue 3 + Vite
  Dockerfile          Build Node lalu sajikan dengan Nginx
  nginx.conf          SPA dan proxy /api/ ke backend
docker-compose.yml    MySQL, backend, frontend
.env.example          Contoh konfigurasi Compose
```
