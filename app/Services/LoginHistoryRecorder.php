<?php

namespace App\Services;

use App\Models\AdminLoginHistory;
use App\Models\LoginHistory;
use App\Models\Registration;
use App\Models\User;
use App\Support\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Writes login history for both guards.
 *
 * Every method is best-effort: history is evidence, not a precondition, so a
 * failure here must never stop somebody signing in or out.
 */
class LoginHistoryRecorder
{
    /** Session key holding the open admin history row, so logout can close it. */
    public const ADMIN_SESSION_KEY = 'admin_login_history_id';

    /** Session key for the customer equivalent. */
    public const CUSTOMER_SESSION_KEY = 'customer_login_history_id';

    /**
     * One row per admin sign-in attempt, successful or not. The failures are
     * the more interesting half — that is how a brute force shows up.
     */
    public function recordAdminAttempt(
        Request $request,
        ?User $user,
        bool $successful,
        ?string $failureReason = null,
    ): ?AdminLoginHistory {
        try {
            $agent    = Agent::fromRequest($request);
            $location = $this->locate($request->ip());

            $history = AdminLoginHistory::create([
                'user_id'        => $user?->id,
                'user_name'      => $user?->name ?? 'Unknown',
                'user_email'     => $user?->email ?? $request->input('email'),
                'user_role'      => $user?->role ?? '—',
                'ip_address'     => $request->ip(),
                'country'        => $location['country'],
                'city'           => $location['city'],
                'user_agent'     => $agent['user_agent'],
                'browser'        => $agent['browser'],
                'device'         => $agent['device'],
                'platform'       => $agent['platform'],
                'session_id'     => $this->sessionId($request),
                'successful'     => $successful,
                'failure_reason' => $failureReason,
                'logged_in_at'   => now(),
            ]);

            if ($successful) {
                $request->session()->put(self::ADMIN_SESSION_KEY, $history->id);
            }

            return $history;
        } catch (\Throwable $e) {
            Log::warning('Admin login history write failed: ' . $e->getMessage());

            return null;
        }
    }

    public function closeAdminSession(Request $request, ?User $user, string $type = 'manual'): void
    {
        try {
            $id = $request->session()->pull(self::ADMIN_SESSION_KEY);

            $history = $id
                ? AdminLoginHistory::find($id)
                : ($user
                    ? AdminLoginHistory::where('user_id', $user->id)
                        ->where('successful', true)
                        ->whereNull('logged_out_at')
                        ->latest('logged_in_at')
                        ->first()
                    : null);

            $history?->update(['logged_out_at' => now(), 'logout_type' => $type]);
        } catch (\Throwable $e) {
            Log::warning('Admin logout history update failed: ' . $e->getMessage());
        }
    }

    /* ------------------------------------------------------------ customer */

    public function recordCustomerLogin(Request $request, Registration $customer, ?string $ipOverride = null): ?LoginHistory
    {
        try {
            $ip       = $ipOverride ?: $request->ip();
            $agent    = Agent::fromRequest($request);
            $location = $this->locate($ip);

            $history = LoginHistory::create([
                'registration_id' => $customer->id,
                'ip_address'      => $ip,
                'country'         => $location['country'],
                'city'            => $location['city'],
                'user_agent'      => $agent['user_agent'],
                'browser'         => $agent['browser'],
                'device'          => $agent['device'],
                'platform'        => $agent['platform'],
                'session_id'      => $this->sessionId($request),
                'successful'      => true,
                'logged_in_at'    => now(),
            ]);

            $request->session()->put(self::CUSTOMER_SESSION_KEY, $history->id);

            return $history;
        } catch (\Throwable $e) {
            Log::warning('Customer login history write failed: ' . $e->getMessage());

            return null;
        }
    }

    public function closeCustomerSession(Request $request, ?Registration $customer): void
    {
        try {
            $id = $request->session()->pull(self::CUSTOMER_SESSION_KEY);

            $history = $id
                ? LoginHistory::find($id)
                : ($customer
                    ? LoginHistory::where('registration_id', $customer->id)
                        ->whereNull('logged_out_at')
                        ->latest('logged_in_at')
                        ->first()
                    : null);

            $history?->update(['logged_out_at' => now()]);
        } catch (\Throwable $e) {
            Log::warning('Customer logout history update failed: ' . $e->getMessage());
        }
    }

    /* -------------------------------------------------------------- detail */

    private function sessionId(Request $request): ?string
    {
        try {
            return $request->hasSession() ? $request->session()->getId() : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * GeoIP is optional — a LAN address has no location and the lookup must
     * never be allowed to block a sign-in.
     *
     * @return array{country: string|null, city: string|null}
     */
    private function locate(?string $ip): array
    {
        try {
            $location = \Torann\GeoIP\Facades\GeoIP::getLocation($ip);

            return ['country' => $location->country, 'city' => $location->city];
        } catch (\Throwable $e) {
            return ['country' => null, 'city' => null];
        }
    }
}
