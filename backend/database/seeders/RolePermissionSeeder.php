<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'manage-roles',
            'manage-users',
            'manage-files',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // 1. Admin: bisa manage role & permission, bisa manage user, bisa manage file
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['manage-roles', 'manage-users', 'manage-files']);

        // 2. Management User: hanya bisa manage role & permission saja
        $managementUserRole = Role::create(['name' => 'management-user']);
        $managementUserRole->givePermissionTo(['manage-roles']);

        // 3. Management File: hanya manage file saja
        $managementFileRole = Role::create(['name' => 'management-file']);
        $managementFileRole->givePermissionTo(['manage-files']);
    }
}

