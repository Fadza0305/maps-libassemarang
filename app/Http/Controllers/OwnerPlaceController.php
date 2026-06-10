<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Place;

class OwnerPlaceController extends Controller
{
    /**
     * Store a newly created place in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'category_id' => 'required|integer', // Assuming category comes as an ID
            'address'     => 'required|string',
            'phone'       => 'nullable|string',
            'description' => 'nullable|string',
            'latitude'    => 'required|numeric|between:-90,90',
            'longitude'   => 'required|numeric|between:-180,180',
        ]);

        $centerLat = -7.005145;
        $centerLng = 110.438126;

        // PHP Haversine Formula
        $distance = $this->haversine($request->latitude, $request->longitude, $centerLat, $centerLng);

        // BACKEND GEOFENCING VALIDATION
        if ($distance > 20) {
            return response()->json([
                'error' => 'Koordinat lokasi di luar batas wilayah Semarang (maks. 20 km dari pusat kota).'
            ], 422);
        }

        // Require authenticated user — no anonymous/test submissions
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['error' => 'Anda harus login sebagai Owner untuk mendaftarkan tempat.'], 401);
        }

        $place = Place::create([
            'user_id'    => $userId,
            'name'       => $request->name,
            'category_id'=> $request->category_id,
            'address'    => $request->address,
            'phone'      => $request->phone,
            'description'=> $request->description,
            'latitude'   => $request->latitude,
            'longitude'  => $request->longitude,
            'status'     => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan berhasil dikirim. Menunggu verifikasi Admin.',
            'data'    => $place
        ], 201);
    }

    /**
     * Menghitung jarak antara dua koordinat menggunakan Haversine Formula.
     * @return float Jarak dalam kilometer
     */
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
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
}
