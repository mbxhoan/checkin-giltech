<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Language;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $languages = config("languages");

        foreach ($languages as $key => $lang) {
            Language::firstOrCreate(
                [
                    'code'          => $key
                ],
                [
                    'code'          => $key,
                    'name'          => $lang['name'],
                    'description'   => $lang['description'],
                    'icon_path'     => $lang['icon_path'] ?? null,
                    'is_default'    => isset($lang['is_default']) && $lang['is_default'] ? $lang['is_default'] : false,
                    'created_at'    => now(),
                    'updated_at'    => now()
                ]
            );
        }
    }
}
