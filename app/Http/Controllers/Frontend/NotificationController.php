<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The storefront notification bell.
 *
 * Every action re-scopes to the signed-in customer's own rows. Route-model
 * binding is deliberately not used for the same reason the admin request
 * inboxes avoid it: /notifications/12/open must not open notification 12 for
 * somebody who does not own it.
 */
class NotificationController extends Controller
{
    /** How many the dropdown shows before "see all". */
    private const DROPDOWN_LIMIT = 8;

    /** Full page listing. */
    public function index(Request $request): View
    {
        $customer = Auth::guard('frontend')->user();

        $notifications = CustomerNotification::query()
            ->for($customer->id)
            ->when($request->query('filter') === 'unread', fn ($q) => $q->unread())
            ->when(
                in_array($request->query('type'), ['order', 'chat', 'account', 'promo'], true),
                fn ($q) => $q->where('type', $request->query('type'))
            )
            ->latest('id')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('frontend.pages.notifications', [
            'notifications' => $notifications,
            'unreadCount'   => CustomerNotification::for($customer->id)->unread()->count(),
            'filter'        => $request->query('filter'),
            'type'          => $request->query('type'),
        ]);
    }

    /** Feed for the header dropdown; also the unread badge's source of truth. */
    public function poll(): JsonResponse
    {
        $customer = Auth::guard('frontend')->user();

        $items = CustomerNotification::query()
            ->for($customer->id)
            ->latest('id')
            ->limit(self::DROPDOWN_LIMIT)
            ->get();

        return response()->json([
            'unread' => CustomerNotification::for($customer->id)->unread()->count(),
            'items'  => $items->map(fn (CustomerNotification $n) => $n->toPayload())->all(),
        ]);
    }

    public function readAll(): JsonResponse
    {
        $customer = Auth::guard('frontend')->user();

        CustomerNotification::for($customer->id)->unread()->update(['read_at' => now()]);

        return response()->json(['unread' => 0]);
    }

    /**
     * Mark one read and go where it points.
     *
     * Redirecting server-side rather than storing the raw url in the dropdown
     * keeps the "mark read" and "navigate" steps atomic — a customer who
     * middle-clicks still gets the row marked.
     */
    public function open(int $notification): RedirectResponse
    {
        $customer = Auth::guard('frontend')->user();

        $row = CustomerNotification::for($customer->id)->find($notification);

        abort_if(!$row, 404);

        if ($row->isUnread()) {
            $row->forceFill(['read_at' => now()])->save();
        }

        return redirect($row->url ?: route('notifications.index'));
    }

    public function destroy(int $notification): RedirectResponse
    {
        $customer = Auth::guard('frontend')->user();

        $row = CustomerNotification::for($customer->id)->find($notification);

        abort_if(!$row, 404);

        $row->delete();

        return back()->with('success', 'Notification removed.');
    }

    public function clear(): RedirectResponse
    {
        $customer = Auth::guard('frontend')->user();

        CustomerNotification::for($customer->id)->delete();

        return back()->with('success', 'All notifications cleared.');
    }
}
