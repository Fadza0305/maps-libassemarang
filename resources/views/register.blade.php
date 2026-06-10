<!DOCTYPE html>
<html>
<head>
    <title>Daftar - Yanmaps Semarang</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; background-color: #121212; color: #eee; }
        .card { background: #1e1e1e; padding: 36px 40px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.5); width: 340px; border: 1px solid #333; }
        .logo-container { text-align: center; margin-bottom: 15px; }
        .logo-container img { height: 60px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); }
        h2 { margin: 0 0 20px; text-align: center; color: #f8ca00; font-size: 24px; text-shadow: 1px 1px 2px rgba(0,0,0,0.8); }
        label { font-size: 13px; font-weight: 600; color: #aaa; display: block; margin-bottom: 4px; }
        input { width: 100%; padding: 10px 12px; margin-bottom: 16px; border: 1px solid #444; border-radius: 6px; font-size: 14px; box-sizing: border-box; transition: border 0.2s; background: #2d2d2d; color: #eee; outline: none; }
        input:focus { border-color: #f8ca00; }
        button { width: 100%; padding: 11px; background: #f8ca00; color: #111; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; font-size: 15px; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.3); }
        button:hover { background: #e0b600; }
        .error { color: #d9534f; font-size: 13px; margin-bottom: 12px; display: none; text-align: center; }
        .footer { text-align: center; font-size: 13px; margin-top: 16px; color: #aaa; }
        .footer a { color: #f8ca00; text-decoration: none; font-weight: bold; }
        .footer a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<div class="card">
    <div class="logo-container">
        <img src="{{ asset('images/Rastra_Sewakottama.png') }}" alt="Logo Polri">
    </div>
    <h2>Daftar Akun Owner</h2>
    
    <div class="error" id="errorMsg"></div>

    <form id="registerForm">
        <label for="name">Nama Lengkap</label>
        <input type="text" id="name" required autofocus placeholder="Nama Anda">

        <label for="email">Email</label>
        <input type="email" id="email" required placeholder="contoh@email.com">

        <label for="password">Password</label>
        <input type="password" id="password" required placeholder="Password">

        <button type="submit" id="submitBtn">Daftar Sekarang</button>
    </form>
    
    <p class="footer">Sudah punya akun? <a href="/login">Login di sini</a></p>
    <p class="footer" style="margin-top: 8px;"><a href="/map">← Kembali ke Peta</a></p>
</div>

<script>
    document.getElementById("registerForm").addEventListener("submit", function(e) {
        e.preventDefault();

        let submitBtn = document.getElementById("submitBtn");
        let errorMsg = document.getElementById("errorMsg");
        
        submitBtn.innerText = "Memproses...";
        submitBtn.disabled = true;
        errorMsg.style.display = 'none';

        let name = document.getElementById("name").value;
        let email = document.getElementById("email").value;
        let password = document.getElementById("password").value;

        fetch('/api/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: name, email: email, password: password })
        })
        .then(response => {
            if(!response.ok) throw new Error("Gagal mendaftar");
            return response.json();
        })
        .then(data => {
            alert("Pendaftaran Berhasil! Silakan Login menggunakan web login kami untuk mendapatkan sesi.");
            window.location.href = '/login';
        })
        .catch(error => {
            errorMsg.innerText = "Gagal Mendaftar! Email mungkin sudah digunakan.";
            errorMsg.style.display = 'block';
            submitBtn.innerText = "Daftar Sekarang";
            submitBtn.disabled = false;
        });
    });
</script>

</body>
</html>