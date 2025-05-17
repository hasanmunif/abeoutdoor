<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        $slug = Str::slug($name);

        // List produk yang bisa multi-quantity
        $multiQtyProducts = ['tenda', 'kompor', 'matras', 'kursi lipat', 'tracking pole',
                           'sarung tangan', 'headlamp', 'gas refill', 'sleeping bag'];

        // 40% produk bisa multi-quantity
        $canMultiQty = $this->faker->randomElement($multiQtyProducts);
        $isMultiQty = str_contains(strtolower($name), $canMultiQty) || $this->faker->boolean(40);

        return [
            'name' => $name,
            'slug' => $slug,
            'thumbnail' => 'products/product-' . $this->faker->numberBetween(1, 5) . '.jpg', // Pastikan file ini ada di storage
            'about' => $this->faker->paragraphs(3, true),
            'price' => $this->faker->numberBetween(50000, 500000), // Harga per 3 hari
            'stock' => $this->faker->numberBetween(1, 20),
            'can_multi_quantity' => $isMultiQty,
            'category_id' => Category::inRandomOrder()->first()->id,
            'brand_id' => Brand::inRandomOrder()->first()->id,
        ];
    }
}