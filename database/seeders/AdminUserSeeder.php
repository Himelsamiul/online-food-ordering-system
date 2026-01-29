<?php

namespace Database\Seeders;

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
        $admins = [
            ['name' => 'Admin One', 'email' => 'admin1@gmail.com'],
            ['name' => 'Admin Two', 'email' => 'admin2@gmail.com'],
            ['name' => 'Admin Three', 'email' => 'admin3@gmail.com'],
            ['name' => 'Admin Four', 'email' => 'admin4@gmail.com'],
            ['name' => 'Admin Five', 'email' => 'admin5@gmail.com'],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => Hash::make('admin123'),
                    'is_admin' => true,
                ]
            );
        }
    }
}
