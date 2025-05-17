<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $brands = ['Eiger', 'Consina', 'Rei', 'The North Face', 'Coleman', 'Osprey', 'Deuter', 'Arei'];
        $name = $this->faker->unique()->randomElement($brands);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'logo' => 'brands/logo-' . strtolower(Str::slug($name)) . '.png', // Pastikan file ini ada
        ];
    }
}