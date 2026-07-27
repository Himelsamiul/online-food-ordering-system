<?php

namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\AdminActivationFormRequest;
use App\Http\Requests\Backend\PasswordAssistanceRequest;
use App\Models\AccountRequest;
use App\Services\AccountRequestService;
use Illuminate\Http\Request;

/**
 * The two public forms an admin who cannot get in fills out.
 *
 * Password assistance — for every admin who is not a superadmin. They cannot
 * reset themselves; a superadmin reviews the request and issues a signed,
 * single-use reset link.
 *
 * Activation — for an admin whose account has been switched off.
 *
 * Both validate that the address on the form is exactly the one on the account
 * (see App\Rules\RegisteredAccountEmail), which is what lets the superadmin
 * treat the request as coming from the account owner.
 */
class PasswordAssistanceController extends Controller
{
    public function __construct(private readonly AccountRequestService $requests)
    {
    }

    /* -------------------------------------------------- password assistance */

    public function createPasswordRequest(Request $request)
    {
        return view('backend.auth.password-assistance', [
            'prefillEmail' => $request->query('email', old('email')),
        ]);
    }

    public function storePasswordRequest(PasswordAssistanceRequest $request)
    {
        $admin = $request->account();

        if ($this->requests->isThrottled(AccountRequest::TYPE_PASSWORD, $request->input('email'))) {
            return back()->withInput()->with(
                'info',
                'A request for this account is already waiting for review. A super admin will get to it shortly.'
            );
        }

        $this->requests->raise(
            AccountRequest::TYPE_PASSWORD,
            AccountRequest::FROM_ADMIN,
            [
                'name'           => $request->input('name'),
                'username'       => $request->input('username'),
                'email'          => $request->input('email'),
                'requested_role' => $request->input('role') ?: $admin?->role,
                'reason'         => $request->input('reason'),
                'message'        => $request->input('notes'),
            ],
            $admin,
        );

        return redirect()->route('admin.login')->with(
            'success',
            'Your request has been sent to the super admin. You will receive an email at '
            . $request->input('email') . ' once it has been reviewed.'
        );
    }

    /* --------------------------------------------------------- activation */

    public function createActivationRequest(Request $request)
    {
        return view('backend.auth.activation-request', [
            'prefillEmail' => $request->query('email', old('email')),
        ]);
    }

    public function storeActivationRequest(AdminActivationFormRequest $request)
    {
        $admin = $request->account();

        if ($this->requests->isThrottled(AccountRequest::TYPE_ACTIVATION, $request->input('email'))) {
            return back()->withInput()->with(
                'info',
                'An activation request for this account is already waiting for review.'
            );
        }

        $this->requests->raise(
            AccountRequest::TYPE_ACTIVATION,
            AccountRequest::FROM_ADMIN,
            [
                'name'           => $request->input('name'),
                'username'       => $request->input('username'),
                'email'          => $request->input('email'),
                'requested_role' => $admin?->role,
                'reason'         => $request->input('reason'),
                'message'        => $request->input('notes'),
            ],
            $admin,
        );

        return redirect()->route('admin.login')->with(
            'success',
            'Your activation request has been sent. You will be emailed once a super admin has reviewed it.'
        );
    }
}
