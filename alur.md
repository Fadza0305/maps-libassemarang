# alur.md — Dokumentasi Alur Kerja: Fitur Peta Aplikasi Libas

---

## 1. Alur Kerja User (Publik) — Tanpa Login

### 1.1 Melihat Peta & Filter Kategori

```
[User membuka halaman peta]
        │
        ▼
[Browser memuat Leaflet.js]
[Tile OSM dirender → Peta tampil terfokus di Semarang]
[Marker data OSM (statis/Overpass) dimuat → ditampilkan]
[Data lokal (status=approved) di-fetch via GET /api/places?status=approved → Marker lokal ditampilkan]
        │
        ▼
[Browser meminta izin akses GPS User]
        │
        ├── [Diizinkan] → Simpan {lat, lng} User di state frontend
        └── [Ditolak]   → Gunakan koordinat default pusat Semarang
        │
        ▼
[User memilih kategori filter (misal: "Rumah Sakit")]
        │
        ▼
[Frontend mengirim GET request ke backend:]
  GET /api/places/nearby
  ?category=rs&user_lat={lat}&user_lng={lng}
        │
        ▼
[Laravel Controller: PlaceController@nearby]
  1. Validasi input (category, user_lat, user_lng wajib ada)
  2. Query MySQL → Hitung jarak tiap record dengan Haversine Formula
  3. Filter: status = 'approved' AND category = {category} AND jarak <= 20km
  4. Urutkan: ORDER BY jarak ASC
  5. Return JSON array [{id, name, address, lat, lng, distance_km, ...}]
        │
        ▼
[Frontend menerima JSON]
  → Marker kategori ditampilkan di peta (marker lain di-hide/filter)
  → Sidebar kiri dirender: daftar tempat urut terdekat
     Format tiap item: [Nama Tempat] — [X.XX km] — [Alamat]
```

---

### 1.2 Melihat Detail & Rute ke Lokasi

```
[User klik salah satu Marker atau item di Sidebar]
        │
        ▼
[Popup/Panel detail tampil:]
  - Nama Tempat
  - Kategori
  - Alamat
  - Jam Operasional
  - Deskripsi
  - Tombol: [Tampilkan Rute]
        │
        ▼
[User klik tombol "Tampilkan Rute"]
        │
        ▼
[Leaflet Routing Machine diaktifkan]
  Waypoints:
    - Origin  : {lat User, lng User}
    - Destination: {lat tempat, lng tempat}
  Routing engine: OSRM (https://router.project-osrm.org)
        │
        ▼
[Garis rute ditampilkan di peta]
[Panel instruksi rute tampil (opsional)]
```

---

## 2. Alur Pendaftaran Tempat oleh Owner

### 2.1 Login & Akses Dashboard

```
[Owner mengakses /login]
        │
        ▼
[Mengisi email & password → Submit]
        │
        ▼
[Laravel Auth: credentials divalidasi]
        ├── [Gagal] → Redirect ke /login dengan pesan error
        └── [Sukses] → Redirect ke /owner/dashboard
        │
        ▼
[Dashboard Owner tampil:]
  - Tabel daftar tempat milik Owner
  - Kolom: Nama | Kategori | Status | Alasan Penolakan | Aksi
  - Status badge: [Disetujui ✓] [Menunggu ⏳] [Ditolak ✗]
  - Tombol: [+ Tambah Tempat Baru]
```

---

### 2.2 Menambah Tempat Baru (Klik di Peta)

```
[Owner klik tombol "+ Tambah Tempat Baru"]
        │
        ▼
[Peta interaktif tampil dalam mode "Pemilihan Lokasi"]
[Instruksi muncul: "Klik pada peta untuk menentukan lokasi tempat Anda"]
        │
        ▼
[Owner klik di titik tertentu pada peta]
        │
        ▼
[Frontend (Leaflet.js) menangkap event klik → dapatkan {lat_klik, lng_klik}]
        │
        ▼
[VALIDASI GEOFENCING — FRONTEND:]
  Hitung jarak: haversine({lat_klik, lng_klik}, {-7.005145, 110.438126})
        │
        ├── [Jarak > 20 km]
        │     → Tampilkan notifikasi: "Lokasi di luar batas wilayah Semarang (maks. 20 km)"
        │     → Pin tidak ditempatkan
        │     → Kembali ke langkah "Owner klik di peta"
        │
        └── [Jarak ≤ 20 km]
              → Pin/Marker sementara ditempatkan di titik klik
              → Form input tempat muncul (sidebar/modal)
        │
        ▼
[Owner mengisi Form:]
  - Nama Tempat       (wajib)
  - Kategori          (wajib, dropdown)
  - Alamat            (wajib)
  - Jam Operasional   (opsional)
  - Deskripsi         (opsional)
  - Koordinat         (auto-filled dari klik: lat, lng — read-only)
        │
        ▼
[Owner klik tombol "Kirim Pengajuan"]
        │
        ▼
[Frontend mengirim POST request:]
  POST /owner/places
  Body: {name, category, address, operating_hours, description, latitude, longitude}
  Header: Authorization (session/cookie Laravel)
        │
        ▼
[Laravel Controller: OwnerPlaceController@store]
  1. Middleware Auth → cek role = 'owner'
  2. Validasi input (name, category, address, latitude, longitude wajib)
  3. VALIDASI GEOFENCING — BACKEND (lapisan kedua):
       Haversine({latitude, longitude}, {-7.005145, 110.438126})
       ├── [Jarak > 20 km] → Return 422: {error: "Koordinat di luar batas wilayah."}
       └── [Jarak ≤ 20 km] → Lanjut
  4. Simpan ke tabel `places`:
       - owner_id   = Auth::id()
       - status     = 'pending'
       - Kolom lain dari input
  5. Return response 201: {message: "Pengajuan berhasil dikirim, menunggu verifikasi Admin."}
        │
        ▼
[Frontend menampilkan notifikasi sukses]
[Dashboard Owner direfresh → tempat baru muncul dengan status "Menunggu"]
```

---

## 3. Alur Verifikasi Admin

```
[Admin login → Redirect ke /admin/dashboard]
        │
        ▼
[Panel Manajemen Pengajuan tampil:]
  - Tabel: Nama Tempat | Owner | Kategori | Koordinat | Tanggal | Status | Aksi
  - Filter: Tampilkan [Semua | Pending | Approved | Rejected]
        │
        ▼
[Admin klik tombol "Review" pada satu pengajuan berstatus 'pending']
        │
        ▼
[Halaman Detail Pengajuan tampil:]
  - Semua data tempat
  - Peta mini (Leaflet) dengan marker lokasi pengajuan
  - Admin dapat membandingkan secara visual dengan marker OSM & data lokal existing
  - Tombol: [✓ Approve] [✗ Reject]
        │
        ▼
[SKENARIO A — Admin klik "Approve"]
        │
        ▼
  [PATCH /admin/places/{id}/approve]
  [AdminPlaceController@approve]
    1. Middleware Auth → cek role = 'admin'
    2. Update tabel `places`: SET status = 'approved', rejection_reason = NULL
    3. Return 200: {message: "Tempat berhasil disetujui."}
        │
        ▼
  [Tempat kini tampil di peta publik sebagai marker lokal]
  [Dashboard Owner: status berubah → "Disetujui ✓"]

        │
        ▼
[SKENARIO B — Admin klik "Reject"]
        │
        ▼
  [Modal/Form muncul: "Masukkan alasan penolakan" (textarea, wajib diisi)]
        │
        ▼
  [Admin mengisi alasan → klik "Konfirmasi Penolakan"]
        │
        ▼
  [PATCH /admin/places/{id}/reject]
  Body: {rejection_reason: "..."}
  [AdminPlaceController@reject]
    1. Middleware Auth → cek role = 'admin'
    2. Validasi: rejection_reason wajib ada dan tidak kosong
    3. Update tabel `places`: SET status = 'rejected', rejection_reason = {alasan}
    4. Return 200: {message: "Pengajuan telah ditolak."}
        │
        ▼
  [Tempat tidak tampil di peta publik]
  [Dashboard Owner: status berubah → "Ditolak ✗" + alasan penolakan tampil]
```

---

## 4. Catatan Teknis: Implementasi Haversine Formula

### 4.1 Implementasi di Laravel (PHP)

Buat helper atau method di dalam Service Class / Controller:

```php
/**
 * Menghitung jarak antara dua koordinat menggunakan Haversine Formula.
 * @return float Jarak dalam kilometer
 */
function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $earthRadius = 6371; // Radius bumi dalam kilometer

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat / 2) * sin($dLat / 2)
       + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
       * sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c;
}
```

---

### 4.2 Query MySQL dengan Haversine (Untuk Sorting & Filtering)

Gunakan raw SQL expression di dalam Laravel Eloquent/Query Builder untuk efisiensi:

```php
// Di dalam PlaceController@nearby
public function nearby(Request $request)
{
    $request->validate([
        'category' => 'required|string',
        'user_lat' => 'required|numeric',
        'user_lng' => 'required|numeric',
    ]);

    $userLat = $request->user_lat;
    $userLng = $request->user_lng;
    $category = $request->category;

    // Konstanta pusat Semarang untuk filter geofencing
    $centerLat = -7.005145;
    $centerLng = 110.438126;
    $maxRadius = 20; // km

    $places = Place::select('*')
        ->selectRaw("
            (6371 * ACOS(
                COS(RADIANS(?)) * COS(RADIANS(latitude))
                * COS(RADIANS(longitude) - RADIANS(?))
                + SIN(RADIANS(?)) * SIN(RADIANS(latitude))
            )) AS distance_km
        ", [$userLat, $userLng, $userLat])
        ->where('status', 'approved')
        ->where('category', $category)
        ->havingRaw("distance_km <= ?", [$maxRadius])
        ->orderBy('distance_km', 'asc')
        ->get();

    return response()->json($places);
}
```

---

### 4.3 Validasi Geofencing Backend (Saat Owner Submit)

```php
// Di dalam OwnerPlaceController@store
public function store(Request $request)
{
    $request->validate([
        'name'      => 'required|string|max:255',
        'category'  => 'required|string|max:100',
        'address'   => 'required|string',
        'latitude'  => 'required|numeric|between:-90,90',
        'longitude' => 'required|numeric|between:-180,180',
    ]);

    $centerLat = -7.005145;
    $centerLng = 110.438126;

    $distance = haversine(
        $request->latitude, $request->longitude,
        $centerLat, $centerLng
    );

    if ($distance > 20) {
        return response()->json([
            'error' => 'Koordinat lokasi di luar batas wilayah Semarang (maks. 20 km dari pusat kota).'
        ], 422);
    }

    Place::create([
        'owner_id'         => auth()->id(),
        'name'             => $request->name,
        'category'         => $request->category,
        'address'          => $request->address,
        'latitude'         => $request->latitude,
        'longitude'        => $request->longitude,
        'operating_hours'  => $request->operating_hours,
        'description'      => $request->description,
        'status'           => 'pending',
    ]);

    return response()->json([
        'message' => 'Pengajuan berhasil dikirim. Menunggu verifikasi Admin.'
    ], 201);
}
```

---

### 4.4 Validasi Geofencing Frontend (Leaflet.js)

```javascript
// Konstanta pusat Semarang
const SEMARANG_CENTER = { lat: -7.005145, lng: 110.438126 };
const MAX_RADIUS_KM = 20;

// Fungsi Haversine di sisi klien
function haversineClient(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2
            + Math.cos(lat1 * Math.PI / 180)
            * Math.cos(lat2 * Math.PI / 180)
            * Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// Event listener klik peta (mode tambah tempat)
map.on('click', function (e) {
    const { lat, lng } = e.latlng;
    const distance = haversineClient(lat, lng, SEMARANG_CENTER.lat, SEMARANG_CENTER.lng);

    if (distance > MAX_RADIUS_KM) {
        alert('Lokasi yang Anda pilih berada di luar batas wilayah Semarang (maks. 20 km). Silakan pilih lokasi lain.');
        return; // Batalkan — tidak menempatkan marker
    }

    // Tempatkan marker sementara dan tampilkan form input
    placeTempMarker(lat, lng);
    showPlaceForm(lat, lng);
});
```

---

## 5. Ringkasan Route Laravel (API & Web)

| Method | URI | Controller | Role | Keterangan |
|---|---|---|---|---|
| GET | `/` atau `/map` | `MapController@index` | Public | Halaman peta utama |
| GET | `/api/places` | `PlaceController@index` | Public | Ambil semua tempat approved |
| GET | `/api/places/nearby` | `PlaceController@nearby` | Public | Filter + sort by distance |
| POST | `/owner/places` | `OwnerPlaceController@store` | Owner | Submit tempat baru |
| GET | `/owner/dashboard` | `OwnerDashboardController@index` | Owner | Dashboard Owner |
| GET | `/admin/dashboard` | `AdminDashboardController@index` | Admin | Panel manajemen pengajuan |
| PATCH | `/admin/places/{id}/approve` | `AdminPlaceController@approve` | Admin | Approve pengajuan |
| PATCH | `/admin/places/{id}/reject` | `AdminPlaceController@reject` | Admin | Reject dengan alasan |

---

## 6. Catatan Penting untuk AI Agent (Antigravity)

1. **Geofencing adalah aturan keras (hard rule):** Implementasikan validasi di **dua lapisan** — frontend dan backend. Jangan abaikan salah satunya.
2. **Haversine di raw SQL:** Gunakan `selectRaw` + `havingRaw` di Laravel, bukan filter PHP setelah query (tidak efisien untuk data besar).
3. **Status tempat adalah gerbang visibilitas:** Query ke `/api/places` **selalu** harus menyertakan `WHERE status = 'approved'`. Jangan pernah menampilkan data `pending` atau `rejected` ke User publik.
4. **Role middleware:** Gunakan middleware Laravel (`auth`, custom `CheckRole`) pada semua route Owner dan Admin. Jangan mengandalkan logika role hanya di Controller.
5. **Rejection reason wajib:** Validasi `rejection_reason` sebagai `required|string|min:10` saat Admin melakukan reject, agar Owner mendapat feedback yang berarti.
6. **Koordinat precision:** Simpan `latitude` sebagai `DECIMAL(10, 8)` dan `longitude` sebagai `DECIMAL(11, 8)` di MySQL untuk akurasi sub-meter.
