<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Owner - Yanmaps Semarang</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #121212; padding: 20px; color: #eee; margin: 0; }
        .header-logo { text-align: center; margin-bottom: 20px; }
        .header-logo img { height: 60px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
        .container { background: #1e1e1e; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); max-width: 960px; margin: auto; border: 1px solid #333; }
        h2 { color: #f8ca00; margin-top: 0; }
        p.subtitle { color: #aaa; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #444; padding: 12px; text-align: left; vertical-align: top; }
        th { background-color: #145022; color: #eee; font-weight: bold; border-color: #1e7031; }
        tr:nth-child(even) { background-color: #242424; }
        tr { transition: background 0.2s; }

        .top-menu { float: right; display: flex; gap: 10px; }
        .logout-btn { background: #444; padding: 8px 15px; color:#eee; border:none; border-radius:4px; cursor:pointer; font-weight:bold; transition: all 0.3s ease-in-out; }
        .logout-btn:hover { background: #555; transform: translateY(-2px); }
        .back-btn { background: #f8ca00; padding: 8px 15px; color:#111; border:none; border-radius:4px; cursor:pointer; font-weight:bold; transition: all 0.3s ease-in-out; }
        .back-btn:hover { background: #e0b600; transform: translateY(-2px); }

        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-pending  { background: rgba(240,173,78,0.2);  color: #f0ad4e; border: 1px solid #f0ad4e; }
        .status-approved { background: rgba(40,167,69,0.2);   color: #28a745; border: 1px solid #28a745; }
        .status-rejected { background: rgba(217,83,79,0.2);   color: #d9534f; border: 1px solid #d9534f; }

        .desc-text { font-size: 12px; color: #888; font-style: italic; margin-top: 4px; }
        .btn-view-map { background: transparent; border: 1px solid #f8ca00; color: #f8ca00; padding: 5px 10px; border-radius: 10px; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.3s ease-in-out; }
        .btn-view-map:hover { background: #f8ca00; color: #111; transform: scale(1.05); }

        .live-indicator { font-size: 11px; color: #555; margin-top: 4px; }
        .live-indicator span { color: #28a745; font-weight: bold; }
    </style>
</head>
<body>

<div class="header-logo">
    <img src="{{ asset('images/Rastra_Sewakottama.png') }}" alt="Logo Polri">
</div>

<div class="container">
    <div class="top-menu">
        <button class="back-btn" onclick="window.location.href='/map'">Kembali ke Peta</button>
        <button class="logout-btn" onclick="logout()">Logout</button>
    </div>

    <h2>Daftar Usaha Saya</h2>
    <p class="subtitle">Pantau status persetujuan lokasi usaha yang sudah Anda daftarkan ke Yanmaps Semarang.</p>
    <p class="live-indicator">Status diperbarui otomatis setiap 15 detik &mdash; <span id="liveStatus">●</span> Live</p>

    <table id="myPlacesTable">
        <thead>
            <tr>
                <th>Detail Usaha</th>
                <th>Alamat &amp; Kontak</th>
                <th>Status di Peta</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody id="myPlacesTbody">
            <tr><td colspan="4" style="text-align:center; padding:20px; color:#aaa;">Memuat data...</td></tr>
        </tbody>
    </table>
</div>

{{-- Hidden form for Laravel logout --}}
<form id="logout-form" action="/logout" method="POST" style="display: none;">
    @csrf
</form>

<script>
    let token = localStorage.getItem('token');
    let role  = localStorage.getItem('user_role');

    if (!token || role !== 'owner') {
        window.location.href = '/login';
    }

    function renderPlaceRow(place) {
        let statusClass = 'status-pending';
        let statusText  = '⏳ Menunggu Persetujuan';

        if (place.status === 'approved') {
            statusClass = 'status-approved';
            statusText  = '✅ Sudah Tampil';
        } else if (place.status === 'rejected') {
            statusClass = 'status-rejected';
            statusText  = '❌ Ditolak';
        }

        let desc  = place.description || '<em style="color:#666">Tidak ada deskripsi</em>';
        let phone = place.phone || '-';
        let lat   = place.latitude;
        let lng   = place.longitude;
        let mapBtn = (lat && lng)
            ? `<button class="btn-view-map" onclick="viewOnMap(${lat}, ${lng})">📍 Lihat di Peta</button>`
            : '';
            
        let rejectInfo = (place.status === 'rejected' && place.reject_reason) 
            ? `<div style="color: #d9534f; font-size: 11px; margin-top: 5px; font-weight: bold;">Alasan: ${place.reject_reason}</div>`
            : '';

        return `
            <tr>
                <td>
                    <strong style="color:#f8ca00; font-size:15px;">${place.name}</strong>
                    <div class="desc-text">${desc}</div>
                </td>
                <td>
                    ${place.address || '-'}<br>
                    <span style="color:#aaa; font-size:12px;">📞 ${phone}</span>
                </td>
                <td>
                    <span class="status-badge ${statusClass}">${statusText}</span>
                    ${rejectInfo}
                </td>
                <td>${mapBtn}</td>
            </tr>
        `;
    }

    function loadMyPlaces() {
        // Flash live indicator green
        document.getElementById('liveStatus').style.color = '#f8ca00';
        setTimeout(() => { document.getElementById('liveStatus').style.color = '#28a745'; }, 500);

        fetch('/api/my-places', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json'
            }
        })
        .then(res => {
            if (res.status === 401) { logout(); return null; }
            return res.json();
        })
        .then(data => {
            if (!data) return;
            let tbody = document.getElementById('myPlacesTbody');

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; padding:20px; color:#aaa;">Belum ada usaha yang didaftarkan. Ayo tambahkan di Peta!</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(renderPlaceRow).join('');
        })
        .catch(err => console.error("Error fetching places:", err));
    }

    window.viewOnMap = function(lat, lng) {
        window.location.href = '/map?lat=' + lat + '&lng=' + lng + '&zoom=17';
    };

    window.logout = function() {
        localStorage.clear();
        document.getElementById('logout-form').submit();
    };

    // Initial load
    loadMyPlaces();

    // Auto-refresh every 15 seconds so status changes (approve/reject) appear automatically
    setInterval(loadMyPlaces, 15000);
</script>
</body>
</html>