# Aplikasi Monitoring Fasilitas - Fitur Peta Libas Semarang

[![Laravel Version](https://img.shields.io/badge/Laravel-12.0-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D_8.2-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![JS Stack](https://img.shields.io/badge/Frontend-Leaflet.js-green?style=for-the-badge&logo=leaflet)](https://leafletjs.com)

Aplikasi Monitoring Fasilitas adalah sistem informasi geografis (SIG) berbasis web yang dirancang khusus untuk wilayah Kota Semarang. Aplikasi ini memadukan data dari OpenStreetMap (OSM) dan data lokal yang diajukan oleh pemilik usaha (Owner) setelah diverifikasi oleh Admin.

Aplikasi ini dilengkapi dengan pembatasan geografis (**Geofencing**) absolut sebesar **20 km** dari titik pusat Kota Semarang (`lat: -7.005145, lng: 110.438126`), perhitungan jarak terdekat (**Haversine Formula**), dan pencarian rute terdekat secara dinamis.

---

## 🚀 Fitur Utama

- **Peta Interaktif Semarang:** Menggunakan Leaflet.js dengan batas operasional wilayah 20 km dari pusat kota Semarang.
- **Geofencing Berlapis:** Validasi koordinat lokasi pada sisi *frontend* (Leaflet.js) dan *backend* (Laravel validation) untuk memastikan data berada dalam wilayah Semarang.
- **Pencarian & Filter Terdekat:** Pengurutan tempat approved terdekat berdasarkan posisi GPS pengguna menggunakan Haversine Formula.
- **Rute Perjalanan Dinamis:** Integrasi Leaflet Routing Machine dengan routing engine OSRM (Open Source Routing Machine).
- **Dashboard Multi-Role:**
  - **Admin:** Memverifikasi pengajuan tempat baru (Approve / Reject dengan alasan penolakan).
  - **Owner:** Mendaftarkan tempat usaha baru dengan mengeklik langsung pada peta dan memantau status persetujuan pengajuannya.
  - **Publik:** Mengakses peta secara anonim, mencari tempat, memfilter kategori, serta melihat rute.

---

## 📋 Prasyarat Sistem

Sebelum memulai instalasi, pastikan sistem Anda telah memenuhi prasyarat berikut:

* **PHP** >= 8.2 (Pastikan ekstensi PHP seperti `sqlite3`, `pdo_sqlite`, `curl`, `mbstring`, `xml` telah diaktifkan)
* **Composer**
* **Node.js** & **npm**
* **Database SQLite** (default) atau **MySQL**

---

## ⚙️ Instalasi

Ikuti langkah-langkah di bawah ini untuk memasang proyek secara lokal:

### 1. Klon Repositori dan Masuk ke Direktori Proyek
```bash
cd monitoring-fasilitas
```

### 2. Instalasi Otomatis (Direkomendasikan)
Proyek ini sudah dilengkapi dengan *custom command* Composer untuk mempermudah proses instalasi:
```bash
composer setup
```
Perintah ini akan secara otomatis melakukan:
- Instalasi dependensi PHP (`composer install`).
- Membuat file konfigurasi `.env` dari `.env.example`.
- Membuat kunci aplikasi (`php artisan key:generate`).
- Menjalankan migrasi database (`php artisan migrate --force`).
- Instalasi dependensi Node.js (`npm install`).
- Membangun aset frontend (`npm run build`).

> [!NOTE]
> Secara default, proyek ini menggunakan database **SQLite** (`database/database.sqlite`). File database akan dibuat secara otomatis saat proses setup/migrasi dijalankan.

### 3. Seed Data (Mengisi Data Awal)
Untuk mengisi data awal seperti kategori dan akun admin default, jalankan perintah seeder berikut:
```bash
php artisan db:seed
```

---

## 💻 Penggunaan

### 1. Menjalankan Server Pengembangan
Jalankan semua server (Laravel server, Vite, Queue Listener, dan Pail Logger) secara bersamaan dengan satu perintah:
```bash
composer dev
```
Setelah dijalankan, buka browser dan akses aplikasi melalui:
**[http://localhost:8000](http://localhost:8000)**

### 2. Akun Uji Coba (Demo Accounts)

Untuk menguji fitur-fitur berbayar/terotentikasi, Anda dapat menggunakan akun berikut:

#### **A. Administrator (Admin)**
* **Fungsi:** Mengelola pendaftaran tempat, menyetujui (Approve), atau menolak (Reject) dengan alasan penolakan.
* **Email:** `admin@test.com`
* **Password:** `123456`
* **Halaman Panel:** `/admin/dashboard`

#### **B. Pemilik Usaha (Owner)**
* **Fungsi:** Mendaftarkan tempat baru dengan mengeklik peta, melihat daftar dan status tempat yang diajukan.
* **Akses:** Silakan mendaftar akun baru melalui halaman `/register` atau halaman pendaftaran di aplikasi.
* **Halaman Panel:** `/owner/dashboard`

---

## 🛠️ Tech Stack & Library

* **Backend:** Laravel 12.x
* **Frontend Logic:** JavaScript (ES6+), Leaflet.js
* **Maps & Routing:** OpenStreetMap (OSM) Tiles, Leaflet Routing Machine, OSRM API
* **Database:** SQLite / MySQL
