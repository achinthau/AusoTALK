<?php

namespace Database\Seeders;

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

        // Create a super admin user if it doesn't exist
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pbx.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'company_id' => null,
            ]
        );

        // Assign super_admin role
        $superAdmin->syncRoles(['super_admin']);
    }
}
