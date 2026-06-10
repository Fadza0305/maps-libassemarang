<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
     protected $fillable = [
        'name',
        'category_id',
        'user_id',
        'latitude',
        'longitude',
        'address',
        'description',
        'phone',
        'status',
        'reject_reason'
    ];

    // Relasi ke category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke photos
    public function photos()
    {
        return $this->hasMany(PlacePhoto::class);
    }
}
