# konsep.md — Dokumentasi Konsep: Fitur Peta Aplikasi Libas

---

## 1. Visi & Ruang Lingkup

**Aplikasi Libas** menghadirkan fitur peta berbasis web yang dirancang eksklusif untuk **Kota Semarang**. Tujuan utamanya adalah membantu pengguna publik menemukan tempat-tempat penting di sekitar mereka secara cepat dan akurat, sekaligus memberi ruang bagi pemilik usaha lokal untuk mendaftarkan tempat mereka melalui mekanisme verifikasi Admin.

> **Batasan Wilayah Absolut:** Seluruh operasional fitur peta dikunci dalam radius **20 km** dari titik pusat Kota Semarang (`lat: -7.005145, lng: 110.438126`). Tidak ada data atau input yang diproses di luar batas ini.

---

## 2. Tech Stack

| Komponen | Teknologi |
|---|---|
| Backend Framework | Laravel (PHP) |
| Database | MySQL |
| Map Library (Frontend) | Leaflet.js |
| Sumber Data Peta Utama | OpenStreetMap (OSM) via Tile Layer |
| Sumber Data Lokal | Database MySQL (tabel `places`) |
| Geolokasi User | Browser Geolocation API |
| Kalkulasi Jarak | Haversine Formula (di Laravel Controller) |

---

## 3. Role Pengguna

### 3.1 User (Publik)
- **Autentikasi:** Tidak diperlukan (akses anonim).
- **Kapabilitas:**
  - Melihat peta OSM interaktif yang terfokus pada wilayah Semarang.
  - Memfilter tempat berdasarkan kategori (Polisi, Rumah Sakit, SPBU, dll.).
  - Melihat daftar tempat di sidebar, **diurutkan dari yang terdekat** berdasarkan lokasi GPS User.
  - Melihat rute dari posisi User ke lokasi yang dipilih (menggunakan Leaflet Routing atau OSRM).
  - Melihat detail tempat (nama, alamat, jam operasional, deskripsi).

### 3.2 Owner (Pemilik Usaha)
- **Autentikasi:** **Wajib login.**
- **Kapabilitas:**
  - Mengakses Dashboard Owner untuk memantau status tempat yang telah didaftarkan.
  - Menambah tempat baru dengan cara **klik langsung di peta** (pin interaktif).
  - Mengisi form detail tempat: nama, alamat, kategori, jam operasional, deskripsi.
  - Melihat status setiap pengajuan: `disetujui`, `menunggu`, atau `ditolak` (beserta alasan penolakan dari Admin).

### 3.3 Admin
- **Autentikasi:** **Wajib login.**
- **Kapabilitas:**
  - Mengakses panel manajemen pengajuan dari Owner.
  - Melakukan aksi **Approve** (mengubah status ke `disetujui`, tempat tampil di peta publik).
  - Melakukan aksi **Reject** dengan menyertakan **alasan penolakan** yang wajib diisi.
  - Melihat detail dan koordinat tempat yang diajukan sebelum mengambil keputusan.

---

## 4. Fitur Utama & Spesifikasi Teknis

### 4.1 Tampilan Peta (Frontend — Leaflet.js)
- Tile layer bersumber dari OpenStreetMap.
- Peta di-*initialize* terfokus pada pusat Semarang dengan zoom level yang sesuai.
- Lingkaran visual radius 20 km ditampilkan sebagai referensi batas wilayah (opsional, bisa di-toggle).
- Marker dibedakan berdasarkan sumber data:
  - **Marker Biru/Default:** Data dari OSM (via Overpass API atau data statis).
  - **Marker Khusus (Warna berbeda per kategori):** Data dari database lokal (hanya yang berstatus `disetujui`).

### 4.2 Geofencing 20 km (Aturan Bisnis Kritis)
- **Definisi Pusat:** `lat: -7.005145, lng: 110.438126` (Pusat Kota Semarang).
- **Radius Maksimum:** `20.000 meter` (20 km).
- **Validasi Berlapis:**
  1. **Frontend (Leaflet.js):** Saat Owner mengklik peta untuk menentukan lokasi, sistem langsung menghitung jarak dari pusat. Jika > 20 km, klik diabaikan dan ditampilkan notifikasi: *"Lokasi di luar batas wilayah Semarang."*
  2. **Backend (Laravel Controller):** Setiap request penyimpanan tempat baru **wajib** memvalidasi ulang koordinat di sisi server menggunakan Haversine Formula. Request yang melanggar batas dikembalikan dengan response `422 Unprocessable Entity`.

### 4.3 Data Hybrid & Toleransi Duplikasi
- Peta menampilkan data dari **dua sumber sekaligus** tanpa penggabungan paksa:
  - Data OSM diambil secara *on-demand* (Overpass API) atau disimpan sebagai data statis.
  - Data lokal diambil dari tabel `places` MySQL (hanya status `disetujui`).
- **Duplikasi Diizinkan:** Sistem tidak melakukan pengecekan duplikasi otomatis. Tanggung jawab ada pada:
  - **Owner:** Wajib mengecek secara visual di peta sebelum mendaftarkan tempat.
  - **Admin:** Wajib memverifikasi apakah tempat yang diajukan sudah terdaftar (dari OSM maupun data lokal) sebelum meng-*approve*.

### 4.4 Pencarian & Pengurutan Terdekat
- Dipicu saat User mengaktifkan filter kategori.
- Backend menerima parameter: `latitude`, `longitude` (dari GPS User), dan `category`.
- Kalkulasi jarak menggunakan **Haversine Formula** di dalam Laravel Controller/Service.
- Response berupa JSON array tempat yang sudah **diurutkan ascending** berdasarkan jarak (terdekat ke terjauh).
- Sidebar frontend me-*render* ulang daftar berdasarkan urutan response.

### 4.5 Rute ke Lokasi
- User dapat memilih satu tempat dari daftar/marker untuk melihat rute.
- Rute ditampilkan langsung di peta menggunakan **Leaflet Routing Machine** dengan sumber routing **OSRM** (gratis, berbasis OSM).
- Titik asal rute adalah posisi GPS User saat itu.

---

## 5. Struktur Database (Tabel Utama)

### Tabel: `places`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `owner_id` | BIGINT (FK) | Relasi ke tabel `users` |
| `name` | VARCHAR(255) | Nama tempat |
| `category` | VARCHAR(100) | Kategori (polisi, rs, spbu, dll.) |
| `address` | TEXT | Alamat lengkap |
| `latitude` | DECIMAL(10, 8) | Koordinat lintang |
| `longitude` | DECIMAL(11, 8) | Koordinat bujur |
| `operating_hours` | VARCHAR(255) | Jam operasional |
| `description` | TEXT | Deskripsi tempat |
| `status` | ENUM | `pending`, `approved`, `rejected` |
| `rejection_reason` | TEXT | Alasan penolakan (nullable) |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |

### Tabel: `users`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | BIGINT (PK) | Auto increment |
| `name` | VARCHAR(255) | Nama pengguna |
| `email` | VARCHAR(255) | Email (unique) |
| `password` | VARCHAR(255) | Bcrypt hash |
| `role` | ENUM | `owner`, `admin` |
| `created_at` | TIMESTAMP | — |
| `updated_at` | TIMESTAMP | — |

---

## 6. Batasan & Asumsi

- Fitur peta **hanya** mencakup wilayah Kota Semarang (radius 20 km dari pusat).
- User publik **tidak** dapat menambah atau mengubah data apapun.
- Tempat dengan status `pending` dan `rejected` **tidak** tampil di peta publik.
- Sistem **tidak** memiliki mekanisme deduplication otomatis; proses ini manual dan berbasis kebijakan.
- Rute yang ditampilkan adalah rute jalan (driving/walking via OSRM), bukan garis lurus.
- Aplikasi diasumsikan berjalan di lingkungan dengan koneksi internet aktif (untuk tile OSM dan OSRM).
