<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Concerns\HandlesAccountRequests;
use App\Http\Controllers\Controller;
use App\Models\AccountRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * Requests from admins whose accounts have been switched off — SUPERADMIN ONLY.
 */
class AdminActivationRequestController extends Controller
{
    use HandlesAccountRequests;

    protected function baseQuery(): Builder
    {
        return AccountRequest::query()->fromAdmins()->activations();
    }

    protected function viewName(): string
    {
        return 'backend.pages.admin-requests.activations';
    }

    protected function routePrefix(): string
    {
        return 'admin.admin-activation-requests';
    }

    protected function exportLabel(): string
    {
        return 'Admin Activation Requests';
    }
}
