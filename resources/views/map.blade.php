<!DOCTYPE html>
<html>
<head>
    <title>Yanmaps Semarang - Command Center Polrestabes</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
    <style>
        /* =======================================
           DESAIN UI/UX MODERN POLICE THEME (DARK)
           ======================================= */
        body, html { margin: 0; padding: 0; height: 100%; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden; background-color: #121212; }
        #map { height: 100vh; width: 100vw; }

        /* 1. Header Container */
        .header-container { position: absolute; top: 15px; left: 50%; transform: translateX(-50%); width: 90%; max-width: 1000px; z-index: 1000; display: flex; flex-direction: column; gap: 10px; }
        .header-row { display: flex; justify-content: space-between; align-items: center; background: rgba(30, 30, 30, 0.95); padding: 10px 20px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 1px solid #333; backdrop-filter: blur(5px); }
        .header-left input { padding: 8px 12px; border: 1px solid #444; border-radius: 4px; font-size: 14px; width: 200px; background: #2d2d2d; color: #eee; outline: none; }
        .header-left input:focus { border-color: #f8ca00; }
        
        .header-middle { display: flex; align-items: center; justify-content: center; gap: 12px; }
        .header-middle img { height: 45px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
        .header-middle h2 { margin: 0; font-size: 20px; color: #f8ca00; text-align: left; font-weight: bold; line-height: 1.2; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); }
        .header-middle h2 span { font-size: 13px; color: #ccc; font-weight: normal; }
        
        .header-right { display: flex; gap: 10px; }
        
        .btn { padding: 8px 16px; border: none; border-radius: 24px; cursor: pointer; font-weight: bold; font-size: 14px; box-shadow: 0 2px 5px rgba(0,0,0,0.3); transition: all 0.3s ease-in-out; }
        .btn-primary { background: #f8ca00; color: #111; }
        .btn-primary:hover { background: #e0b600; transform: translateY(-2px); }
        .btn-danger { background: #d9534f; color: white; }
        .btn-danger:hover { background: #c9302c; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; transition: all 0.3s ease-in-out; }

        /* 2. Tombol Filter (Atas Menyamping) */
        .top-filters { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
        .filter-pill { background: rgba(45, 45, 45, 0.9); border: 1px solid #444; padding: 8px 16px; border-radius: 24px; cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease-in-out; color: #eee; }
        .filter-pill:hover { background: #3d3d3d; transform: translateY(-2px); }
        .filter-pill.active { background: #f8ca00; border-color: #f8ca00; color: #111; box-shadow: 0 0 10px rgba(248, 202, 0, 0.5); }

        /* 3. Sidebar Kiri */
        .sidebar { position: absolute; top: 0; left: -400px; width: 350px; height: 100vh; background: #1e1e1e; z-index: 1001; box-shadow: 4px 0 15px rgba(0,0,0,0.5); transition: left 0.4s ease-in-out, opacity 0.4s ease-in-out; display: flex; flex-direction: column; border-right: 1px solid #333; opacity: 0; visibility: hidden; }
        .sidebar.open { left: 0; opacity: 1; visibility: visible; }
        .sidebar-header { padding: 15px 20px; border-bottom: 1px solid #333; display: flex; justify-content: space-between; align-items: center; background: #1e1e1e; }
        .sidebar-header h3 { margin: 0; font-size: 18px; color: #f8ca00; }
        .close-btn { background: none; border: none; font-size: 20px; cursor: pointer; color: #aaa; transition: all 0.3s ease-in-out; }
        .close-btn:hover { color: #fff; transform: scale(1.1); }
        .sidebar-content { padding: 20px; overflow-y: auto; flex-grow: 1; background: #121212; }
        
        /* Kartu Hasil di Sidebar */
        .result-card { background: #2d2d2d; padding: 15px; border-radius: 8px; margin-bottom: 10px; border: 1px solid #444; box-shadow: 0 2px 4px rgba(0,0,0,0.2); transition: all 0.3s ease-in-out; opacity: 1; visibility: visible; }
        .result-card.hidden { opacity: 0; visibility: hidden; position: absolute; pointer-events: none; }
        .result-card h4 { margin: 0 0 5px 0; color: #f8ca00; }
        .result-card p { margin: 0; font-size: 13px; color: #bbb; }
        .result-card .desc { font-size: 12px; color: #999; margin-top: 5px; font-style: italic; }

        /* 4. Radius & Zoom (Pojok Kanan Bawah) */
        .bottom-right-controls { position: absolute; bottom: 20px; right: 20px; z-index: 1000; display: flex; align-items: flex-end; gap: 15px; }
        .radius-box { background: rgba(30, 30, 30, 0.9); padding: 10px 15px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.4); text-align: center; margin-bottom: 10px; border: 1px solid #444; }
        
        /* Custom Location Button */
        .btn-location { background: #2d2d2d; color: #f8ca00; height: 48px; width: 48px; border-radius: 50%; padding: 0; display: flex; align-items: center; justify-content: center; border: 2px solid #f8ca00; margin-bottom: 10px; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.5); transition: all 0.3s ease-in-out; }
        .btn-location:hover { background: #3d3d3d; transform: scale(1.1); }

        /* Leaflet Popups Dark Theme */
        .leaflet-popup { transition: opacity 0.3s ease-in-out; }
        .leaflet-popup-content-wrapper, .leaflet-popup-tip { background: #1e1e1e !important; color: #eee !important; border: 1px solid #444; box-shadow: 0 4px 15px rgba(0,0,0,0.5) !important; }
        .leaflet-popup-content { margin: 15px; font-family: 'Segoe UI', sans-serif; }
        .leaflet-popup-close-button { color: #aaa !important; }
        
        .leaflet-control-zoom { margin-bottom: 0 !important; border: none !important; box-shadow: 0 4px 10px rgba(0,0,0,0.4) !important; border-radius: 8px !important; overflow: hidden; }
        .leaflet-control-zoom a { background-color: #2d2d2d !important; color: #eee !important; border-color: #444 !important; }
        .leaflet-control-zoom a:hover { background-color: #3d3d3d !important; }
        
        /* Form Inputs in Popup */
        .popup-form input, .popup-form select, .popup-form textarea { width: 100%; margin-bottom: 8px; padding: 6px; box-sizing: border-box; background: #2d2d2d; border: 1px solid #555; color: #eee; border-radius: 4px; }
        .popup-form input:focus, .popup-form select:focus, .popup-form textarea:focus { outline: none; border-color: #f8ca00; }
        .popup-form label { font-size: 12px; font-weight: bold; color: #aaa; display: block; margin-bottom: 2px; }

        /* Custom Marker & Tooltip */
        .custom-dark-tooltip {
            background: #1a1a1a;
            color: #ffffff;
            border: 1px solid #444;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            white-space: nowrap;
        }
        .custom-dark-tooltip::before {
            display: none;
        }

        .custom-marker-container {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            background: transparent !important;
            border: none !important;
        }
        .marker-avatar {
            width: 40px;
            height: 40px;
            object-fit: contain;
            position: absolute;
            bottom: 5px;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.6));
            z-index: 2;
        }
        .glow-base {
            position: absolute;
            bottom: 0;
            width: 24px;
            height: 10px;
            border-radius: 50%;
            z-index: 1;
        }
        .glow-blue { background: rgba(0, 195, 255, 0.4); box-shadow: 0 0 15px 5px rgba(0, 195, 255, 0.8); }
        .glow-red { background: rgba(255, 50, 50, 0.4); box-shadow: 0 0 15px 5px rgba(255, 50, 50, 0.8); }
        .glow-yellow { background: rgba(255, 215, 0, 0.4); box-shadow: 0 0 15px 5px rgba(255, 215, 0, 0.8); }
        .glow-orange { background: rgba(255, 140, 0, 0.4); box-shadow: 0 0 15px 5px rgba(255, 140, 0, 0.8); }
        .glow-green { background: rgba(50, 255, 50, 0.4); box-shadow: 0 0 15px 5px rgba(50, 255, 50, 0.8); }
        .glow-gray { background: rgba(200, 200, 200, 0.4); box-shadow: 0 0 15px 5px rgba(200, 200, 200, 0.8); }
    </style>
</head>
<body>

<div class="header-container">
    <div class="header-row">
        <div class="header-left" style="display:flex; gap:6px; align-items:center;">
            <input type="text" id="searchInput" placeholder="Cari lokasi di peta..." style="width:220px;" onkeypress="if(event.key==='Enter') runSearch();">
            <button onclick="runSearch()" style="padding:8px 12px; background:#f8ca00; border:none; border-radius:4px; cursor:pointer; font-weight:bold; color:#111; font-size:13px; transition: all 0.3s ease-in-out; white-space:nowrap;" onmouseover="this.style.background='#e0b600';" onmouseout="this.style.background='#f8ca00';">🔍 Cari</button>
        </div>
        <div class="header-middle">
            <img src="{{ asset('images/Rastra_Sewakottama.png') }}" alt="Logo Polri">
            <h2>Yanmaps Semarang<br><span>Command Center Polrestabes</span></h2>
        </div>
        <div class="header-right">
            @if(auth()->check())
                @if(auth()->user()->role == 'admin')
                    <button class="btn btn-primary" onclick="window.location.href='/admin/dashboard'">Dashboard Admin</button>
                @elseif(auth()->user()->role == 'owner')
                    <button class="btn btn-primary" onclick="window.location.href='/owner/dashboard'">Dashboard Owner</button>
                @else
                    <button class="btn btn-primary" onclick="window.location.href='/dashboard'">Dashboard</button>
                @endif
                <button id="logoutBtn" class="btn btn-danger" onclick="logout()">Logout</button>
            @else
                <button class="btn btn-primary" onclick="window.location.href='/login'">Login</button>
            @endif
        </div>
    </div>
    
    <div class="top-filters">
        <button class="filter-pill" id="btn-nearby" onclick="showNearby()" style="border-color:#f8ca00; color:#f8ca00;">📡 Terdekat</button>
        <button class="filter-pill" id="btn-police" onclick="toggleFilter('police', '🚓 Kantor Polisi')">🚓 Kantor Polisi</button>
        <button class="filter-pill" id="btn-hospital" onclick="toggleFilter('hospital', '🏥 Rumah Sakit')">🏥 Rumah Sakit</button>
        <button class="filter-pill" id="btn-SPBU" onclick="toggleFilter('SPBU', '⛽ SPBU')">⛽ SPBU</button>
        <button class="filter-pill" id="btn-workshop" onclick="toggleFilter('workshop', '🔧 Bengkel')">🔧 Bengkel</button>
        <button class="filter-pill" id="btn-tourism" onclick="toggleFilter('tourism', '🏞️ Pariwisata')">🏞️ Pariwisata</button>
        <button class="filter-pill" id="btn-restaurant" onclick="toggleFilter('restaurant', '🍴 Restoran')">🍴 Restoran</button>
    </div>
</div>

{{-- Hidden form for Laravel logout (requires POST + CSRF) --}}
<form id="logout-form" action="/logout" method="POST" style="display: none;">
    @csrf
</form>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3 id="sidebar-title">Kategori</h3>
        <button class="close-btn" onclick="closeSidebar()">✖</button>
    </div>
    <div class="sidebar-content" id="sidebar-content">
        <p style="text-align:center; color:#888; margin-top:20px;">Memuat data...</p>
    </div>
</div>

<div id="routeInfoPanel" style="display:none; position: absolute; top: 130px; left: 50%; transform: translateX(-50%); background: rgba(30,30,30,0.95); padding: 15px 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); border: 1px solid #444; z-index: 1000; text-align: center; backdrop-filter: blur(5px);">
    <h3 style="margin: 0 0 10px 0; color: #007bff; font-size: 16px;">Informasi Rute</h3>
    <p style="margin: 0 0 5px 0; color: #eee; font-size: 14px;">Jarak: <span id="routeDistance" style="font-weight:bold; color:#f8ca00;">-</span></p>
    <p style="margin: 0 0 15px 0; color: #eee; font-size: 14px;">Estimasi Waktu: <span id="routeTime" style="font-weight:bold; color:#28a745;">-</span></p>
    <button id="googleMapsBtn" style="background: #4285F4; color: white; border: none; padding: 8px 16px; border-radius: 24px; cursor: pointer; font-weight: bold; font-size: 13px; transition: all 0.3s ease;" onmouseover="this.style.background='#3367d6';" onmouseout="this.style.background='#4285F4';">
        🗺️ Mulai di Google Maps
    </button>
</div>

<div id="map"></div>

<div class="bottom-right-controls" id="bottomControls">
    <button class="btn-location" onclick="getCurrentLocation()" title="Lokasi Saya">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="3 11 22 2 13 21 11 13 3 11"></polygon>
        </svg>
    </button>
    
    {{-- Cancel Route Button (hidden by default) --}}
    <button id="cancelRouteBtn" onclick="cancelRoute()" title="Batalkan Rute" style="display:none; background:#d9534f; color:white; height:48px; width:48px; border-radius:50%; border:2px solid #c9302c; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 4px 10px rgba(0,0,0,0.5); margin-bottom:10px; font-size:20px; transition: all 0.3s ease-in-out;" onmouseover="this.style.transform='scale(1.1)';" onmouseout="this.style.transform='scale(1)';" title="Batalkan Rute">
        ✖
    </button>

    <div class="radius-box">
        <label style="font-size:12px; font-weight:bold; color:#ccc;">Radius: <span id="radiusValue" style="color:#f8ca00;">10</span> km</label><br>
        <input type="range" id="radiusSlider" min="2" max="20" value="10" style="width: 100px; margin-top:5px; accent-color: #f8ca00;" />
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
// ==========================================
// A. INISIALISASI PETA & ZOOM
// ==========================================
var centerLat = -6.9932;
var centerLng = 110.4203;

var map = L.map('map', { zoomControl: false }).setView([centerLat, centerLng], 13);

let currentLat = -7.005145;
let currentLng = 110.438126;
let userLat = null;
let userLng = null;

// Menggunakan Dark Theme Map Layer
L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
}).addTo(map);

L.control.zoom({ position: 'bottomright' }).addTo(map);

// Pan to ?lat=&lng=&zoom= if coming from Owner Dashboard "Lihat di Peta"
(function() {
    const params = new URLSearchParams(window.location.search);
    const pLat  = parseFloat(params.get('lat'));
    const pLng  = parseFloat(params.get('lng'));
    const pZoom = parseInt(params.get('zoom')) || 17;
    if (!isNaN(pLat) && !isNaN(pLng)) {
        map.setView([pLat, pLng], pZoom);
        L.circleMarker([pLat, pLng], { color:'#f8ca00', fillColor:'#f8ca00', fillOpacity:1, radius:10, weight:2 })
            .addTo(map)
            .bindPopup('<div style="color:#f8ca00; font-weight:bold;">📍 Lokasi Usaha Anda</div>')
            .openPopup();
    }
})();

// ==========================================
// B. RADIUS & LINGKARAN
// ==========================================
var radiusKm = 10;
var circle = L.circle([centerLat, centerLng], {
    color: '#f8ca00', fillColor: '#f8ca00', fillOpacity: 0.05, radius: radiusKm * 1000, weight: 1
}).addTo(map);

document.getElementById("radiusSlider").addEventListener("input", function () {
    radiusKm = this.value;
    document.getElementById("radiusValue").innerText = radiusKm;
    circle.setRadius(radiusKm * 1000);
});

// ==========================================
// C. LOGIKA UI & MESIN PENYEDOT OSM
// ==========================================
let activeFilter = null;
let osmLayer = L.layerGroup().addTo(map); 
let currentMapMarkers = []; // Simpan referensi marker untuk fitur search front-end

function createCustomMarker(imageFile, glowClass) {
    return L.divIcon({
        className: 'custom-marker-container',
        html: `
            <img src="{{ asset('images') }}/${imageFile}" class="marker-avatar">
            <div class="glow-base ${glowClass}"></div>
        `,
        iconSize: [40, 45],
        iconAnchor: [20, 45],
        popupAnchor: [0, -45],
        tooltipAnchor: [0, 5]
    });
}

const customIcons = {
    'police': createCustomMarker('icon-polisi.png', 'glow-blue'),
    'hospital': createCustomMarker('hospital.png', 'glow-red'),
    'SPBU': createCustomMarker('SPBU.png', 'glow-yellow'),
    'workshop': createCustomMarker('bengkel.png', 'glow-gray'),
    'tourism': createCustomMarker('pariwisata.png', 'glow-green'),
    'restaurant': createCustomMarker('restaurant.png', 'glow-orange')
};

const osmTags = {
    'police': '["amenity"="police"]',
    'hospital': '["amenity"="hospital"]',
    'SPBU': '["amenity"="fuel"]',
    'workshop': '["shop"~"car_repair|motorcycle_repair"]',
    'tourism': '["tourism"]',
    'restaurant': '["amenity"="restaurant"]'
};

function renderLocalPlace(place, category) {
    let lat = place.latitude;
    let lon = place.longitude;
    let name = place.name;
    let address = place.address || "Kota Semarang";
    let desc = place.description || "Tidak ada deskripsi.";
    let distance = parseFloat(place.distance_km || 0).toFixed(2);
    
    let selectedIcon = customIcons[category] || customIcons['police'];
    let marker = L.marker([lat, lon], {icon: selectedIcon});
    marker.titleName = name.toLowerCase();
    marker.addTo(osmLayer);
    currentMapMarkers.push(marker);
    
    marker.bindTooltip(name, {permanent: true, direction: 'bottom', className: 'custom-dark-tooltip'});

    marker.bindPopup(`
        <div style="min-width: 220px;">
            <h4 style="margin: 0 0 5px 0; color: #f8ca00; font-size: 16px;">⭐ ${name}</h4>
            <p style="margin: 0 0 5px 0; font-size: 12px; color: #ccc;">📍 ${address}</p>
            ${place.phone ? '<p style="margin: 0 0 5px 0; font-size: 12px; color: #ccc;">📞 ' + place.phone + '</p>' : ''}
            <p style="margin: 0 0 10px 0; font-size: 12px; color: #eee; font-style: italic;">"${desc}"</p>
            <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #28a745;">Jarak: ${distance} km</p>
            <button onclick="makeRoute(${lat}, ${lon})" style="background: #f8ca00; border: none; color: #111; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; width: 100%; transition: all 0.3s ease;">
                🚗 Rute ke Sini
            </button>
        </div>
    `);

    return `
        <div class="result-card" style="border-left: 4px solid #f8ca00;">
            <h4 onclick="map.setView([${lat}, ${lon}], 17)" style="margin-bottom: 2px; cursor:pointer; transition: color 0.3s ease;">⭐ ${name}</h4>
            <p style="font-size: 12px;">Jarak: ${distance} km — ${address}</p>
            <p class="desc">${desc.substring(0,50)}${desc.length>50?'...':''}</p>
            <div style="margin-top: 10px; display: flex; gap: 10px;">
                <button onclick="map.setView([${lat}, ${lon}], 17)" style="background: transparent; border: 1px solid #f8ca00; color: #f8ca00; padding: 5px 10px; border-radius: 15px; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.background='#f8ca00'; this.style.color='#111';" onmouseout="this.style.background='transparent'; this.style.color='#f8ca00';">
                    📍 Peta
                </button>
                <button onclick="makeRoute(${lat}, ${lon})" style="background: #f8ca00; border: none; color: #111; padding: 5px 10px; border-radius: 15px; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                    🚗 Rute
                </button>
            </div>
        </div>
    `;
}

window.toggleFilter = function(category, title) {
    let sidebar = document.getElementById('sidebar');
    let content = document.getElementById('sidebar-content');
    
    if (activeFilter === category) {
        closeSidebar();
        return;
    }

    document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btn-' + category).classList.add('active');
    document.getElementById('sidebar-title').innerText = title;
    sidebar.classList.add('open');
    activeFilter = category;

    // DO NOT completely clear OSM layer before fetching.
    // Show loading state while keeping old markers visible until new data arrives.
    let oldContent = content.innerHTML;
    content.innerHTML = '<p style="text-align:center; color:#f8ca00; margin-top:20px; font-weight:bold;">Tunggu Sebentar...</p>' + oldContent;
    
    currentLat = userLat || centerLat;
    currentLng = userLng || centerLng;
    
    let localApiUrl = '/api/places/nearby?category=' + category + '&user_lat=' + currentLat + '&user_lng=' + currentLng;
    let tag = osmTags[category]; 
    let radiusMeters = radiusKm * 1000; 
    let overpassQuery = '[out:json];nwr' + tag + '(around:' + radiusMeters + ',' + centerLat + ',' + centerLng + ');out center;';
    let osmUrl = "https://overpass-api.de/api/interpreter?data=" + encodeURIComponent(overpassQuery);

    Promise.all([
        fetch(localApiUrl).then(res => res.json()).catch(err => []), 
        fetch(osmUrl).then(response => response.json()).catch(err => ({elements: []})) 
    ])
    .then(([localData, osmData]) => {
        // Clear layers only AFTER data arrives to prevent screen flash
        osmLayer.clearLayers();
        currentMapMarkers = [];
        let htmlContent = ''; 

        if (localData && localData.length > 0) {
            htmlContent += '<h4 style="margin: 10px 0; color: #f8ca00; border-bottom: 1px solid #444; padding-bottom: 5px;">Data Resmi</h4>';
            localData.forEach(place => {
                htmlContent += renderLocalPlace(place, category);
            });
        }

        let elements = osmData.elements || [];
        if (elements.length > 0) {
            htmlContent += '<h4 style="margin: 15px 0 10px 0; color: #aaa; border-bottom: 1px solid #444; padding-bottom: 5px;">Data Publik</h4>';
            
            elements.forEach(place => {
                let lat = place.lat || (place.center && place.center.lat);
                let lon = place.lon || (place.center && place.center.lon);
                if (!lat || !lon) { place.distance = 9999; return; }

                let R = 6371; 
                let dLat = (lat - currentLat) * Math.PI / 180;
                let dLon = (lon - currentLng) * Math.PI / 180;
                let a = Math.sin(dLat/2) * Math.sin(dLat/2) + Math.cos(currentLat * Math.PI / 180) * Math.cos(lat * Math.PI / 180) * Math.sin(dLon/2) * Math.sin(dLon/2);
                let c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                place.distance = R * c;
            });

            elements.sort((a, b) => a.distance - b.distance);

            elements.forEach(place => {
                let lat = place.lat || (place.center && place.center.lat);
                let lon = place.lon || (place.center && place.center.lon);
                if (!lat || !lon) return; 

                let name = place.tags.name || title;
                let street = place.tags["addr:street"] || "";
                let number = place.tags["addr:housenumber"] || "";
                let city = place.tags["addr:city"] || "";
                let fullAddress = street ? street + ' ' + (number ? 'No. '+number : '') + ', ' + city : "Kota Semarang";
                let distance = place.distance.toFixed(2);

                let selectedIcon = customIcons[category];
                let marker = L.marker([lat, lon], {icon: selectedIcon});
                marker.titleName = name.toLowerCase();
                marker.addTo(osmLayer);
                currentMapMarkers.push(marker);
                
                marker.bindTooltip(name, {permanent: true, direction: 'bottom', className: 'custom-dark-tooltip'});

                marker.bindPopup(`
                    <div style="min-width: 200px;">
                        <h4 style="margin: 0 0 5px 0; color: #fff; font-size: 15px;">${name}</h4>
                        <p style="margin: 0 0 5px 0; font-size: 12px; color: #ccc;">📍 ${fullAddress}</p>
                        <p style="margin: 0 0 10px 0; font-size: 13px; font-weight: bold; color: #28a745;">Jarak: ${distance} km</p>
                        <button onclick="makeRoute(${lat}, ${lon})" style="background: #f8ca00; border: none; color: #111; padding: 6px 12px; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: bold; width: 100%; transition: all 0.3s ease;">
                            🚗 Rute ke Sini
                        </button>
                    </div>
                `);

                htmlContent += `
                    <div class="result-card">
                        <h4 onclick="map.setView([${lat}, ${lon}], 17)" style="margin-bottom: 2px; cursor:pointer; color: #eee; transition: color 0.3s ease;">${name}</h4>
                        <p style="font-size: 12px;">Jarak: ${distance} km — ${fullAddress}</p>
                        
                        <div style="margin-top: 10px; display: flex; gap: 10px;">
                            <button onclick="map.setView([${lat}, ${lon}], 17)" style="background: transparent; border: 1px solid #777; color: #ccc; padding: 5px 10px; border-radius: 15px; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.background='#777'; this.style.color='#fff';" onmouseout="this.style.background='transparent'; this.style.color='#ccc';">
                                📍 Peta
                            </button>
                            <button onclick="makeRoute(${lat}, ${lon})" style="background: #444; border: none; color: white; padding: 5px 10px; border-radius: 15px; cursor: pointer; font-size: 12px; font-weight: bold; transition: all 0.3s ease;" onmouseover="this.style.transform='scale(1.05)';" onmouseout="this.style.transform='scale(1)';">
                                🚗 Rute
                            </button>
                        </div>
                    </div>
                `;
            });
        }

        if (htmlContent === '') {
            content.innerHTML = '<p style="text-align:center; color:#888; margin-top:20px;">Tidak ada ' + title + ' ditemukan dalam radius ' + radiusKm + ' KM.</p>';
        } else {
            content.innerHTML = htmlContent;
        }
    })
    .catch(error => {
        content.innerHTML = '<p style="text-align:center; color:#d9534f; margin-top:20px;">Gagal mengambil data.</p>';
        console.error("Error Fetching Data:", error);
    });
};

window.closeSidebar = function() {
    document.getElementById('sidebar').classList.remove('open');
    document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('active'));
    activeFilter = null;
    osmLayer.clearLayers(); 
    currentMapMarkers = [];
};

// ==========================================
// D. PENGATURAN ROLE (LOGIN/LOGOUT)
// ==========================================
var token = localStorage.getItem('token');
var userRole = localStorage.getItem('user_role');

if (!token) {
    let loginBtn = document.getElementById('loginBtn');
    if (loginBtn) loginBtn.style.display = 'block';
} else {
    let logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) logoutBtn.style.display = 'block';
}

window.logout = function() {
    localStorage.clear();
    document.getElementById('logout-form').submit();
}

// ==========================================
// E. DETEKSI LOKASI & RUTE (NAVIGASI)
// ==========================================
let userMarker = null;
let routingControl = null;

window.getCurrentLocation = function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            userLat = position.coords.latitude;
            userLng = position.coords.longitude;

            if (userMarker) {
                map.removeLayer(userMarker);
            }

            userMarker = L.circleMarker([userLat, userLng], {
                color: '#fff',
                fillColor: '#f8ca00',
                fillOpacity: 1,
                radius: 8,
                weight: 2
            }).addTo(map);

            map.setView([userLat, userLng], 15);
            userMarker.bindPopup("<div style='color:#f8ca00; font-weight:bold;'>Lokasi Anda Saat Ini</div>").openPopup();

        }, function(error) {
            alert("Gagal mendeteksi lokasi. Pastikan GPS aktif.");
        });
    } else {
        alert("Browser Anda tidak mendukung deteksi lokasi.");
    }
};

window.makeRoute = function(destLat, destLng) {
    if (!userLat || !userLng) {
        alert("Silakan klik tombol lokasi (panah di kanan bawah) terlebih dahulu!");
        return;
    }

    if (routingControl) {
        map.removeControl(routingControl);
    }

    // Tampilkan panel dengan info default "Menghitung..." dan tombol G-Maps langsung
    document.getElementById('routeDistance').innerText = 'Menghitung...';
    document.getElementById('routeTime').innerText = 'Menghitung...';
    document.getElementById('routeInfoPanel').style.display = 'block';
    document.getElementById('cancelRouteBtn').style.display = 'flex';
    closeSidebar();

    document.getElementById('googleMapsBtn').onclick = function() {
        window.open(`https://www.google.com/maps/dir/?api=1&origin=${userLat},${userLng}&destination=${destLat},${destLng}&travelmode=driving`, '_blank');
    };

    try {
        routingControl = L.Routing.control({
            waypoints: [
                L.latLng(userLat, userLng),
                L.latLng(destLat, destLng)
            ],
            routeWhileDragging: false,
            lineOptions: {
                styles: [{color: '#007bff', opacity: 0.8, weight: 6}]
            },
            createMarker: function() { return null; },
            show: false,
            fitSelectedRoutes: true
        }).addTo(map);
        
        routingControl.on('routesfound', function(e) {
            let routes = e.routes;
            if(routes && routes.length > 0) {
                let summary = routes[0].summary;
                let dist = (summary.totalDistance / 1000).toFixed(2) + ' km';
                let timeMin = Math.round(summary.totalTime / 60);
                let timeStr = timeMin > 60 ? Math.floor(timeMin / 60) + ' jam ' + (timeMin % 60) + ' mnt' : timeMin + ' mnt';

                document.getElementById('routeDistance').innerText = dist;
                document.getElementById('routeTime').innerText = timeStr;
            }
        });

        routingControl.on('routingerror', function(e) {
            console.error('Routing Error:', e);
            document.getElementById('routeDistance').innerText = 'Gagal (Coba G-Maps)';
            document.getElementById('routeTime').innerText = 'Gagal (Coba G-Maps)';
            
            // Draw a straight fallback line if OSRM fails
            let straightLine = L.polyline([
                [userLat, userLng],
                [destLat, destLng]
            ], {color: '#007bff', opacity: 0.8, weight: 6, dashArray: '10, 10'}).addTo(map);
            
            routingControl._fallbackLine = straightLine;
            map.fitBounds(straightLine.getBounds(), {padding: [50, 50]});
        });
    } catch (err) {
        console.error("Leaflet Routing Error:", err);
    }
};

window.cancelRoute = function() {
    if (routingControl) {
        if (routingControl._fallbackLine) {
            map.removeLayer(routingControl._fallbackLine);
        }
        map.removeControl(routingControl);
        routingControl = null;
    }
    document.getElementById('cancelRouteBtn').style.display = 'none';
    document.getElementById('routeInfoPanel').style.display = 'none';
};

// ==========================================
// F. SEARCH — HYBRID (LOCAL DB + OSM NOMINATIM)
// ==========================================
window.runSearch = function() {
    const keyword = document.getElementById('searchInput').value.trim();
    if (!keyword) return;

    let sidebar   = document.getElementById('sidebar');
    let content   = document.getElementById('sidebar-content');
    let titleEl   = document.getElementById('sidebar-title');

    document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('active'));
    titleEl.innerText = '🔍 Hasil Pencarian';
    sidebar.classList.add('open');
    osmLayer.clearLayers();
    currentMapMarkers = [];
    activeFilter = 'search';

    content.innerHTML = '<p style="text-align:center; color:#f8ca00; margin-top:30px;">🔄 Mencari...</p>';

    currentLat = userLat || centerLat;
    currentLng = userLng || centerLng;

    // 1. Local DB search
    const localPromise = fetch('/api/places/search?q=' + encodeURIComponent(keyword) + '&user_lat=' + currentLat + '&user_lng=' + currentLng)
        .then(r => r.json()).catch(() => []);

    // 2. OSM Nominatim search (text geocoding around Semarang)
    const nominatimUrl = 'https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(keyword) + '&viewbox=109.95,-7.25,110.90,-6.75&bounded=1&limit=15&addressdetails=1';
    const osmPromise = fetch(nominatimUrl, { headers: { 'Accept-Language': 'id' } })
        .then(r => r.json()).catch(() => []);

    Promise.all([localPromise, osmPromise]).then(([localData, osmResults]) => {
        let htmlContent = '';

        // --- LOCAL DB results ---
        if (localData && localData.length > 0) {
            htmlContent += '<h4 style="margin:10px 0; color:#f8ca00; border-bottom:1px solid #444; padding-bottom:5px;">⭐ Data Resmi (' + localData.length + ')</h4>';
            localData.forEach(place => {
                let catName = place.category ? place.category.name.toLowerCase() : '';
                let catKey = 'police';
                if(catName.includes('sakit'))  catKey = 'hospital';
                if(catName.includes('bensin')) catKey = 'SPBU';
                if(catName.includes('bengkel')) catKey = 'workshop';
                if(catName.includes('wisata')) catKey = 'tourism';
                if(catName.includes('warung') || catName.includes('restoran')) catKey = 'restaurant';
                htmlContent += renderLocalPlace(place, catKey);
            });
        }

        // --- OSM Nominatim results ---
        if (osmResults && osmResults.length > 0) {
            htmlContent += '<h4 style="margin:15px 0 10px; color:#aaa; border-bottom:1px solid #444; padding-bottom:5px;">🗺️ Data Publik OSM (' + osmResults.length + ')</h4>';
            osmResults.forEach(item => {
                let lat = parseFloat(item.lat);
                let lon = parseFloat(item.lon);
                let name = item.display_name.split(',')[0];
                let addr = item.display_name;
                let dist = 0;
                if (userLat && userLng) {
                    let dLat = (lat - userLat) * Math.PI/180;
                    let dLon = (lon - userLng) * Math.PI/180;
                    let a = Math.sin(dLat/2)**2 + Math.cos(userLat*Math.PI/180)*Math.cos(lat*Math.PI/180)*Math.sin(dLon/2)**2;
                    dist = (6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a))).toFixed(2);
                }

                let icon = guessIconFromNominatim(item);
                let marker = L.marker([lat, lon], {icon});
                marker.titleName = name.toLowerCase();
                marker.addTo(osmLayer);
                currentMapMarkers.push(marker);

                marker.bindTooltip(name, {permanent: true, direction: 'bottom', className: 'custom-dark-tooltip'});

                marker.bindPopup(`
                    <div style="min-width:200px;">
                        <h4 style="margin:0 0 5px; color:#f8ca00;">${name}</h4>
                        <p style="margin:0 0 10px; font-size:12px; color:#ccc;">${addr}</p>
                        ${userLat ? '<p style="margin:0 0 10px; font-size:13px; font-weight:bold; color:#28a745;">Jarak: ' + dist + ' km</p>' : ''}
                        <button onclick="makeRoute(${lat}, ${lon})" style="background:#f8ca00; border:none; color:#111; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:13px; font-weight:bold; width:100%;">🚗 Rute ke Sini</button>
                    </div>
                `);

                htmlContent += `
                    <div class="result-card">
                        <h4 onclick="map.setView([${lat},${lon}],17)" style="cursor:pointer; margin-bottom:4px;">${name}</h4>
                        <p style="font-size:12px; color:#999; margin:0 0 8px;">${userLat ? '📍 ' + dist + ' km — ' : ''}${addr.split(',').slice(0,3).join(',')}</p>
                        <div style="display:flex; gap:8px;">
                            <button onclick="map.setView([${lat},${lon}],17)" style="background:transparent; border:1px solid #f8ca00; color:#f8ca00; padding:5px 10px; border-radius:12px; cursor:pointer; font-size:12px; transition:all 0.3s ease;" onmouseover="this.style.background='#f8ca00';this.style.color='#111';" onmouseout="this.style.background='transparent';this.style.color='#f8ca00';">📍 Peta</button>
                            <button onclick="makeRoute(${lat},${lon})" style="background:#444; border:none; color:white; padding:5px 10px; border-radius:12px; cursor:pointer; font-size:12px; transition:all 0.3s ease;">🚗 Rute</button>
                        </div>
                    </div>
                `;
            });
        }

        if (!htmlContent) {
            content.innerHTML = '<p style="text-align:center; color:#888; margin-top:30px;">Tidak ada hasil ditemukan untuk "' + keyword + '".</p>';
        } else {
            content.innerHTML = htmlContent;
            if (localData[0]) map.setView([localData[0].latitude, localData[0].longitude], 14);
            else if (osmResults[0]) map.setView([parseFloat(osmResults[0].lat), parseFloat(osmResults[0].lon)], 14);
        }
    });
};

// Also allow filtering sidebar cards if a filter is already active
document.getElementById('searchInput').addEventListener('input', function() {
    if (activeFilter && activeFilter !== 'search') {
        const kw = this.value.toLowerCase().trim();
        document.querySelectorAll('#sidebar-content .result-card').forEach(card => {
            const t = card.querySelector('h4');
            const txt = t ? t.innerText.toLowerCase() : '';
            card.classList.toggle('hidden', !!kw && !txt.includes(kw));
        });
        osmLayer.clearLayers();
        currentMapMarkers.forEach(m => { if (!kw || m.titleName.includes(kw)) m.addTo(osmLayer); });
    }
});

// Helper: resolve the best Leaflet icon for a Nominatim result
function guessIconFromNominatim(item) {
    const cls  = (item.class  || '').toLowerCase();
    const type = (item.type   || '').toLowerCase();
    const name = (item.display_name || '').toLowerCase();

    if (cls === 'amenity' && (type === 'hospital' || type === 'clinic' || name.includes('rumah sakit') || name.includes('klinik') || name.includes('puskesmas'))) return customIcons['hospital'];
    if (cls === 'amenity' && (type === 'fuel' || name.includes('spbu') || name.includes('pom bensin') || name.includes('pertamina'))) return customIcons['SPBU'];
    if (cls === 'amenity' && (type === 'police' || name.includes('polisi') || name.includes('polres') || name.includes('polsek'))) return customIcons['police'];
    if (cls === 'amenity' && (type === 'restaurant' || type === 'cafe' || type === 'fast_food' || name.includes('restoran') || name.includes('warung') || name.includes('makan'))) return customIcons['restaurant'];
    if ((cls === 'shop' && (type === 'car_repair' || type === 'motorcycle_repair')) || name.includes('bengkel')) return customIcons['workshop'];
    if (cls === 'tourism' || type === 'attraction' || type === 'hotel' || name.includes('wisata') || name.includes('museum') || name.includes('pantai')) return customIcons['tourism'];

    return customIcons['police']; // default fallback
}

// ==========================================
// H. NEARBY ALL — Show closest of every category
// ==========================================
window.showNearby = function() {
    if (!userLat || !userLng) {
        alert('Aktifkan lokasi Anda terlebih dahulu (klik tombol panah di kanan bawah).');
        return;
    }

    let sidebar  = document.getElementById('sidebar');
    let content  = document.getElementById('sidebar-content');
    let titleEl  = document.getElementById('sidebar-title');

    document.querySelectorAll('.filter-pill').forEach(btn => btn.classList.remove('active'));
    document.getElementById('btn-nearby').classList.add('active');
    titleEl.innerText = '📡 Tempat Terdekat';
    sidebar.classList.add('open');
    osmLayer.clearLayers();
    currentMapMarkers = [];
    activeFilter = 'nearby';

    content.innerHTML = '<p style="text-align:center; color:#f8ca00; margin-top:30px;">🔄 Mencari lokasi terdekat...</p>';

    const rad = radiusKm * 1000;
    const allTags = Object.values(osmTags).join('');
    // Query all our category amenities at once in one Overpass call
    const overpassQuery = `[out:json];
(
  nwr["amenity"="police"](around:${rad},${userLat},${userLng});
  nwr["amenity"="hospital"](around:${rad},${userLat},${userLng});
  nwr["amenity"="fuel"](around:${rad},${userLat},${userLng});
  nwr["shop"~"car_repair|motorcycle_repair"](around:${rad},${userLat},${userLng});
  nwr["amenity"="restaurant"](around:${rad},${userLat},${userLng});
  nwr["tourism"](around:${rad},${userLat},${userLng});
);
out center;`;

    const osmUrl = 'https://overpass-api.de/api/interpreter?data=' + encodeURIComponent(overpassQuery);

    // Also fetch local DB places nearby (all categories)
    const localUrl = '/api/public-places';

    Promise.all([
        fetch(osmUrl).then(r => r.json()).catch(() => ({elements:[]})),
        fetch(localUrl, { headers: { 'Accept': 'application/json' } }).then(r => r.json()).catch(() => [])
    ]).then(([osmData, localData]) => {
        let htmlContent = '';

        // Local DB — compute distance and render
        if (localData && localData.length > 0) {
            let nearby = localData
                .map(p => {
                    let dLat = (p.latitude  - userLat) * Math.PI/180;
                    let dLon = (p.longitude - userLng) * Math.PI/180;
                    let a = Math.sin(dLat/2)**2 + Math.cos(userLat*Math.PI/180)*Math.cos(parseFloat(p.latitude)*Math.PI/180)*Math.sin(dLon/2)**2;
                    p.distance_km = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return p;
                })
                .filter(p => p.distance_km <= radiusKm)
                .sort((a,b) => a.distance_km - b.distance_km)
                .slice(0, 15);

            if (nearby.length > 0) {
                htmlContent += '<h4 style="margin:10px 0; color:#f8ca00; border-bottom:1px solid #444; padding-bottom:5px;">⭐ Data Resmi Terdekat</h4>';
                nearby.forEach(p => {
                    let catName = p.category ? p.category.name.toLowerCase() : '';
                    let catKey = 'police';
                    if(catName.includes('sakit'))  catKey = 'hospital';
                    if(catName.includes('bensin')) catKey = 'SPBU';
                    if(catName.includes('bengkel')) catKey = 'workshop';
                    if(catName.includes('wisata')) catKey = 'tourism';
                    if(catName.includes('warung') || catName.includes('restoran')) catKey = 'restaurant';
                    htmlContent += renderLocalPlace(p, catKey);
                });
            }
        }

        // OSM — sort by distance, pick top 20
        let elements = (osmData.elements || [])
            .map(el => {
                let lat = el.lat || (el.center && el.center.lat);
                let lon = el.lon || (el.center && el.center.lon);
                if (!lat || !lon) return null;
                let dLat = (lat - userLat) * Math.PI/180;
                let dLon = (lon - userLng) * Math.PI/180;
                let a = Math.sin(dLat/2)**2 + Math.cos(userLat*Math.PI/180)*Math.cos(lat*Math.PI/180)*Math.sin(dLon/2)**2;
                el._dist = 6371 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                el._lat = lat; el._lon = lon;
                return el;
            })
            .filter(Boolean)
            .sort((a,b) => a._dist - b._dist)
            .slice(0, 20);

        if (elements.length > 0) {
            htmlContent += '<h4 style="margin:15px 0 10px; color:#aaa; border-bottom:1px solid #444; padding-bottom:5px;">🗺️ Tempat Publik Terdekat (OSM)</h4>';

            elements.forEach(el => {
                let lat  = el._lat;
                let lon  = el._lon;
                let dist = el._dist.toFixed(2);
                let name = (el.tags && el.tags.name) || el.tags.amenity || el.tags.shop || el.tags.tourism || 'Lokasi';

                // Determine category from tags
                let catKey = 'police';
                if (el.tags.amenity === 'hospital' || el.tags.amenity === 'clinic') catKey = 'hospital';
                if (el.tags.amenity === 'fuel') catKey = 'SPBU';
                if (el.tags.shop === 'car_repair' || el.tags.shop === 'motorcycle_repair') catKey = 'workshop';
                if (el.tags.amenity === 'restaurant' || el.tags.amenity === 'cafe') catKey = 'restaurant';
                if (el.tags.tourism) catKey = 'tourism';

                let icon = customIcons[catKey];
                let marker = L.marker([lat, lon], {icon});
                marker.titleName = name.toLowerCase();
                marker.addTo(osmLayer);
                currentMapMarkers.push(marker);

                marker.bindTooltip(name, {permanent: true, direction: 'bottom', className: 'custom-dark-tooltip'});

                marker.bindPopup(`
                    <div style="min-width:200px;">
                        <h4 style="margin:0 0 5px; color:#f8ca00;">${name}</h4>
                        <p style="margin:0 0 10px; font-size:13px; font-weight:bold; color:#28a745;">Jarak: ${dist} km</p>
                        <button onclick="makeRoute(${lat}, ${lon})" style="background:#f8ca00; border:none; color:#111; padding:6px 12px; border-radius:4px; cursor:pointer; font-size:13px; font-weight:bold; width:100%;">🚗 Rute ke Sini</button>
                    </div>
                `);

                htmlContent += `
                    <div class="result-card">
                        <h4 onclick="map.setView([${lat},${lon}],17)" style="cursor:pointer; margin-bottom:4px;">${name}</h4>
                        <p style="font-size:12px; color:#999; margin:0 0 8px;">📍 ${dist} km</p>
                        <div style="display:flex; gap:8px;">
                            <button onclick="map.setView([${lat},${lon}],17)" style="background:transparent; border:1px solid #f8ca00; color:#f8ca00; padding:5px 10px; border-radius:12px; cursor:pointer; font-size:12px; transition:all 0.3s ease;" onmouseover="this.style.background='#f8ca00';this.style.color='#111';" onmouseout="this.style.background='transparent';this.style.color='#f8ca00';">📍 Peta</button>
                            <button onclick="makeRoute(${lat},${lon})" style="background:#444; border:none; color:white; padding:5px 10px; border-radius:12px; cursor:pointer; font-size:12px; transition:all 0.3s ease;">🚗 Rute</button>
                        </div>
                    </div>
                `;
            });
        }

        content.innerHTML = htmlContent || '<p style="text-align:center; color:#888; margin-top:30px;">Tidak ada tempat ditemukan dalam radius ' + radiusKm + ' km.</p>';
    }).catch(err => {
        content.innerHTML = '<p style="text-align:center; color:#d9534f; margin-top:30px;">Gagal mengambil data terdekat.</p>';
        console.error(err);
    });
};
// ==========================================
// G. OWNER ADDS PLACE (GEOFENCING)
// ==========================================
const SEMARANG_CENTER = { lat: -7.005145, lng: 110.438126 };
const MAX_RADIUS_KM = 20;

function haversineClient(lat1, lng1, lat2, lng2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLng = (lng2 - lng1) * Math.PI / 180;
    const a = Math.sin(dLat / 2) ** 2 + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * Math.sin(dLng / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

let tempMarker = null;
let isOwner = {{ auth()->check() && auth()->user()->role == 'owner' ? 'true' : 'false' }};

if(userRole === 'owner') { isOwner = true; }

map.on('click', function (e) {
    if (!isOwner) return;

    const lat = e.latlng.lat;
    const lng = e.latlng.lng;
    
    const distance = haversineClient(lat, lng, SEMARANG_CENTER.lat, SEMARANG_CENTER.lng);

    if (distance > MAX_RADIUS_KM) {
        alert('Lokasi di luar batas wilayah Semarang (maks. 20 km).');
        return; 
    }

    if (tempMarker) map.removeLayer(tempMarker);
    tempMarker = L.marker([lat, lng]).addTo(map);

    let popupContent = `
        <div class="popup-form" style="width: 240px;">
            <h4 style="margin-top:0; color: #f8ca00; border-bottom: 1px solid #444; padding-bottom: 5px;">Tambah Tempat Baru</h4>
            <form id="addPlaceForm" onsubmit="submitPlace(event)">
                <input type="hidden" id="placeLat" value="${lat}">
                <input type="hidden" id="placeLng" value="${lng}">
                
                <label>Nama Pemilik</label>
                <input type="text" id="placeOwnerName" placeholder="Nama pemilik usaha" required>

                <label>Nama Tempat / Usaha</label>
                <input type="text" id="placeName" required>
                
                <label>Kategori</label>
                <select id="placeCategory" required>
                    <option value="7">🏥 Rumah Sakit</option>
                    <option value="2">⛽ Pom Bensin</option>
                    <option value="4">🍴 Warung</option>
                    <option value="5">🔧 Bengkel</option>
                    <option value="1">🚓 Kantor Polisi</option>
                    <option value="6">🏞️ Pariwisata</option>
                </select>
                
                <label>Alamat Lengkap</label>
                <textarea id="placeAddress" required rows="2"></textarea>

                <label>Nomor Telepon</label>
                <input type="text" id="placePhone" placeholder="0812...">

                <label>Deskripsi</label>
                <textarea id="placeDesc" rows="2" placeholder="Info tambahan..."></textarea>
                
                <button type="submit" style="background: #28a745; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer; width: 100%; font-weight:bold; margin-top: 5px; transition: all 0.3s ease;">Kirim Pengajuan</button>
            </form>
        </div>
    `;

    tempMarker.bindPopup(popupContent).openPopup();
});

window.submitPlace = function(event) {
    event.preventDefault();
    
    let submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.innerText = "Mengirim...";
    submitBtn.disabled = true;

    let data = {
        owner_name:  document.getElementById('placeOwnerName').value,
        name:        document.getElementById('placeName').value,
        category_id: document.getElementById('placeCategory').value,
        address:     document.getElementById('placeAddress').value,
        phone:       document.getElementById('placePhone').value,
        description: document.getElementById('placeDesc').value,
        latitude:    document.getElementById('placeLat').value,
        longitude:   document.getElementById('placeLng').value
    };

    let authHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    };
    if (token) {
        authHeaders['Authorization'] = 'Bearer ' + token;
    }

    fetch('/api/owner/places', {
        method: 'POST',
        headers: authHeaders,
        body: JSON.stringify(data)
    })
    .then(response => response.json().then(resData => ({ status: response.status, body: resData })))
    .then(result => {
        if (result.status === 201 || result.status === 200) {
            alert(result.body.message || "Pengajuan berhasil dikirim! Menunggu persetujuan admin.");
            map.closePopup();
            if (tempMarker) map.removeLayer(tempMarker);
            // Dispatch event so owner.blade.php (if open in another tab) can reload
            // Also push new place to a sessionStorage list for live dashboard display
            let savedPlaces = JSON.parse(sessionStorage.getItem('pendingPlaces') || '[]');
            savedPlaces.push(result.body.data || data);
            sessionStorage.setItem('pendingPlaces', JSON.stringify(savedPlaces));
        } else {
            alert("Validasi Gagal: " + (result.body.error || result.body.message || JSON.stringify(result.body)));
        }
        submitBtn.innerText = "Kirim Pengajuan";
        submitBtn.disabled = false;
    })
    .catch(error => {
        alert("Gagal mengirim request.");
        submitBtn.innerText = "Kirim Pengajuan";
        submitBtn.disabled = false;
    });
};
</script>
</body>
</html>