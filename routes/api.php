<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\PlaceController;
use App\Models\Category;
use Illuminate\Support\Facades\App;
use League\Uri\Http;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->post(
    '/logout',
    [AuthController::class, 'logout']
);

Route::middleware(['auth:sanctum'])
    ->get('/user', function (Request $request) {
        return $request->user();
    });

Route::middleware(['auth:sanctum', 'role:admin'])
    ->get('/admin-only', function () {
        return response()->json([
            'message' => 'Admin access granted'
        ]);
    });

/* OWNER */
Route::middleware([
    'auth:sanctum',
    'role:owner'
])->group(function () {

    Route::post(
        '/places',
        [PlaceController::class, 'store']
    );

    Route::post('/owner/places', [\App\Http\Controllers\OwnerPlaceController::class, 'store']);

    Route::get(
        '/my-places',
        [PlaceController::class, 'myPlaces']
    );

});

/* ADMIN */
Route::middleware([
    'auth:sanctum',
    'role:admin'
])->group(function () {

    Route::get(
        '/places',
        [PlaceController::class, 'index']
    );

    Route::post(
        '/places/{id}/approve',
        [PlaceController::class, 'approve']
    );

    Route::post(
        '/places/{id}/reject',
        [PlaceController::class, 'reject']
    );

    Route::get(
        '/users',
        [AuthController::class, 'indexUsers']
    );

    Route::delete(
        '/users/{id}',
        [AuthController::class, 'destroyUser']
    );

});

Route::get(
    '/approved-places',
    [PlaceController::class, 'approvedPlaces']
);

Route::get('/categories', function () {
    return Category::all();
});

Route::middleware('auth:sanctum')->group(function () {
    // ... rute lain yang sudah ada sebelumnya ...

    // TAMBAHKAN BARIS INI UNTUK FITUR HAPUS:
    Route::delete('/places/{id}', [\App\Http\Controllers\PlaceController::class, 'destroy']);
    Route::get('/myPlaces', [\App\Http\Controllers\PlaceController::class, 'myPlaces']);
});

// Public routes
Route::get('/public-places', [\App\Http\Controllers\PlaceController::class, 'publicPlaces']);
Route::get('/places/nearby', [\App\Http\Controllers\PlaceController::class, 'nearby']);
Route::get('/places/search', [\App\Http\Controllers\PlaceController::class, 'searchPlaces']);