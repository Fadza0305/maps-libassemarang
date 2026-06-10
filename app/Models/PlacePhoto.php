<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacePhoto extends Model
{
    protected $fillable = [
        'place_id',
        'photo_path'
    ];

    // Relasi ke place
    public function place()
    {
        return $this->belongsTo(Place::class);
    }
}
