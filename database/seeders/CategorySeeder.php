<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
        'Tempat Ibadah',
        'Pom Bensin',
        'Bengkel',
        'Warung',
        'Apotek',
        'Kantor Polisi',
        'Rumah Sakit'
    ];

    foreach ($categories as $category) {
        Category::create([
            'name' => $category
        ]);
    }
    }
}
