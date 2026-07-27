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
            // Sample limited admins. Permissions are granular — "<module>.<action>" —
            // so these also demonstrate handing out view-only vs full control.
            [
                'name'        => 'Admin Two',
                'email'       => 'admin2@gmail.com',
                'password'    => '2222',
                'role'        => 'admin',
                // Runs the till and works orders, but may not delete anything.
                'permissions' => [
                    'pos.view', 'pos.create',
                    'orders.view', 'orders.edit',
                ],
            ],
            [
                'name'        => 'Admin Three',
                'email'       => 'admin3@gmail.com',
                'password'    => '3333',
                'role'        => 'admin',
                // Full catalog control.
                'permissions' => [
                    'foods.view', 'foods.create', 'foods.edit', 'foods.delete',
                    'categories.view', 'categories.create', 'categories.edit', 'categories.delete',
                    'subcategories.view', 'subcategories.create', 'subcategories.edit',
                    'units.view', 'units.create', 'units.edit',
                ],
            ],
            [
                'name'        => 'Admin Four',
                'email'       => 'admin4@gmail.com',
                'password'    => '4444',
                'role'        => 'admin',
                // Delivery desk plus the customer-support inbox.
                'permissions' => [
                    'delivery_men.view', 'delivery_men.create', 'delivery_men.edit',
                    'delivery_runs.view', 'delivery_runs.create', 'delivery_runs.edit',
                    'account_requests.view', 'account_requests.edit',
                    'customers.view',
                ],
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
