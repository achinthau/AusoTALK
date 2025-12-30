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
        // Create roles
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'user']);

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
        $superAdmin->assignRole('super_admin');
    }
}
