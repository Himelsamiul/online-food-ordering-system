<?php

use App\Models\Permission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Permissions were one row per module ("foods"). They are now one row per
     * module+action ("foods.view", "foods.delete", ...).
     *
     * Existing grants must not silently shrink: an admin who held "foods"
     * could do everything to foods, so they are given every foods.* action.
     */
    public function up(): void
    {
        // 1. make sure every granular permission exists
        foreach (Permission::catalog() as $name => $meta) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        // 2. re-point every existing grant at the matching granular rows
        $legacy = Permission::whereRaw("name NOT LIKE '%.%'")->get();

        foreach ($legacy as $old) {
            $replacements = Permission::where('name', 'like', $old->name . '.%')->pluck('id');

            if ($replacements->isEmpty()) {
                continue;
            }

            $holders = DB::table('permission_user')
                ->where('permission_id', $old->id)
                ->pluck('user_id');

            foreach ($holders as $userId) {
                foreach ($replacements as $newId) {
                    DB::table('permission_user')->updateOrInsert(
                        ['user_id' => $userId, 'permission_id' => $newId],
                        ['user_id' => $userId, 'permission_id' => $newId]
                    );
                }
            }
        }

        // 3. drop the old module-level rows and their now-duplicated links
        $legacyIds = $legacy->pluck('id');

        if ($legacyIds->isNotEmpty()) {
            DB::table('permission_user')->whereIn('permission_id', $legacyIds)->delete();
            Permission::whereIn('id', $legacyIds)->delete();
        }
    }

    /**
     * Collapse back to module-level rows, keeping anyone who held any action.
     */
    public function down(): void
    {
        $modules = [];

        foreach (Permission::MODULES as $group => $items) {
            foreach ($items as $module => $meta) {
                $modules[$module] = ['label' => $meta['label'], 'group' => $group];
            }
        }

        foreach ($modules as $module => $meta) {
            $old = Permission::updateOrCreate(
                ['name' => $module],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );

            $granularIds = Permission::where('name', 'like', $module . '.%')->pluck('id');

            $holders = DB::table('permission_user')
                ->whereIn('permission_id', $granularIds)
                ->distinct()
                ->pluck('user_id');

            foreach ($holders as $userId) {
                DB::table('permission_user')->updateOrInsert(
                    ['user_id' => $userId, 'permission_id' => $old->id],
                    ['user_id' => $userId, 'permission_id' => $old->id]
                );
            }

            DB::table('permission_user')->whereIn('permission_id', $granularIds)->delete();
            Permission::whereIn('id', $granularIds)->delete();
        }
    }
};
