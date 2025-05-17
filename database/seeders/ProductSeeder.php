<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder untuk menyimpan gambar ada
        Storage::disk('public')->makeDirectory('products');

        // Setup data produk
        $multiQtyProducts = [
            [
                'name' => 'Tenda Coleman Alpine 4P',
                'about' => 'Tenda untuk 4 orang dengan kualitas premium, tahan air dan ringan.',
                'price' => 250000, // per 3 hari
                'stock' => 15,
                'category' => 'Tenda',
                'brand' => 'Coleman',
                'can_multi_quantity' => true
            ],
            [
                'name' => 'Kompor Portable Eiger',
                'about' => 'Kompor portable yang ringan dan efisien untuk kegiatan outdoor.',
                'price' => 100000, // per 3 hari
                'stock' => 20,
                'category' => 'Peralatan Masak',
                'brand' => 'Eiger',
                'can_multi_quantity' => true
            ],
            [
                'name' => 'Sleeping Bag Consina',
                'about' => 'Sleeping bag dengan suhu -5°C untuk tidur nyaman di udara dingin.',
                'price' => 150000, // per 3 hari
                'stock' => 25,
                'category' => 'Sleeping Gear',
                'brand' => 'Consina',
                'can_multi_quantity' => true
            ],
            [
                'name' => 'Matras Camping Rei',
                'about' => 'Matras dengan ketebalan 2.5cm untuk kenyamanan tidur di alam.',
                'price' => 80000, // per 3 hari
                'stock' => 30,
                'category' => 'Sleeping Gear',
                'brand' => 'Rei',
                'can_multi_quantity' => true
            ]
        ];

        $singleQtyProducts = [
            [
                'name' => 'Sepatu Hiking The North Face',
                'about' => 'Sepatu hiking waterproof dengan grip yang kuat untuk medan terjal.',
                'price' => 200000, // per 3 hari
                'stock' => 10,
                'category' => 'Sepatu',
                'brand' => 'The North Face',
                'can_multi_quantity' => false
            ],
            [
                'name' => 'Tas Carrier Osprey 60L',
                'about' => 'Tas carrier 60L dengan sistem ergonomis untuk kenyamanan pendakian.',
                'price' => 180000, // per 3 hari
                'stock' => 12,
                'category' => 'Tas',
                'brand' => 'Osprey',
                'can_multi_quantity' => false
            ],
            [
                'name' => 'Jaket Gunung Eiger',
                'about' => 'Jaket windbreaker dan waterproof untuk melindungi dari cuaca ekstrim.',
                'price' => 220000, // per 3 hari
                'stock' => 8,
                'category' => 'Jaket',
                'brand' => 'Eiger',
                'can_multi_quantity' => false
            ],
            [
                'name' => 'Tas Ransel Deuter 30L',
                'about' => 'Tas daypack 30L untuk kegiatan hiking satu hari.',
                'price' => 150000, // per 3 hari
                'stock' => 15,
                'category' => 'Tas',
                'brand' => 'Deuter',
                'can_multi_quantity' => false
            ]
        ];

        // Fungsi untuk membuat produk
        $createProduct = function($productData) {
            $category = Category::where('name', $productData['category'])->first();
            $brand = Brand::where('name', $productData['brand'])->first();

            if ($category && $brand) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'thumbnail' => 'products/default-product.jpg',
                    'about' => $productData['about'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'can_multi_quantity' => $productData['can_multi_quantity'],
                    'category_id' => $category->id,
                    'brand_id' => $brand->id
                ]);
            }
        };

        // Buat produk multi quantity
        foreach ($multiQtyProducts as $product) {
            $createProduct($product);
        }

        // Buat produk single quantity
        foreach ($singleQtyProducts as $product) {
            $createProduct($product);
        }
    }
}