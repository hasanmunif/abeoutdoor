<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BrandCategory;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder untuk menyimpan gambar ada
        Storage::disk('public')->makeDirectory('brands');

        // Brand umum untuk outdoor
        $brands = [
            'Eiger',
            'Consina',
            'Rei',
            'The North Face',
            'Coleman',
            'Osprey',
            'Deuter',
            'Arei'
        ];

        foreach ($brands as $brand) {
            $newBrand = Brand::create([
                'name' => $brand,
                'slug' => Str::slug($brand),
                'logo' => 'brands/default-logo.png', // Placeholder, ganti dengan gambar asli
            ]);

            // Masukkan relasi brand dengan semua kategori
            $categories = Category::all();
            foreach ($categories as $category) {
                BrandCategory::create([
                    'brand_id' => $newBrand->id,
                    'category_id' => $category->id
                ]);
            }
        }
    }
}