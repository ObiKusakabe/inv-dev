<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::Create(
            [
                'name' => 'Admin',
                'email' => 'admin@1.com',
                'password' => Hash::make('123'),
                'role' => 'admin', // pastikan ada kolom role
                // 'support_email' => 'support@example.com', // kolom tambahan
                // 'email_verified_at' => now(),
            ]
        );
    }
}
