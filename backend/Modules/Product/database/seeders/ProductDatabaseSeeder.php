<?php

namespace Modules\Product\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Category\Models\Category;
use Modules\Product\Models\Product;

/**
 * Attaches demo products to the leaf categories created by the category
 * seeder, so the landing page and category pages have something to render.
 */
class ProductDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Leaves only: products hang off the most specific node, which is
        // what makes the "products of a whole branch" query meaningful.
        $leaves = Category::query()
            ->whereNotIn('id', Category::query()->whereNotNull('parent_id')->pluck('parent_id'))
            ->get();

        if ($leaves->isEmpty()) {
            $this->command?->warn('No categories found - run the category seeder first.');

            return;
        }

        foreach ($leaves as $category) {
            Product::factory()->count(4)->create([
                'category_id' => $category->id,
                'brand_id' => $category->brand_id,
            ]);
        }
    }
}
