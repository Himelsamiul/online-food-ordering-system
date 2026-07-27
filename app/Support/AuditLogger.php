<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * The audit trail.
 *
 * Every write is best-effort: a logging failure must never take down the action
 * being logged, so the whole thing is wrapped and only reported to the app log.
 *
 * Actor name, role, subject label and client details are all COPIED in rather
 * than joined. The point of an audit row is that it still reads correctly after
 * the admin, the record, or the session it describes is gone.
 */
class AuditLogger
{
    /* ---------------------------------------------------------------- CRUD */
    public const ACTION_CREATED       = 'created';
    public const ACTION_UPDATED       = 'updated';
    public const ACTION_DELETED       = 'deleted';
    public const ACTION_RESTORED      = 'restored';
    public const ACTION_FORCE_DELETED = 'force_deleted';

    /* ------------------------------------------------------ Authentication */
    public const ACTION_LOGIN                = 'login';
    public const ACTION_LOGOUT               = 'logout';
    public const ACTION_LOGIN_FAILED         = 'login_failed';
    public const ACTION_PASSWORD_CHANGED     = 'password_changed';
    public const ACTION_PASSWORD_RESET       = 'password_reset';
    public const ACTION_RESET_REQUESTED      = 'password_reset_requested';
    public const ACTION_RESET_APPROVED       = 'password_reset_approved';
    public const ACTION_RESET_REJECTED       = 'password_reset_rejected';
    public const ACTION_ACTIVATION_REQUESTED = 'activation_requested';
    public const ACTION_ACTIVATION_APPROVED  = 'activation_approved';
    public const ACTION_ACTIVATION_REJECTED  = 'activation_rejected';
    public const ACTION_PROFILE_UPDATED      = 'profile_updated';

    /* --------------------------------------------------------------- System */
    public const ACTION_ROLE_ASSIGNED      = 'role_assigned';
    public const ACTION_PERMISSION_CHANGED = 'permission_changed';
    public const ACTION_STATUS_CHANGED     = 'status_changed';
    public const ACTION_SETTINGS_CHANGED   = 'settings_changed';
    public const ACTION_USER_ACTIVATED     = 'user_activated';
    public const ACTION_USER_DEACTIVATED   = 'user_deactivated';
    public const ACTION_ADMIN_CREATED      = 'admin_created';
    public const ACTION_ADMIN_REMOVED      = 'admin_removed';
    public const ACTION_CUSTOMER_CREATED   = 'customer_created';
    public const ACTION_CUSTOMER_REMOVED   = 'customer_removed';
    public const ACTION_FILE_UPLOADED      = 'file_uploaded';
    public const ACTION_FILE_DELETED       = 'file_deleted';
    public const ACTION_EMAIL_SENT         = 'email_sent';
    public const ACTION_OTP_SENT           = 'otp_sent';
    public const ACTION_EXPORTED           = 'exported';
    public const ACTION_CUSTOM             = 'custom';

    /** Model class => the module name shown in the trail and its filter. */
    private const MODULE_MAP = [
        \App\Models\Food::class            => 'Foods',
        \App\Models\Category::class        => 'Categories',
        \App\Models\Subcategory::class     => 'Subcategories',
        \App\Models\Unit::class            => 'Units',
        \App\Models\Order::class           => 'Orders',
        \App\Models\Coupon::class          => 'Coupons',
        \App\Models\Promotion::class       => 'Promotions',
        \App\Models\DeliveryMan::class     => 'Delivery Men',
        \App\Models\DeliveryRun::class     => 'Delivery Runs',
        \App\Models\ContactMessage::class  => 'Contact Messages',
        \App\Models\PosSale::class         => 'POS',
        \App\Models\Registration::class    => 'Customers',
        \App\Models\User::class            => 'Admin Users',
        \App\Models\AccountRequest::class  => 'Account Requests',
        \App\Models\Permission::class      => 'Permissions',
        \App\Models\ChatConversation::class => 'Support Chat',
    ];

    /**
     * Record anything.
     *
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function log(
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $old = null,
        ?array $new = null,
        ?string $module = null,
        ?string $subjectLabel = null,
    ): ?ActivityLog {
        try {
            $actor  = self::actor();
            $client = self::client();

            return ActivityLog::create([
                'user_id'       => $actor['id'],
                'user_name'     => $actor['name'],
                'user_role'     => $actor['role'],
                'user_type'     => $actor['type'],

                'event'         => $action,
                'module'        => $module ?: self::moduleFor($subject),

                'subject_type'  => $subject ? $subject::class : null,
                'subject_id'    => $subject?->getKey(),
                'subject_label' => $subjectLabel ?? ($subject ? self::labelFor($subject) : null),

                'description'   => Str::limit($description, 250),
                'old_values'    => $old ?: null,
                'new_values'    => $new ?: null,

                'ip_address'    => $client['ip'],
                'browser'       => $client['browser'],
                'device'        => $client['device'],
                'platform'      => $client['platform'],
                'user_agent'    => $client['user_agent'],
                'session_id'    => $client['session_id'],
                'url'           => $client['url'],
                'method'        => $client['method'],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Audit log write failed: ' . $e->getMessage());

            return null;
        }
    }

    /** An authentication event — no model subject, module fixed. */
    public static function auth(string $action, string $description, ?Model $subject = null): ?ActivityLog
    {
        return self::log($action, $description, $subject, null, null, 'Authentication');
    }

    /** A system-level event (settings, files, mail, permissions). */
    public static function system(
        string $action,
        string $module,
        string $description,
        ?Model $subject = null,
        ?array $old = null,
        ?array $new = null,
    ): ?ActivityLog {
        return self::log($action, $description, $subject, $old, $new, $module);
    }

    /* -------------------------------------------------- model event helpers */

    public static function created(Model $subject): void
    {
        self::log(
            self::ACTION_CREATED,
            'Created ' . self::subjectName($subject) . ' "' . self::labelFor($subject) . '"',
            $subject,
            null,
            self::snapshot($subject, $subject->getAttributes()),
        );
    }

    public static function updated(Model $subject): void
    {
        [$old, $new] = self::diff($subject);

        if (empty($new)) {
            return;
        }

        $fields = implode(', ', array_slice(array_keys($new), 0, 4));
        $extra  = count($new) > 4 ? ' +' . (count($new) - 4) . ' more' : '';

        self::log(
            self::ACTION_UPDATED,
            'Updated ' . self::subjectName($subject) . ' "' . self::labelFor($subject) . "\" ({$fields}{$extra})",
            $subject,
            $old,
            $new,
        );
    }

    public static function deleted(Model $subject): void
    {
        self::log(
            self::ACTION_DELETED,
            'Deleted ' . self::subjectName($subject) . ' "' . self::labelFor($subject) . '"',
            $subject,
            self::snapshot($subject, $subject->getOriginal()),
            null,
        );
    }

    public static function restored(Model $subject): void
    {
        self::log(
            self::ACTION_RESTORED,
            'Restored ' . self::subjectName($subject) . ' "' . self::labelFor($subject) . '"',
            $subject,
        );
    }

    public static function forceDeleted(Model $subject): void
    {
        self::log(
            self::ACTION_FORCE_DELETED,
            'Permanently deleted ' . self::subjectName($subject) . ' "' . self::labelFor($subject) . '"',
            $subject,
            self::snapshot($subject, $subject->getOriginal()),
            null,
        );
    }

    /* --------------------------------------------------------------- detail */

    /**
     * The attributes that actually moved, split into before and after.
     *
     * @return array{0: array<string, mixed>, 1: array<string, mixed>}
     */
    public static function diff(Model $subject): array
    {
        $ignored = self::ignoredFor($subject);
        $old     = [];
        $new     = [];

        foreach ($subject->getChanges() as $key => $value) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            $old[$key] = self::scalarise($subject->getOriginal($key));
            $new[$key] = self::scalarise($value);
        }

        return [$old, $new];
    }

    /**
     * A whole-record snapshot with the noisy and secret columns stripped.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function snapshot(Model $subject, array $attributes): array
    {
        $ignored = self::ignoredFor($subject);
        $out     = [];

        foreach ($attributes as $key => $value) {
            if (in_array($key, $ignored, true)) {
                continue;
            }

            $out[$key] = self::scalarise($value);
        }

        return $out;
    }

    /**
     * The human label for a record — model-supplied when possible, otherwise
     * the first name-ish column, otherwise "#id".
     */
    public static function labelFor(Model $subject): string
    {
        if (method_exists($subject, 'activityLabel')) {
            $label = $subject->activityLabel();

            if (filled($label)) {
                return Str::limit((string) $label, 120);
            }
        }

        foreach (['name', 'full_name', 'title', 'code', 'invoice_no', 'order_number', 'email', 'username'] as $key) {
            if (filled($subject->getAttribute($key))) {
                return Str::limit((string) $subject->getAttribute($key), 120);
            }
        }

        return '#' . $subject->getKey();
    }

    /** "App\Models\DeliveryMan" => "delivery man" */
    public static function subjectName(Model $subject): string
    {
        return Str::lower(Str::headline(class_basename($subject)));
    }

    public static function moduleFor(?Model $subject): ?string
    {
        if (!$subject) {
            return null;
        }

        return self::MODULE_MAP[$subject::class] ?? Str::headline(class_basename($subject));
    }

    /** Every module name the trail can carry, for the filter dropdown. */
    public static function modules(): array
    {
        $modules = array_values(self::MODULE_MAP);
        $modules[] = 'Authentication';
        $modules[] = 'System';

        sort($modules);

        return array_values(array_unique($modules));
    }

    /**
     * Who is acting. Admins come from the default guard, customers from the
     * frontend guard; a console command, queue job or webhook has neither.
     *
     * @return array{id: int|null, name: string, role: string, type: string}
     */
    public static function actor(): array
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();

            return [
                'id'   => $user->id,
                'name' => $user->name,
                'role' => $user->role ?: 'admin',
                'type' => 'admin',
            ];
        }

        if (Auth::guard('frontend')->check()) {
            $user = Auth::guard('frontend')->user();

            return [
                // Different table — never point users.id at a registration.
                'id'   => null,
                'name' => $user->full_name ?? $user->username,
                'role' => 'customer',
                'type' => 'customer',
            ];
        }

        return ['id' => null, 'name' => 'System', 'role' => 'system', 'type' => 'system'];
    }

    /**
     * The request context, as it was.
     *
     * @return array{ip: string|null, browser: string, device: string, platform: string,
     *               user_agent: string|null, session_id: string|null, url: string|null, method: string|null}
     */
    public static function client(): array
    {
        $request = request();
        $agent   = Agent::parse($request?->userAgent());

        $sessionId = null;

        try {
            // No session at all in console and queue contexts.
            $sessionId = $request?->hasSession() ? $request->session()->getId() : null;
        } catch (\Throwable $e) {
            $sessionId = null;
        }

        return [
            'ip'         => $request?->ip(),
            'browser'    => $agent['browser'],
            'device'     => $agent['device'],
            'platform'   => $agent['platform'],
            'user_agent' => Str::limit((string) $agent['user_agent'], 500) ?: null,
            'session_id' => $sessionId,
            'url'        => $request ? Str::limit($request->fullUrl(), 250) : null,
            'method'     => $request?->method(),
        ];
    }

    /** @return list<string> */
    private static function ignoredFor(Model $subject): array
    {
        $ignored = (array) config('security.audit.never_log', []);

        if (method_exists($subject, 'activityIgnoredAttributes')) {
            $ignored = array_merge($ignored, $subject->activityIgnoredAttributes());
        }

        return $ignored;
    }

    /** Keep arrays and objects readable and bounded inside the JSON columns. */
    private static function scalarise(mixed $value): mixed
    {
        $limit = (int) config('security.audit.value_limit', 200);

        if (is_null($value) || is_bool($value) || is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return Str::limit($value, $limit);
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return Str::limit(json_encode($value) ?: '', $limit);
    }
}
