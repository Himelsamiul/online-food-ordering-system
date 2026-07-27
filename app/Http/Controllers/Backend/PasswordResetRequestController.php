<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\HandlesAccountRequests;
use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Admin password assistance requests — SUPERADMIN ONLY.
 *
 * Approving mints a signed, single-use reset link and emails it to the address
 * on the account. No password is ever generated or sent.
 */
class PasswordResetRequestController extends Controller
{
    use HandlesAccountRequests;

    protected function baseQuery(): Builder
    {
        return AccountRequest::query()->fromAdmins()->passwordResets();
    }

    protected function viewName(): string
    {
        return 'backend.pages.admin-requests.password-resets';
    }

    protected function routePrefix(): string
    {
        return 'admin.password-reset-requests';
    }

    protected function exportLabel(): string
    {
        return 'Admin Password Reset Requests';
    }
}
