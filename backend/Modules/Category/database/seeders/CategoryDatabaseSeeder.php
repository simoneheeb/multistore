<?php

namespace Modules\Category\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Brand\Models\Brand;
use Modules\Category\Models\Category;

/**
 * Seeds a realistic three-level category tree for one brand.
 *
 * The structure below is the demo catalogue; every node is created with an
 * explicit parent so `depth` is filled in by the model and the tree endpoint
 * has something to return on a fresh install.
 */
class CategoryDatabaseSeeder extends Seeder
{
    /**
     * The demo catalogue, three levels deep.
     *
     * @var list<array{name: string, children?: array}>
     */
    protected array $tree = [
        ['name' => 'Sports Balls', 'children' => [
            ['name' => 'Football', 'children' => [
                ['name' => 'Leather Football'],
                ['name' => 'Training Football'],
            ]],
            ['name' => 'Futsal'],
            ['name' => 'Basketball'],
            ['name' => 'Volleyball'],
        ]],
        ['name' => 'Apparel', 'children' => [
            ['name' => 'T-Shirts'],
            ['name' => 'Shorts'],
            ['name' => 'Tracksuits'],
        ]],
        ['name' => 'Fitness Equipment', 'children' => [
            ['name' => 'Dumbbells'],
            ['name' => 'Resistance Bands'],
        ]],
        ['name' => 'Swimming Gear'],
        ['name' => 'Sports Bags'],
    ];

    public function run(): void
    {
        $brand = Brand::query()->first() ?? Brand::factory()->create();

        foreach ($this->tree as $index => $node) {
            $this->createNode($node, $brand->id, null, $index);
        }
    }

    /**
     * @param  array{name: string, children?: array}  $node
     */
    protected function createNode(array $node, string $brandId, ?string $parentId, int $order): void
    {
        $category = Category::query()->updateOrCreate(
            ['brand_id' => $brandId, 'slug' => Str::slug($node['name'])],
            [
                'name' => $node['name'],
                'parent_id' => $parentId,
                'description' => 'Products in the '.$node['name'].' category.',
                'is_active' => true,
                'is_new' => false,
                'order' => $order,
            ]
        );

        foreach ($node['children'] ?? [] as $childIndex => $child) {
            $this->createNode($child, $brandId, $category->id, $childIndex);
        }
    }
}
