<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permission::CATALOG as $group => $items) {
            foreach ($items as $name => $label) {
                Permission::updateOrCreate(
                    ['name' => $name],
                    ['label' => $label, 'group' => $group]
                );
            }
        }
    }
}
