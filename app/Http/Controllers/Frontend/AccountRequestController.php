<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\CustomerActivationFormRequest;
use App\Http\Requests\Frontend\CustomerPasswordAssistanceRequest;
use App\Models\AccountRequest;
use App\Services\AccountRequestService;
use Illuminate\Http\Request;

/**
 * The customer-facing "I can't get in, please help" forms.
 *
 * Public on purpose — everyone who needs these is by definition locked out.
 *
 * Both forms require the address on the form to be exactly the one on the
 * account (App\Rules\RegisteredAccountEmail), which is what lets the admin
 * treat the request as coming from the account owner.
 */
class AccountRequestController extends Controller
{
    public function __construct(private readonly AccountRequestService $requests)
    {
    }

    /**
     * $type is "password" or "activation" — the short word kept in the URL.
     */
    public function create(Request $request, string $type = 'password')
    {
        $stored = $this->normaliseType($type);

        return view('frontend.pages.auth.account-request', [
            'type'    => $stored,
            'prefill' => [
                'email' => $request->query('email', old('email')),
                'name'  => old('name'),
            ],
        ]);
    }

    public function storePasswordRequest(CustomerPasswordAssistanceRequest $request)
    {
        if ($this->requests->isThrottled(AccountRequest::TYPE_PASSWORD, $request->input('email'))) {
            return $this->throttledResponse('password');
        }

        $this->requests->raise(
            AccountRequest::TYPE_PASSWORD,
            AccountRequest::FROM_CUSTOMER,
            $request->only(['name', 'email', 'phone', 'reason']),
            $request->account(),
        );

        return redirect()->route('login')->with(
            'success',
            'Your request has been sent to the admin. You will get an email at '
            . $request->input('email') . ' once it has been handled.'
        );
    }

    public function storeActivationRequest(CustomerActivationFormRequest $request)
    {
        if ($this->requests->isThrottled(AccountRequest::TYPE_ACTIVATION, $request->input('email'))) {
            return $this->throttledResponse('activation');
        }

        $this->requests->raise(
            AccountRequest::TYPE_ACTIVATION,
            AccountRequest::FROM_CUSTOMER,
            $request->only(['name', 'email', 'phone', 'reason']),
            $request->account(),
        );

        return redirect()->route('login')->with(
            'success',
            'Your reactivation request has been sent. You will be emailed once an admin has reviewed it.'
        );
    }

    private function throttledResponse(string $slug)
    {
        // Redirect by name, not back() — back() falls through to "/" whenever
        // the Referer header is absent, which would lose the typed form.
        return redirect()->route('account.help', $slug)->withInput()->with(
            'info',
            'You already have a request waiting for this email. An admin will get to it shortly.'
        );
    }

    private function normaliseType(string $type): string
    {
        return $type === 'activation'
            ? AccountRequest::TYPE_ACTIVATION
            : AccountRequest::TYPE_PASSWORD;
    }
}
