<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Brand\Database\Seeders\BrandDatabaseSeeder;
use Modules\Category\Database\Seeders\CategoryDatabaseSeeder;
use Modules\Product\Database\Seeders\ProductDatabaseSeeder;
use Modules\Settings\Database\Seeders\SettingsDatabaseSeeder;
use Modules\User\Database\Seeders\UserDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    // Note: WithoutModelEvents is deliberately NOT used here. The Category
    // and Product models compute `depth` and `pid` inside their model events,
    // so suppressing events would seed rows with a broken tree depth and no
    // product identifier.
    public function run(): void
    {
        // Order matters: settings and the admin account are always seeded,
        // the catalogue seeders are optional demo data and depend on each
        // other (brand -> category tree -> products).
        $this->call([
            UserDatabaseSeeder::class,
            SettingsDatabaseSeeder::class,
        ]);

        if (app()->environment('local', 'testing')) {
            $this->call([
                BrandDatabaseSeeder::class,
                CategoryDatabaseSeeder::class,
                ProductDatabaseSeeder::class,
            ]);
        }
    }
}
