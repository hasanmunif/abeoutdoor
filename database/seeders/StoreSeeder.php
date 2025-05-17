<?php

namespace Database\Seeders;

use App\Models\Store;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class StoreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan folder untuk menyimpan gambar ada
        Storage::disk('public')->makeDirectory('stores');

        // Buat 1 store
        Store::create([
            'name' => 'Abe Outdoor Store',
            'address' => 'Jl. Pahlawan No. 123, Jakarta Selatan',
            'thumbnail' => 'stores/default-store.jpg',
            'is_open' => true,
        ]);
    }
}