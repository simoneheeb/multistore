<?php

namespace Modules\Category\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = ucwords($this->faker->unique()->words(2, true));

        return [
            'brand_id' => Brand::factory(),
            'parent_id' => null,
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'logo' => null,
            'is_active' => true,
            'is_new' => $this->faker->boolean(30),
            'order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Attaches the category under a parent. The parent's brand is reused so
     * a branch never spans two catalogues.
     */
    public function childOf(Category $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'brand_id' => $parent->brand_id,
        ]);
    }
}
