<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ==========================================
// PUBLIC ROUTES
// ==========================================
Route::get('/', function () {
    return redirect('/map');
});

Route::get('/map', function () {
    return view('map');
});

// Login page (GET) & submit (POST via web session)
Route::get('/login', function() {
    return view('login');
})->name('login');

Route::post('/login', [AuthController::class, 'webLogin'])->name('login.post');

// Logout via POST form (requires CSRF token)
Route::post('/logout', [AuthController::class, 'webLogout'])->name('logout');

Route::get('/register', function() {
    return view('register');
});

// ==========================================
// AUTHENTICATED DASHBOARD ROUTES
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', function() {
        return view('admin');
    })->name('admin.dashboard');

    Route::get('/owner/dashboard', function() {
        return view('owner');
    })->name('owner.dashboard');
});

// ==========================================
// LEGACY / TEST ROUTES
// ==========================================
Route::get('/test-register', function () {
    return '
        <form method="POST" action="/api/register">
            <input type="hidden" name="_token" value="'.csrf_token().'">
            Name: <input name="name"><br>
            Email: <input name="email"><br>
            Password: <input name="password" type="password"><br>
            <button type="submit">Register</button>
        </form>
    ';
});

Route::get('/test-admin', function () {
    return response()->json(['message' => 'Admin test route working']);
});

// Legacy routes kept for backward compatibility
Route::get('/admin', function() { return view('admin'); });
Route::get('/owner', function() { return view('owner'); });
