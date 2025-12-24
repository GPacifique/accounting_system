<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user into the database.
     */
    public function run(): void
    {
        // Create system admin user
        User::firstOrCreate(
            ['email' => 'admin@itsinda.local'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@itsinda.local',
                'password' => Hash::make('AdminPassword123!'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create demo users
        User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'password' => Hash::make('DemoPassword123!'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );

        // Create another demo admin for testing
        User::firstOrCreate(
            ['email' => 'groupadmin@example.com'],
            [
                'name' => 'Group Administrator',
                'email' => 'groupadmin@example.com',
                'password' => Hash::make('GroupAdminPass123!'),
                'is_admin' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
