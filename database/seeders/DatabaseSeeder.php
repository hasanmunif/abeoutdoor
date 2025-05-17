<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,     // User admin dan customer
            StoreSeeder::class,    // 1 store saja
            CategorySeeder::class, // Kategori-kategori
            BrandSeeder::class,    // Brand-brand
            ProductSeeder::class,  // Produk-produk
        ]);
    }
}