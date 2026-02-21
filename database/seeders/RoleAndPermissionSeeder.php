<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Create roles
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'company_admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        // Create demo company
        $company = Company::firstOrCreate(
            ['domain' => 'auso-world.com'],
            [
                'name' => 'Auso',
                'context' => 'auso',
            ]
        );

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pbx.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'company_id' => null,
                'phone' => '1234567890',
                'nic' => '123-4567-890123',
                'gender' => 'male',
                'address' => '123 Admin Street, PBX City',
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // Create company admin user
        $companyAdmin = User::firstOrCreate(
            ['email' => 'admin@auso-world.com'],
            [
                'name' => 'Company Admin',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'phone' => '0987654321',
                'nic' => '456-7890-123456',
                'gender' => 'female',
                'address' => '456 Company Avenue, Auso World',
            ]
        );
        $companyAdmin->syncRoles(['company_admin']);

        // Create company user
        $companyUser = User::firstOrCreate(
            ['email' => 'user@auso-world.com'],
            [
                'name' => 'Company User',
                'password' => bcrypt('password'),
                'company_id' => $company->id,
                'phone' => '5555551234',
                'nic' => '789-0123-456789',
                'gender' => 'other',
                'address' => '789 User Boulevard, Auso World',
            ]
        );
        $companyUser->syncRoles(['user']);
    }
}
