<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\HandlesAccountRequests;
use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Customer help requests — password resets and reactivations raised from the
 * storefront.
 *
 * Guarded by the grantable `account_requests` permission, so a support admin
 * can be given this inbox without any access to admin accounts.
 */
class AccountRequestController extends Controller
{
    use HandlesAccountRequests;

    protected function baseQuery(): Builder
    {
        return AccountRequest::query()->fromCustomers();
    }

    protected function viewName(): string
    {
        return 'backend.pages.account-requests.index';
    }

    protected function routePrefix(): string
    {
        return 'admin.account-requests';
    }

    protected function exportLabel(): string
    {
        return 'Customer Account Requests';
    }
}
