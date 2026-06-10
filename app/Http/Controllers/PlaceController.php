<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Place;

class PlaceController extends Controller
{
    // OWNER TAMBAH USAHA
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'address' => 'required'
        ]);

        $place = Place::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'user_id' => $request->user()->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'address' => $request->address,
            'description' => $request->description,
            'phone' => $request->phone,
            'status' => 'pending'
        ]);

        return response()->json([
            'message' => 'Usaha berhasil ditambahkan',
            'data' => $place
        ]);
    }

    // OWNER LIHAT USAHA SENDIRI
    // public function myPlaces(Request $request)
    // {
    //     $places = Place::where(
    //         'user_id',
    //         $request->user()->id
    //     )->get();

    //     return response()->json($places);
    // }

    // ADMIN LIHAT SEMUA USAHA
    public function index()
    {
        return Place::with([
            'category',
            'user' // Eager load user relationship for Admin dashboard
        ])->get();
    }

    // ADMIN APPROVE USAHA
    public function approve($id)
    {
        $place = Place::findOrFail($id);

        $place->update([
            'status' => 'approved'
        ]);

        return response()->json([
            'message' => 'Usaha disetujui'
        ]);
    }

    // ADMIN REJECT USAHA
    public function reject(Request $request, $id)
    {
        $place = Place::findOrFail($id);

        $place->update([
            'status' => 'rejected',
            'reject_reason' => $request->input('reason')
        ]);

        return response()->json([
            'message' => 'Usaha ditolak'
        ]);
    }

    // PUBLIC: tampilkan usaha approved
    public function approvedPlaces()
    {
        $places = Place::with('category')
            ->where('status', 'approved')
            ->get();

        return response()->json($places);
    }
    
    public function destroy($id)
    {
        // Cari tempat berdasarkan ID
        $place = \App\Models\Place::find($id);

        // Jika tempat tidak ditemukan, kembalikan pesan error 404
        if (!$place) {
            return response()->json(['message' => 'Tempat tidak ditemukan.'], 404);
        }

        // Hapus tempat dari database
        $place->delete();

        return response()->json(['message' => 'Tempat berhasil dihapus secara permanen.']);
    }

    public function myPlaces(\Illuminate\Http\Request $request)
    {
        $place = \App\Models\Place::where('user_id', auth()->id())->get();

        return response()->json($place);
    }

    public function publicPlaces()
    {
        $places = \App\Models\Place::where('status', 'approved')->get();

        return response()->json($places);
    }

    public function nearby(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'user_lat' => 'required|numeric',
            'user_lng' => 'required|numeric',
        ]);

        $userLat = $request->user_lat;
        $userLng = $request->user_lng;
        $category = $request->category;

        // Map category parameter to actual database category name
        $categoryMapping = [
            'hospital' => 'Rumah Sakit',
            'fuel' => 'Pom Bensin',
            'restaurant' => 'Warung',
        ];

        $categoryName = $categoryMapping[$category] ?? $category;

        $categoryModel = \App\Models\Category::where('name', $categoryName)->first();
        if (!$categoryModel) {
            return response()->json([]);
        }

        $maxRadius = 20; // 20 km maximum distance

        // Haversine query implementation via selectRaw and havingRaw
        $places = \App\Models\Place::select('places.*')
            ->selectRaw("
                (6371 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(latitude))
                    * COS(RADIANS(longitude) - RADIANS(?))
                    + SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                )) AS distance_km
            ", [$userLat, $userLng, $userLat])
            ->where('status', 'approved')
            ->where('category_id', $categoryModel->id)
            ->havingRaw("distance_km <= ?", [$maxRadius])
            ->orderBy('distance_km', 'asc')
            ->get();

        return response()->json($places);
    }

    public function searchPlaces(\Illuminate\Http\Request $request)
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json([]);
        }

        $placesQuery = \App\Models\Place::with('category')
            ->where('status', 'approved')
            ->where('name', 'like', '%' . $query . '%');

        // Calculate distance if user coords are provided
        if ($request->has('user_lat') && $request->has('user_lng')) {
            $userLat = $request->user_lat;
            $userLng = $request->user_lng;

            $placesQuery->select('places.*')
                ->selectRaw("
                    (6371 * ACOS(
                        COS(RADIANS(?)) * COS(RADIANS(latitude))
                        * COS(RADIANS(longitude) - RADIANS(?))
                        + SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                    )) AS distance_km
                ", [$userLat, $userLng, $userLat])
                ->orderBy('distance_km', 'asc');
        }

        $places = $placesQuery->take(20)->get();
        return response()->json($places);
    }
}

