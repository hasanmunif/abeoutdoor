<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Abe Outdoor Store',
            'address' => 'Jl. Pahlawan No. 123, Jakarta Selatan',
            'thumbnail' => 'stores/default-store.jpg', // Pastikan file ini ada di storage/app/public/stores/
            'is_open' => true,
        ];
    }
}