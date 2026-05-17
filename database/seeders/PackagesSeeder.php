<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Package;

class PackagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = config("info.packages");

        foreach ($packages as $code => $package) {
            Package::firstOrCreate(
                [
                    'code'          => $code
                ],
                [
                    'code'          => $code,
                ]
            );
        }
    }
}
