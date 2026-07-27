<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Brings the permissions table in line with Permission::MODULES.
 *
 * Safe to re-run: rows are matched on name, and anything no longer in the
 * catalog is dropped along with its grants, so a removed module cannot leave
 * an orphaned permission that nothing checks.
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Permission::catalog() as $name => $meta) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        Permission::whereNotIn('name', Permission::names())->each(function (Permission $stale) {
            $stale->users()->detach();
            $stale->delete();
        });
    }
}
