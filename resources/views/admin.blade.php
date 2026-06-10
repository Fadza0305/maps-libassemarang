<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - Yanmaps Semarang</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #121212; padding: 20px; color: #eee; margin: 0; }
        .header-logo { text-align: center; margin-bottom: 20px; }
        .header-logo img { height: 60px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
        .container { background: #1e1e1e; padding: 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); max-width: 1000px; margin: auto; border: 1px solid #333; }
        h2 { color: #f8ca00; margin-top: 0; }
        p { color: #aaa; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { border: 1px solid #444; padding: 12px; text-align: left; }
        th { background-color: #2d2d2d; color: #f8ca00; font-weight: bold; }
        tr:nth-child(even) { background-color: #242424; }
        
        .btn-approve { background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; font-weight: bold; transition: all 0.3s ease-in-out; }
        .btn-approve:hover { background: #218838; transform: translateY(-2px); }
        .btn-reject { background: #f0ad4e; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; margin-right: 5px; font-weight: bold; transition: all 0.3s ease-in-out; }
        .btn-reject:hover { background: #ec971f; transform: translateY(-2px); }
        .btn-delete { background: #d9534f; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-weight: bold; transition: all 0.3s ease-in-out; }
        .btn-delete:hover { background: #c9302c; transform: translateY(-2px); }
        
        /* CSS untuk menu tombol di kanan atas */
        .top-menu { float: right; display: flex; gap: 10px; }
        .logout-btn { background: #444; padding: 8px 15px; color:#eee; border:none; border-radius:4px; cursor:pointer; font-weight:bold; transition: all 0.3s ease-in-out; }
        .logout-btn:hover { background: #555; transform: translateY(-2px); }
        .back-btn { background: #f8ca00; padding: 8px 15px; color:#111; border:none; border-radius:4px; cursor:pointer; font-weight:bold; transition: all 0.3s ease-in-out; }
        .back-btn:hover { background: #e0b600; transform: translateY(-2px); }

        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; }
        .status-pending { background: rgba(240, 173, 78, 0.2); color: #f0ad4e; border: 1px solid #f0ad4e; }
        .status-approved { background: rgba(40, 167, 69, 0.2); color: #28a745; border: 1px solid #28a745; }
        .status-rejected { background: rgba(217, 83, 79, 0.2); color: #d9534f; border: 1px solid #d9534f; }
        
        .desc-text { font-size: 12px; color: #888; font-style: italic; margin-top: 4px; }
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

    <h2>Dashboard Admin</h2>
    <p>Kelola semua lokasi usaha yang didaftarkan oleh pengguna.</p>

    <table id="placesTable">
        <thead>
            <tr>
                <th>Nama Usaha & Deskripsi</th>
                <th>Alamat & Kontak</th>
                <th>Nama Pemilik (Akun Terdaftar)</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>

    <h2 style="margin-top: 40px;">Manajemen Pengguna</h2>
    <table id="usersTable">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

{{-- Hidden form for Laravel logout --}}
<form id="logout-form" action="/logout" method="POST" style="display: none;">
    @csrf
</form>

<script>
    let token = localStorage.getItem('token');
    let role = localStorage.getItem('user_role');

    if (role !== 'admin' || !token) {
        window.location.href = '/login';
    }

    function loadAllPlaces() {
        fetch('/api/places', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        })
        .then(res => {
            if(res.status === 401) {
                logout();
                return;
            }
            return res.json();
        })
        .then(data => {
            if(!data) return;
            let tbody = document.querySelector('#placesTable tbody');
            tbody.innerHTML = '';

            data.forEach(place => {
                let statusClass = 'status-pending';
                if(place.status === 'approved') statusClass = 'status-approved';
                if(place.status === 'rejected') statusClass = 'status-rejected';
                
                let desc = place.description || 'Tidak ada deskripsi';
                let phone = place.phone || '-';

                let actionButtons = '';
                actionButtons += `<button class="btn-approve" style="background:#17a2b8;" onclick="window.open('/map?lat=${place.latitude}&lng=${place.longitude}', '_blank')">📍 Lihat di Peta</button>`;
                
                if(place.status === 'pending') {
                    actionButtons += `<button class="btn-approve" onclick="approvePlace(${place.id})">Setujui</button>`;
                    actionButtons += `<button class="btn-reject" onclick="rejectPlace(${place.id})">Tolak</button>`;
                }
                actionButtons += `<button class="btn-delete" onclick="deletePlace(${place.id})">Hapus</button>`;

                let ownerName  = place.user ? place.user.name  : '<em style="color:#888;">Tidak diketahui</em>';
                let ownerEmail = place.user ? place.user.email : '-';

                let tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <strong style="color:#f8ca00; font-size:15px;">${place.name}</strong>
                        <div class="desc-text">${desc}</div>
                    </td>
                    <td>
                        ${place.address}<br>
                        <span style="color:#aaa; font-size:12px;">📞 ${phone}</span>
                    </td>
                    <td>
                        <strong style="color:#eee;">${ownerName}</strong><br>
                        <span style="font-size:12px; color:#aaa;">✉️ ${ownerEmail}</span>
                    </td>
                    <td><span class="status-badge ${statusClass}">${place.status.toUpperCase()}</span></td>
                    <td>${actionButtons}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error("Error loading places:", err));
    }

    window.approvePlace = function(id) {
        if(!confirm("Setujui tempat ini?")) return;
        fetch('/api/places/' + id + '/approve', {
            method: 'POST',
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        })
        .then(() => { alert("Berhasil disetujui!"); loadAllPlaces(); });
    }

    window.rejectPlace = function(id) {
        let reason = prompt("Masukkan alasan penolakan:");
        if(reason === null) return;
        fetch('/api/places/' + id + '/reject', {
            method: 'POST',
            headers: { 
                'Authorization': 'Bearer ' + token, 
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(() => { alert("Pengajuan ditolak."); loadAllPlaces(); });
    }

    window.deletePlace = async function(id) {
        if(!confirm("Hapus tempat ini secara permanen?")) return;
        
        try {
            let response = await fetch('/api/places/' + id, {
                method: 'DELETE',
                headers: { 
                    'Authorization': 'Bearer ' + token, 
                    'Accept': 'application/json'
                }
            });

            if(response.ok) {
                alert("Tempat berhasil dihapus!");
                loadAllPlaces();
            } else {
                let errorData = await response.text(); 
                console.error("ALASAN GAGAL HAPUS DARI LARAVEL:", errorData);
                alert("Gagal menghapus! Buka tab Console (Inspect Element) untuk melihat penyebab errornya.");
            }
        } catch (error) {
            console.error("Network Error:", error);
        }
    }

    window.logout = function() {
        localStorage.clear();
        document.getElementById('logout-form').submit();
    };

    function loadUsers() {
        fetch('/api/users', {
            headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if(!data) return;
            let tbody = document.querySelector('#usersTable tbody');
            tbody.innerHTML = '';
            data.forEach(user => {
                let tr = document.createElement('tr');
                let deleteBtn = user.role !== 'admin' ? `<button class="btn-delete" onclick="deleteUser(${user.id})">Hapus</button>` : '';
                tr.innerHTML = `
                    <td>${user.name}</td>
                    <td>${user.email}</td>
                    <td><span class="status-badge ${user.role === 'admin' ? 'status-approved' : 'status-pending'}">${user.role.toUpperCase()}</span></td>
                    <td>${deleteBtn}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => console.error("Error loading users:", err));
    }

    window.deleteUser = async function(id) {
        if(!confirm("Hapus pengguna ini?")) return;
        try {
            let response = await fetch('/api/users/' + id, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' }
            });
            if(response.ok) {
                alert("Pengguna berhasil dihapus!");
                loadUsers();
            } else {
                alert("Gagal menghapus pengguna.");
            }
        } catch (error) {
            console.error("Error:", error);
        }
    }

    loadAllPlaces();
    loadUsers();
</script>
</body>
</html>