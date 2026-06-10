<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'name',
        'icon'
    ];

    // Relasi ke places
    public function places()
    {
        return $this->hasMany(Place::class);
    }
}
