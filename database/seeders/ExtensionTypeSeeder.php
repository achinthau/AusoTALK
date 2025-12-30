<?php

namespace Database\Seeders;

use App\Models\ExtensionType;
use Illuminate\Database\Seeder;

class ExtensionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ['SIP', 'IAX2', 'PJSIP'];

        foreach ($types as $type) {
            ExtensionType::firstOrCreate(
                ['name' => $type]
            );
        }
    }
}
