<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;

/**
 * Starter delivery areas.
 *
 * Without at least one zone the checkout page has nothing to select and no
 * order can be placed, so this seeds a workable default set. Safe to re-run —
 * rows are matched on name and existing charges are left alone.
 */
class DeliveryZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            ['name' => 'Inside Dhaka — Near',  'areas' => 'Dhanmondi, Mohammadpur, Lalmatia', 'charge' => 40,  'eta_minutes' => 30, 'free_above' => 1000, 'sort_order' => 1],
            ['name' => 'Inside Dhaka — Far',   'areas' => 'Uttara, Mirpur, Bashundhara',      'charge' => 70,  'eta_minutes' => 50, 'free_above' => 1500, 'sort_order' => 2],
            ['name' => 'Outside Dhaka',        'areas' => 'Savar, Gazipur, Narayanganj',      'charge' => 120, 'eta_minutes' => 90, 'min_order' => 500,   'sort_order' => 3],
        ];

        foreach ($zones as $zone) {
            DeliveryZone::firstOrCreate(
                ['name' => $zone['name']],
                $zone + ['is_active' => true]
            );
        }
    }
}
