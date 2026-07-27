<?php

namespace App\Support;

use App\Models\AccountRequest;
use App\Models\ContactMessage;
use App\Models\Food;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Everything the header bell shows.
 *
 * Each source is gated on the same permission that guards its page — there is
 * no point telling an admin about orders they are not allowed to open, and a
 * notification that 403s when clicked is worse than no notification.
 */
class AdminNotifications
{
    /** Nothing older than this is worth surfacing as "new". */
    public const WINDOW_DAYS = 14;

    /** Hard cap so the dropdown never turns into an endless scroll. */
    public const MAX_ITEMS = 20;

    /**
     * The header and the sidebar are separate view composers but want the same
     * numbers, so the work is done once per request.
     *
     * @var array<string, mixed>
     */
    private static array $cache = [];

    /**
     * @return array{items: Collection, total: int, unread: int, seen_at: Carbon|null}
     */
    public static function for(?User $admin): array
    {
        if (!$admin) {
            return ['items' => collect(), 'total' => 0, 'unread' => 0, 'seen_at' => null];
        }

        $key = 'feed:' . $admin->id;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $seenAt = $admin->notifications_seen_at ? Carbon::parse($admin->notifications_seen_at) : null;
        $since  = now()->subDays(self::WINDOW_DAYS);

        $items = collect()
            ->concat(self::accountRequests($admin))
            ->concat(self::orders($admin, $since))
            ->concat(self::lowStock($admin))
            ->concat(self::contactMessages($admin, $since))
            ->sortByDesc(fn ($item) => $item['time']?->getTimestamp() ?? 0)
            ->values()
            ->take(self::MAX_ITEMS)
            ->map(function ($item) use ($seenAt) {
                $item['is_new'] = $item['always_new']
                    || !$seenAt
                    || ($item['time'] && $item['time']->greaterThan($seenAt));

                return $item;
            });

        return self::$cache[$key] = [
            'items'   => $items,
            'total'   => $items->count(),
            'unread'  => $items->where('is_new', true)->count(),
            'seen_at' => $seenAt,
        ];
    }

    /** Customers waiting on a password reset or reactivation. */
    private static function accountRequests(User $admin): Collection
    {
        if (!$admin->hasPermission('account_requests.view')) {
            return collect();
        }

        return AccountRequest::pending()
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (AccountRequest $req) => [
                'icon'       => $req->typeIcon(),
                'tone'       => $req->typeTone(),
                'title'      => $req->typeLabel() . ' request',
                'body'       => $req->name . ' — ' . $req->email,
                'time'       => $req->created_at,
                'url'        => route('admin.account-requests.index', ['status' => 'pending']),
                'always_new' => false,
            ]);
    }

    /** Orders still sitting in "pending" — the ones that need a decision. */
    private static function orders(User $admin, Carbon $since): Collection
    {
        if (!$admin->hasPermission('orders.view')) {
            return collect();
        }

        return Order::where('order_status', 'pending')
            ->where('created_at', '>=', $since)
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (Order $order) => [
                'icon'       => 'feather-shopping-bag',
                'tone'       => 'primary',
                'title'      => 'New order ' . ($order->order_number ?: '#' . $order->id),
                'body'       => $order->name . ' — ' . number_format((float) $order->total_amount, 2) . ' BDT',
                'time'       => $order->created_at,
                'url'        => route('admin.orders.show', $order->id),
                'always_new' => false,
            ]);
    }

    /**
     * Stock warnings. These have no useful timestamp — a food sitting at 2
     * units is just as urgent today as it was yesterday — so they are always
     * flagged, and sorted in using the row's last update.
     */
    private static function lowStock(User $admin): Collection
    {
        if (!$admin->hasPermission('foods.view')) {
            return collect();
        }

        return Food::whereColumn('quantity', '<=', 'low_stock_alert')
            ->where('status', 1)
            ->orderBy('quantity')
            ->limit(6)
            ->get()
            ->map(fn (Food $food) => [
                'icon'       => 'feather-alert-triangle',
                'tone'       => $food->quantity <= 0 ? 'danger' : 'warning',
                'title'      => $food->quantity <= 0 ? 'Out of stock: ' . $food->name : 'Low stock: ' . $food->name,
                'body'       => $food->quantity . ' left (alert at ' . $food->low_stock_alert . ')',
                'time'       => $food->updated_at,
                'url'        => route('admin.foods.index'),
                'always_new' => true,
            ]);
    }

    private static function contactMessages(User $admin, Carbon $since): Collection
    {
        if (!$admin->hasPermission('contact_messages.view')) {
            return collect();
        }

        return ContactMessage::where('created_at', '>=', $since)
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (ContactMessage $msg) => [
                'icon'       => 'feather-mail',
                'tone'       => 'info',
                'title'      => 'Message from ' . $msg->name,
                'body'       => Str::limit((string) $msg->message, 60),
                'time'       => $msg->created_at,
                'url'        => route('admin.aboutus.index'),
                'always_new' => false,
            ]);
    }

    /**
     * Sidebar counters — small numbers next to menu entries. Kept separate
     * from the bell so a page can show a count without building the full list.
     *
     * @return array<string, int>
     */
    public static function sidebarCounts(?User $admin): array
    {
        if (!$admin) {
            return [];
        }

        $key = 'sidebar:' . $admin->id;

        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $counts = [];

        if ($admin->hasPermission('account_requests.view')) {
            $counts['account_requests'] = AccountRequest::pending()->count();
        }

        if ($admin->hasPermission('orders.view')) {
            $counts['orders'] = Order::where('order_status', 'pending')->count();
        }

        if ($admin->hasPermission('foods.view')) {
            $counts['foods'] = Food::whereColumn('quantity', '<=', 'low_stock_alert')
                ->where('status', 1)
                ->count();
        }

        return self::$cache[$key] = array_filter($counts);
    }
}
