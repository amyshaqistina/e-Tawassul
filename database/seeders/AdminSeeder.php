<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds one demo admin so you can log in and verify crisis reports
 * during testing. Run via:
 *
 *   php artisan db:seed --class=AdminSeeder
 *
 * Safe to re-run — uses updateOrCreate.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@iium.edu.my'],
            [
                'admin_name'  => 'Dr. Azmi (Demo Admin)',
                'role'        => 'super_admin',
                'active'      => true,
                'permissions' => [
                    'verify_crisis',
                    'verify_death',
                    'trigger_ldms',
                    'manage_donations',
                    'view_blockchain',
                ],
                'password'    => Hash::make('password'),
            ]
        );

        $this->command->info('Demo admin ready:');
        $this->command->line('  email:    admin@iium.edu.my');
        $this->command->line('  password: password');
    }
}
