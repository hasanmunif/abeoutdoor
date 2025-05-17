<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder untuk menyimpan gambar ada
        Storage::disk('public')->makeDirectory('categories');

        // Kategori umum untuk outdoor
        $categories = [
            'Tenda',
            'Tas',
            'Sepatu',
            'Jaket',
            'Peralatan Masak',
            'Sleeping Gear',
            'Aksesoris'
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'icon' => 'categories/default-icon.png', // Placeholder, ganti dengan gambar asli
            ]);
        }
    }
}