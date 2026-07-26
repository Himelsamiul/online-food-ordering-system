<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'group'];

    /**
     * Canonical list of grantable permissions, grouped for the UI.
     * Keys are the permission "name" used in can:xxx / @can('xxx').
     *
     * Note: managing admin users is intentionally NOT here — it is
     * superadmin-only and never grantable to a normal admin.
     */
    public const CATALOG = [
        'POS' => [
            'pos' => 'POS Billing',
        ],
        'Catalog' => [
            'foods'         => 'Foods',
            'categories'    => 'Categories',
            'subcategories' => 'Subcategories',
            'units'         => 'Units',
        ],
        'Sales' => [
            'orders' => 'Orders',
        ],
        'Marketing' => [
            'coupons'    => 'Coupons & Offers',
            'promotions' => 'Promo Banners',
        ],
        'Delivery' => [
            'delivery_men'  => 'Delivery Men',
            'delivery_runs' => 'Delivery Runs',
        ],
        'People' => [
            'customers'        => 'Customers & Login History',
            'contact_messages' => 'Contact Messages',
        ],
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
