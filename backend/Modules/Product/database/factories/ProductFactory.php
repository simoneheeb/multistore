<?php

namespace Modules\Product\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $base = $this->faker->randomElement([
            'Football', 'Tennis Racket', 'Dumbbell', 'Swim Goggles',
            'Sports Bag', 'Training Shirt', 'Jump Rope', 'Yoga Mat',
        ]);


        $edition = $this->faker->randomElement(['Pro', 'Elite', 'Classic', 'New Edition']);

        $name = $base.' '.$edition.' '.Str::upper(Str::random(4));

        return [
            'name' => $name,
            'slug' => Str::slug($name),

            'category_id' => Category::factory(),
            'brand_id' => Brand::factory(),

            'description' => $this->faker->paragraph(),

            'is_active' => true,
            'is_new' => $this->faker->boolean(40),

            'featured_img' => 'https://placehold.co/640x480/png',
            'gallery' => ['https://placehold.co/640x480/png', 'https://placehold.co/800x600/png'],

            // Stored as a list of key/value pairs, matching what the admin
            // attributes editor posts.
            'attributes' => [
                ['key' => 'Material', 'value' => $this->faker->randomElement(['Leather', 'Fabric', 'Metal', 'Rubber'])],
                ['key' => 'Weight', 'value' => $this->faker->numberBetween(100, 5000).' g'],
                ['key' => 'Best for', 'value' => $this->faker->randomElement(['Outdoors', 'The gym', 'Competition'])],
            ],
        ];
    }
}
