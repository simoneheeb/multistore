<?php

namespace Modules\User\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\User\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            // The id is left to HasUuids; forcing a numeric id here would
            // conflict with the uuid primary key.
            'name' => $this->faker->name(),
            'mobile' => '09'.$this->faker->numerify('#########'),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'is_active' => true,
            'is_admin' => false,
            'remember_token' => Str::random(60),
        ];
    }

    /**
     * State for the single account that may reach /api/admin/*.
     */
    public function admin(): static
    {
        return $this->state(fn () => [
            'is_admin' => true,
            'is_active' => true,
        ]);
    }
}
