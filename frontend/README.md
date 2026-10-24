# Frontend LokaHP

Antarmuka Vue 3 + Vite untuk katalog HP dan panel admin toko.

## Prasyarat

- Node.js 24
- Backend Laravel berjalan di `http://localhost:8000`

## Konfigurasi

Salin `.env.example` menjadi `.env`, lalu sesuaikan nilainya.

```env
VITE_API_URL=http://localhost:8000/api
VITE_WA_NUMBER=6281234567890
```

`VITE_WA_NUMBER` bersifat opsional. Jika dikosongkan, tombol WhatsApp tidak ditampilkan.

## Menjalankan

```sh
npm install
npm run dev
```

Vite berjalan di `http://localhost:5173`.

## Perintah Pemeriksaan

```sh
npm run lint
npm run test:unit -- --run
npm run build
```

## Halaman

- `/` — beranda
- `/produk` — katalog dengan pencarian, filter merek, dan urutan harga
- `/produk/:id` — detail produk
- `/admin/login` — login admin
- `/admin/produk` — daftar produk
- `/admin/produk/baru` — tambah produk
- `/admin/produk/:id/edit` — edit produk

Semua permintaan API melewati `src/api.js`. Token admin disimpan di localStorage dan dipasang otomatis
sebagai bearer token. Respons `401` menghapus sesi dan mengarahkan kembali ke halaman login.
