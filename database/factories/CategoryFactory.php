<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Tenda', 'Tas', 'Sepatu', 'Jaket', 'Peralatan Masak', 'Sleeping Gear', 'Aksesoris'];
        $name = $this->faker->unique()->randomElement($categories);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'icon' => 'categories/icon-' . strtolower(Str::slug($name)) . '.png', // Pastikan file ini ada
        ];
    }
}