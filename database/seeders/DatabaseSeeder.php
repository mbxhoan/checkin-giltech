<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\dev\CompanyTableSeeder;
use Database\Seeders\Videc\Videc2026Seeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CompanyTableSeeder::class,
            ProvincesTableSeeder::class,
            LanguageTableSeeder::class,
            PackagesSeeder::class,
            UserSeeder::class,
            Videc2026Seeder::class,
        ]);

        // Roles
        $roles[] = Role::firstOrCreate(['name' => Role::ROLE_ADMIN])->id;
        $roles[] = Role::firstOrCreate(['name' => Role::ROLE_SCANNER])->id;
        $roles[] = Role::firstOrCreate(['name' => Role::ROLE_USER])->id;
        $role_ids = array_values($roles);

        // Users
        $user = User::firstOrCreate(
            ['email'                => config('info.admin_email')],
            [
                'is_admin'          => true,
                'name'              => config('info.admin_name'),
                'username'          => 'admin',
                'password'          => Hash::make("@Admin2025"),
                'email_verified_at' => now(),
                'status'            => User::STATUS_ACTIVE,
            ]
        );

        // $user->roles()->sync([$role_admin->id]);
        $user->roles()->sync($role_ids);
    }
}
