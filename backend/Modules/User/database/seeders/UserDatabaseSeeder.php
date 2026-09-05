<?php

namespace Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Models\User;

class UserDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Credentials come from the environment so no real password ever
        // lands in version control. Falls back to obvious local-only values.
        $email = env('ADMIN_EMAIL', 'admin@example.com');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Administrator'),
                'mobile' => env('ADMIN_MOBILE', '041000000000'),
                'password' => env('ADMIN_PASSWORD', 'password'),
                'is_active' => true,
                'is_admin' => true,
            ]
        );
    }
}
