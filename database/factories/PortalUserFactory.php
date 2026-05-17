<?php

namespace Database\Factories;

use App\Models\PortalUser;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class PortalUserFactory extends Factory
{
    protected $model = PortalUser::class;

    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'name' => $this->faker->name(),
            'phone' => $this->faker->numerify('09########'),
            'password' => Hash::make(PortalUser::DEFAULT_PASSWORD),
            'status' => 'ACTIVE',
            'email_verified_at' => now(),
            'last_login_at' => null,
            'metadata' => [],
        ];
    }
}
