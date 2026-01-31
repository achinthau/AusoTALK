<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Extension;
use App\Models\ExtensionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompanyExtensionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Auso company
        $company = Company::where('domain', 'auso-world.com')->first();
        
        if (!$company) {
            $this->command->info('Auso company not found. Skipping extension seeding.');
            return;
        }

        // Get extension types
        $sipType = ExtensionType::where('name', 'SIP')->first();
        $iax2Type = ExtensionType::where('name', 'IAX2')->first();

        if (!$sipType || !$iax2Type) {
            $this->command->info('Extension types not found. Ensure ExtensionTypeSeeder has run.');
            return;
        }

        // Define extensions to create (SIP)
        $sipExtensions = [
            ['number' => '1001', 'password' => $this->generatePassword()],
            ['number' => '1002', 'password' => $this->generatePassword()],
            ['number' => '1003', 'password' => $this->generatePassword()],
            ['number' => '1004', 'password' => $this->generatePassword()],
            ['number' => '1005', 'password' => $this->generatePassword()],
        ];

        // Define extensions to create (IAX2)
        $iax2Extensions = [
            ['number' => '2001', 'password' => $this->generatePassword()],
            ['number' => '2002', 'password' => $this->generatePassword()],
            ['number' => '2003', 'password' => $this->generatePassword()],
        ];

        // Create SIP extensions
        foreach ($sipExtensions as $extData) {
            Extension::firstOrCreate(
                [
                    'number' => $extData['number'],
                    'company_id' => $company->id,
                ],
                [
                    'extension_type_id' => $sipType->id,
                    'password' => $extData['password'],
                ]
            );
        }

        // Create IAX2 extensions
        foreach ($iax2Extensions as $extData) {
            Extension::firstOrCreate(
                [
                    'number' => $extData['number'],
                    'company_id' => $company->id,
                ],
                [
                    'extension_type_id' => $iax2Type->id,
                    'password' => $extData['password'],
                ]
            );
        }

        $this->command->info('Auso company extensions seeded successfully!');
        $this->command->info('SIP Extensions: ' . implode(', ', array_column($sipExtensions, 'number')));
        $this->command->info('IAX2 Extensions: ' . implode(', ', array_column($iax2Extensions, 'number')));
    }

    /**
     * Generate a secure password for extension
     */
    private function generatePassword(): string
    {
        return bin2hex(random_bytes(8));
    }
}
