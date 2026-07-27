<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['name', 'label', 'group'];

    /**
     * Every action a permission can cover, with the label the admin UI shows.
     *
     * "view" doubles as sidebar visibility: an admin who cannot view a module
     * never sees its menu entry, which is how the superadmin controls exactly
     * which sidebar links each admin gets.
     */
    public const ACTIONS = [
        'view'   => 'View',
        'create' => 'Create',
        'edit'   => 'Edit',
        'delete' => 'Delete',
    ];

    /** Icon shown next to each action in the permission matrix. */
    public const ACTION_ICONS = [
        'view'   => 'feather-eye',
        'create' => 'feather-plus-circle',
        'edit'   => 'feather-edit-2',
        'delete' => 'feather-trash-2',
    ];

    /**
     * The permission matrix, grouped for the UI.
     *
     * Permission names are "<module>.<action>" — e.g. foods.delete. A bare
     * module name (foods) is NOT a stored permission; it is answered by
     * User::hasPermission() as "holds any action on this module".
     *
     * Modules only list the actions that actually exist for them — orders
     * cannot be created from the admin panel, activity logs cannot be edited.
     *
     * Note: managing admin users is intentionally NOT here — it is
     * superadmin-only and never grantable to a normal admin.
     */
    public const MODULES = [
        'POS' => [
            'pos' => ['label' => 'POS Billing', 'icon' => 'feather-shopping-cart', 'actions' => ['view', 'create']],
        ],
        'Catalog' => [
            'foods'         => ['label' => 'Foods',         'icon' => 'feather-shopping-bag', 'actions' => ['view', 'create', 'edit', 'delete']],
            'categories'    => ['label' => 'Categories',    'icon' => 'feather-grid',         'actions' => ['view', 'create', 'edit', 'delete']],
            'subcategories' => ['label' => 'Subcategories', 'icon' => 'feather-layers',       'actions' => ['view', 'create', 'edit', 'delete']],
            'units'         => ['label' => 'Units',         'icon' => 'feather-sliders',      'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Sales' => [
            'orders' => ['label' => 'Orders', 'icon' => 'feather-package', 'actions' => ['view', 'edit', 'delete']],
        ],
        'Marketing' => [
            'coupons'    => ['label' => 'Coupons & Offers', 'icon' => 'feather-gift',  'actions' => ['view', 'create', 'edit', 'delete']],
            'promotions' => ['label' => 'Promo Banners',    'icon' => 'feather-image', 'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'Delivery' => [
            'delivery_men'  => ['label' => 'Delivery Men',  'icon' => 'feather-truck', 'actions' => ['view', 'create', 'edit', 'delete']],
            'delivery_runs' => ['label' => 'Delivery Runs', 'icon' => 'feather-map',   'actions' => ['view', 'create', 'edit', 'delete']],
        ],
        'People' => [
            'customers'         => ['label' => 'Customers',          'icon' => 'feather-users',     'actions' => ['view', 'edit', 'delete']],
            'account_requests'  => ['label' => 'Account Requests',   'icon' => 'feather-inbox',     'actions' => ['view', 'edit', 'delete']],
            'contact_messages'  => ['label' => 'Contact Messages',   'icon' => 'feather-life-buoy', 'actions' => ['view', 'delete']],
        ],
        'Monitoring' => [
            'activity_log'        => ['label' => 'Activity Log',        'icon' => 'feather-activity',  'actions' => ['view', 'delete']],
            'login_history'       => ['label' => 'Customer Logins',     'icon' => 'feather-log-in',    'actions' => ['view', 'delete']],
            'admin_login_history' => ['label' => 'Admin Logins',        'icon' => 'feather-shield',    'actions' => ['view', 'delete']],
        ],
    ];

    /**
     * Human sentence for each action, used as the checkbox tooltip so an
     * admin granting rights knows exactly what they are handing over.
     */
    public static function actionHint(string $module, string $action): string
    {
        $label = strtolower(self::moduleLabel($module));

        return match ($action) {
            'view'   => "Open the {$label} pages and see the sidebar link",
            'create' => "Add new {$label}",
            'edit'   => "Change existing {$label}",
            'delete' => "Permanently remove {$label}",
            default  => ucfirst($action) . ' ' . $label,
        };
    }

    /**
     * Flat map of every permission name => metadata, in catalog order.
     *
     * @return array<string, array{label: string, group: string, module: string, action: string}>
     */
    public static function catalog(): array
    {
        $flat = [];

        foreach (self::MODULES as $group => $modules) {
            foreach ($modules as $module => $meta) {
                foreach ($meta['actions'] as $action) {
                    $flat["{$module}.{$action}"] = [
                        'label'  => self::ACTIONS[$action] . ' ' . $meta['label'],
                        'group'  => $group,
                        'module' => $module,
                        'action' => $action,
                    ];
                }
            }
        }

        return $flat;
    }

    /**
     * Every valid permission name, for validation.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        return array_keys(self::catalog());
    }

    /**
     * Every module key => its metadata, flattened out of the groups.
     *
     * @return array<string, array{label: string, icon: string, actions: list<string>, group: string}>
     */
    public static function modules(): array
    {
        $flat = [];

        foreach (self::MODULES as $group => $modules) {
            foreach ($modules as $module => $meta) {
                $flat[$module] = $meta + ['group' => $group];
            }
        }

        return $flat;
    }

    public static function moduleLabel(string $module): string
    {
        return self::modules()[$module]['label'] ?? ucfirst(str_replace('_', ' ', $module));
    }

    /**
     * The module a permission belongs to: foods.delete => foods.
     */
    public static function moduleOf(string $permission): string
    {
        return explode('.', $permission, 2)[0];
    }

    /**
     * Every permission name belonging to a module, e.g. foods => [foods.view, ...].
     *
     * @return list<string>
     */
    public static function namesForModule(string $module): array
    {
        $actions = self::modules()[$module]['actions'] ?? [];

        return array_map(fn ($action) => "{$module}.{$action}", $actions);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
