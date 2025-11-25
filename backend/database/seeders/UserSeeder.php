<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan roles sudah ada
        $adminRole = Role::where('name', 'admin')->first();
        $managementUserRole = Role::where('name', 'management-user')->first();
        $managementFileRole = Role::where('name', 'management-file')->first();

        if (!$adminRole || !$managementUserRole || !$managementFileRole) {
            $this->command->error('Roles not found. Please run RolePermissionSeeder first.');
            return;
        }

        // ============================================
        // SKENARIO 1: User dengan 1 Role (Single Role)
        // ============================================
        $this->command->info('Creating users with single role...');

        // User dengan role admin saja
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('admin');
        $this->command->info('✓ Created: admin@example.com (Role: admin)');

        // User dengan role management-user saja
        $managementUser = User::create([
            'name' => 'Management User',
            'email' => 'management.user@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $managementUser->assignRole('management-user');
        $this->command->info('✓ Created: management.user@example.com (Role: management-user)');

        // User dengan role management-file saja
        $managementFile = User::create([
            'name' => 'Management File',
            'email' => 'management.file@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $managementFile->assignRole('management-file');
        $this->command->info('✓ Created: management.file@example.com (Role: management-file)');

        // ============================================
        // SKENARIO 2: User dengan Multiple Roles
        // ============================================
        $this->command->info('Creating users with multiple roles...');

        // User dengan multiple roles (admin + management-user)
        $multiRoleUser1 = User::create([
            'name' => 'Multi Role User 1',
            'email' => 'multi1@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $multiRoleUser1->assignRole(['admin', 'management-user']);
        $this->command->info('✓ Created: multi1@example.com (Roles: admin, management-user)');

        // User dengan multiple roles (admin + management-file)
        $multiRoleUser2 = User::create([
            'name' => 'Multi Role User 2',
            'email' => 'multi2@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $multiRoleUser2->assignRole(['admin', 'management-file']);
        $this->command->info('✓ Created: multi2@example.com (Roles: admin, management-file)');

        // User dengan multiple roles (management-user + management-file)
        $multiRoleUser3 = User::create([
            'name' => 'Multi Role User 3',
            'email' => 'multi3@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $multiRoleUser3->assignRole(['management-user', 'management-file']);
        $this->command->info('✓ Created: multi3@example.com (Roles: management-user, management-file)');

        // User dengan multiple roles (admin + management-user + management-file)
        $multiRoleUser4 = User::create([
            'name' => 'Multi Role User 4',
            'email' => 'multi4@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $multiRoleUser4->assignRole(['admin', 'management-user', 'management-file']);
        $this->command->info('✓ Created: multi4@example.com (Roles: admin, management-user, management-file)');

        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('User Seeding Summary:');
        $this->command->info('========================================');
        $this->command->info('Skenario 1 - Single Role:');
        $this->command->info('  • admin@example.com (admin)');
        $this->command->info('  • management.user@example.com (management-user)');
        $this->command->info('  • management.file@example.com (management-file)');
        $this->command->newLine();
        $this->command->info('Skenario 2 - Multiple Roles:');
        $this->command->info('  • multi1@example.com (admin, management-user)');
        $this->command->info('  • multi2@example.com (admin, management-file)');
        $this->command->info('  • multi3@example.com (management-user, management-file)');
        $this->command->info('  • multi4@example.com (admin, management-user, management-file)');
        $this->command->newLine();
        $this->command->info('All users password: password');
        $this->command->info('========================================');
    }
}
