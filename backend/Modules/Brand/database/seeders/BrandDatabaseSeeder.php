<?php

namespace Modules\Brand\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brand\Models\Brand;

class BrandDatabaseSeeder extends Seeder
{
    /// Run the database seeds.
    public function run(): void
    {
        Brand::factory()->count(10)->create(); // Create 20 brands using the factory
    }
}
