<?php

namespace Database\Seeders;

use App\Models\Company;
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

        // Create extension types
        foreach ($types as $type) {
            ExtensionType::firstOrCreate(
                ['name' => $type]
            );
        }

        // Allocate extension types to Auso company
        $company = Company::where('domain', 'auso-world.com')->first();
        if ($company) {
            $extensionTypeIds = ExtensionType::pluck('id')->toArray();
            // Sync the extension types (attach only the ones not already attached)
            $company->extensionTypes()->syncWithoutDetaching($extensionTypeIds);
        }
    }
}
