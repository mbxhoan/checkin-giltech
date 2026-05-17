<?php

namespace Database\Seeders;

use App\Models\MediaLibrary;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        // Role::firstOrCreate(['name' => Role::ROLE_USER]);
        // Role::firstOrCreate(['name' => Role::ROLE_SCANNER]);
        $role_admin = Role::firstOrCreate(['name' => Role::ROLE_ADMIN])->id;

        // MediaLibrary
        MediaLibrary::firstOrCreate([]);

        // Users
        $users = [
            // [
            //     'is_admin'              => true,
            //     'username'              => 'admin',
            //     'email'                 => config('info.admin_email'),
            //     'name'                  => 'Admin',
            //     'password'              => '@Admin2025',
            // ],
            [
                'is_sys_admin'          => false,
                'is_admin'              => true,
                'username'              => 'tester',
                'email'                 => 'tester@'.config('info.domain'),
                'name'                  => 'Tester',
                'password'              => 'Testing@2025',
                ''
            ],
            // [
            //     'is_admin'              => false,
            //     'username'              => 'api.agent',
            //     'email'                 => 'api.agent@'.config('info.domain'),
            //     'name'                  => 'Api Agent',
            //     'password'              => 'Apiagent347@',
            // ],
            // [
            //     'is_admin'              => false,
            //     'username'              => 'api',
            //     'email'                 => 'apiagent@'.config('info.domain'),
            //     'name'                  => 'API Agent',
            //     'password'              => 'Agent@2025',
            // ],
        ];


        foreach ($users as $user) {
            $newUser = User::firstOrCreate(
                ['email'                    => $user['email']],
                [
                    'is_admin'              => $user['is_sys_admin'],
                    'name'                  => $user['name'],
                    'username'              => $user['username'],
                    'password'              => Hash::make($user['password']),
                    'email_verified_at'     => now(),
                    'status'                => User::STATUS_ACTIVE,
                ]
            );

            if ($user['is_admin']) {
                $newUser->roles()->sync([$role_admin]);
            }
        }
    }
}
