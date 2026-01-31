<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            ]
        );

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pbx.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'company_id' => null,
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
            ]
        );
        $companyUser->syncRoles(['user']);
    }
}
