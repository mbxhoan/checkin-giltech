<?php

namespace Database\Seeders\dev;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $infos = config('info');
        Company::firstOrCreate(
            [
                'code'          => $infos['code']
            ],
            [
                'is_default'    => true,
                'code'          => $infos['code'],
                'name'          => $infos['company_name'],
                'status'        => Company::STATUS_ACTIVE
            ]
        );
    }
}
