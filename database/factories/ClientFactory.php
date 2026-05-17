<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        static $event_id;
        static $event_code;
        static $qrcode;

        return [
            'event_id'      => $event_id ?: $event_id = 0,
            'event_code'    => $event_code ?: $event_code = null,
            'qrcode'        => $qrcode ?: $qrcode = 'qrcode',
            // 'name'          => $this->faker->name,
            // 'email'         => $this->faker->unique()->safeEmail,
            'name'          => "unnamed",
            'email'         => null,
            'status'        => Client::STATUS_ACTIVE,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }
}
