<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admins = [
            // The owner — full control, can manage other admins.
            [
                'name'        => 'Admin One',
                'email'       => 'admin1@gmail.com',
                'password'    => '1111',
                'role'        => 'superadmin',
                'permissions' => [], // superadmin needs none — it has everything
            ],
            // Sample limited admins.
            [
                'name'        => 'Admin Two',
                'email'       => 'admin2@gmail.com',
                'password'    => '2222',
                'role'        => 'admin',
                'permissions' => ['pos', 'orders'],
            ],
            [
                'name'        => 'Admin Three',
                'email'       => 'admin3@gmail.com',
                'password'    => '3333',
                'role'        => 'admin',
                'permissions' => ['foods', 'categories', 'subcategories', 'units'],
            ],
            [
                'name'        => 'Admin Four',
                'email'       => 'admin4@gmail.com',
                'password'    => '4444',
                'role'        => 'admin',
                'permissions' => ['delivery_men', 'delivery_runs'],
            ],
            [
                'name'        => 'Admin Five',
                'email'       => 'admin5@gmail.com',
                'password'    => '5555',
                'role'        => 'admin',
                'permissions' => [], // no access granted yet
            ],
        ];

        foreach ($admins as $admin) {
            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name'     => $admin['name'],
                    'password' => Hash::make($admin['password']),
                    'is_admin' => true,
                    'role'     => $admin['role'],
                ]
            );

            $permissionIds = Permission::whereIn('name', $admin['permissions'])->pluck('id');
            $user->permissions()->sync($permissionIds);
        }
    }
}
