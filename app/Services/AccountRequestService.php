<?php

namespace App\Services;

use App\Mail\AccountStatusMail;
use App\Mail\PasswordResetLinkMail;
use App\Models\AccountRequest;
use App\Models\Registration;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * The lifecycle of a help request: raised, approved, rejected.
 *
 * All four inboxes (admin/customer × password/activation) run through here so
 * the transaction boundary, the audit entries and the notification email are
 * written once rather than four times.
 */
class AccountRequestService
{
    public function __construct(
        private readonly PasswordResetLinkService $links,
    ) {
    }

    /* ----------------------------------------------------------- creation */

    /**
     * Record a new request.
     *
     * The caller has already proven the email belongs to the named account —
     * that check lives in the form request so the user gets a field-level
     * error, not an exception.
     *
     * @param  array<string, mixed>  $data
     */
    public function raise(string $type, string $requesterType, array $data, Model|null $account): AccountRequest
    {
        return DB::transaction(function () use ($type, $requesterType, $data, $account) {
            $request = AccountRequest::create([
                'type'            => $type,
                'requester_type'  => $requesterType,
                'registration_id' => $account instanceof Registration ? $account->id : null,
                'user_id'         => $account instanceof User ? $account->id : null,
                'name'            => $data['name'],
                'username'        => $data['username'] ?? null,
                'email'           => mb_strtolower(trim($data['email'])),
                'phone'           => $data['phone'] ?? null,
                'requested_role'  => $data['requested_role'] ?? ($account instanceof User ? $account->role : null),
                'reason'          => $data['reason'] ?? null,
                'message'         => $data['message'] ?? null,
                'status'          => AccountRequest::STATUS_PENDING,
                'ip_address'      => request()->ip(),
                'user_agent'      => request()->userAgent(),
            ]);

            AuditLogger::log(
                $type === AccountRequest::TYPE_PASSWORD
                    ? AuditLogger::ACTION_RESET_REQUESTED
                    : AuditLogger::ACTION_ACTIVATION_REQUESTED,
                ucfirst($requesterType) . ' ' . $request->email . ' requested '
                    . ($type === AccountRequest::TYPE_PASSWORD ? 'a password reset' : 'account reactivation'),
                $request,
                null,
                null,
                'Account Requests',
            );

            return $request;
        });
    }

    /* ---------------------------------------------------------- approval */

    /**
     * Approve a password reset: mint a signed single-use link and email it.
     *
     * A plain password is never generated or sent.
     *
     * @return array{ok: bool, message: string}
     */
    public function approvePasswordReset(AccountRequest $request, ?string $note, User $approver): array
    {
        $account = $request->account();

        if (!$account) {
            return ['ok' => false, 'message' => 'No account matches ' . $request->email . '.'];
        }

        $issued = DB::transaction(function () use ($request, $note, $approver, $account) {
            $issued = $request->isFromAdmin()
                ? $this->links->issueForAdmin($account, $request, $approver)
                : $this->links->issueForCustomer($account, $request, $approver);

            $this->markResolved($request, $note, $approver);

            AuditLogger::log(
                AuditLogger::ACTION_RESET_APPROVED,
                $approver->name . ' approved the password reset for ' . $request->email
                    . ' and issued a single-use link',
                $request,
                null,
                ['link_expires_at' => $issued['link']->expires_at->toDateTimeString()],
                'Account Requests',
            );

            return $issued;
        });

        $sent = $this->send($request->email, new PasswordResetLinkMail(
            name:           $request->name,
            email:          $request->email,
            url:            $issued['url'],
            expiresMinutes: $this->links->expiryMinutes(),
            note:           $note,
            isAdmin:        $request->isFromAdmin(),
        ));

        return [
            'ok'      => true,
            'message' => $sent
                ? 'A single-use reset link was emailed to ' . $request->email . '.'
                : 'The link was created, but the email could not be sent. Ask the user to request again.',
        ];
    }

    /**
     * Approve a reactivation: switch the account back on and say so.
     *
     * @return array{ok: bool, message: string}
     */
    public function approveActivation(AccountRequest $request, ?string $note, User $approver): array
    {
        $account = $request->account();

        if (!$account) {
            return ['ok' => false, 'message' => 'No account matches ' . $request->email . '.'];
        }

        DB::transaction(function () use ($request, $note, $approver, $account) {
            $account->activate();

            $this->markResolved($request, $note, $approver);

            AuditLogger::log(
                AuditLogger::ACTION_ACTIVATION_APPROVED,
                $approver->name . ' reactivated the account ' . $request->email,
                $request,
                ['is_active' => false],
                ['is_active' => true],
                'Account Requests',
            );

            AuditLogger::system(
                AuditLogger::ACTION_USER_ACTIVATED,
                $request->isFromAdmin() ? 'Admin Users' : 'Customers',
                'Account ' . $request->email . ' was reactivated',
                $account,
            );
        });

        $sent = $this->send($request->email, new AccountStatusMail(
            name:    $request->name,
            email:   $request->email,
            state:   AccountStatusMail::ACTIVATED,
            note:    $note,
            isAdmin: $request->isFromAdmin(),
        ));

        return [
            'ok'      => true,
            'message' => $sent
                ? $request->email . ' can sign in again and has been emailed.'
                : $request->email . ' was reactivated, but the notification email failed to send.',
        ];
    }

    /**
     * Turn a request down, with a reason that is emailed to the requester.
     *
     * @return array{ok: bool, message: string}
     */
    public function reject(AccountRequest $request, string $reason, User $approver): array
    {
        DB::transaction(function () use ($request, $reason, $approver) {
            $request->update([
                'status'          => AccountRequest::STATUS_REJECTED,
                'admin_note'      => $reason,
                'handled_by'      => $approver->id,
                'handled_by_name' => $approver->name,
                'handled_at'      => now(),
            ]);

            AuditLogger::log(
                $request->isPasswordReset()
                    ? AuditLogger::ACTION_RESET_REJECTED
                    : AuditLogger::ACTION_ACTIVATION_REJECTED,
                $approver->name . ' rejected the ' . $request->typeLabel() . ' request from ' . $request->email,
                $request,
                null,
                ['reason' => $reason],
                'Account Requests',
            );
        });

        $this->send($request->email, new AccountStatusMail(
            name:    $request->name,
            email:   $request->email,
            state:   AccountStatusMail::REJECTED,
            note:    $reason,
            isAdmin: $request->isFromAdmin(),
        ));

        return ['ok' => true, 'message' => 'Request rejected and the requester was notified.'];
    }

    /* ------------------------------------------------------------ helpers */

    /**
     * True when an identical request from the same address is still pending
     * inside the cooldown window.
     */
    public function isThrottled(string $type, string $email): bool
    {
        return AccountRequest::where('email', mb_strtolower(trim($email)))
            ->where('type', $type)
            ->where('status', AccountRequest::STATUS_PENDING)
            ->where('created_at', '>=', now()->subMinutes((int) config('security.account_requests.cooldown_minutes', 15)))
            ->exists();
    }

    private function markResolved(AccountRequest $request, ?string $note, User $approver): void
    {
        $request->update([
            'status'          => AccountRequest::STATUS_RESOLVED,
            'admin_note'      => $note,
            'handled_by'      => $approver->id,
            'handled_by_name' => $approver->name,
            'handled_at'      => now(),
        ]);
    }

    /**
     * Queued so a slow SMTP handshake never blocks the response, and wrapped so
     * a dead transport cannot roll back an action the admin already took —
     * the flash message reports the failure instead.
     */
    private function send(string $email, $mailable): bool
    {
        try {
            Mail::to($email)->queue($mailable);

            AuditLogger::system(
                AuditLogger::ACTION_EMAIL_SENT,
                'Account Requests',
                'Queued ' . class_basename($mailable) . ' to ' . $email,
            );

            return true;
        } catch (\Throwable $e) {
            Log::warning('Account request mail failed: ' . $e->getMessage());

            return false;
        }
    }
}
