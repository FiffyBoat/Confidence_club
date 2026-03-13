<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'phone' => '0240000000',
                'role' => 'admin',
                'is_active' => true,
                'password' => Hash::make('admin12345'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'treasurer@example.com'],
            [
                'name' => 'Head Treasurer',
                'phone' => '0240000001',
                'role' => 'treasurer',
                'is_active' => true,
                'password' => Hash::make('treasurer12345'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'viewer@example.com'],
            [
                'name' => 'Transparency Viewer',
                'phone' => '0240000002',
                'role' => 'viewer',
                'is_active' => true,
                'password' => Hash::make('viewer12345'),
            ]
        );
    }
}
