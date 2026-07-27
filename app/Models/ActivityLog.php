<?php

namespace App\Models;

use App\Support\Agent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per auditable action.
 *
 * Actor name, subject label and client details are snapshots, not joins — an
 * audit trail has to still read correctly after the admin, the record or the
 * session it describes is gone.
 */
class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'user_name', 'user_role', 'user_type',
        'event', 'module',
        'subject_type', 'subject_label', 'subject_id',
        'description', 'old_values', 'new_values',
        'ip_address', 'browser', 'device', 'platform', 'user_agent',
        'session_id', 'url', 'method',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Every action the trail can record, grouped the way the filter shows them.
     *
     * @var array<string, array<string, array{label: string, icon: string, tone: string}>>
     */
    public const EVENT_GROUPS = [
        'Records' => [
            'created'       => ['label' => 'Created',           'icon' => 'feather-plus-circle',  'tone' => 'success'],
            'updated'       => ['label' => 'Updated',           'icon' => 'feather-edit-2',       'tone' => 'warning'],
            'deleted'       => ['label' => 'Deleted',           'icon' => 'feather-trash-2',      'tone' => 'danger'],
            'restored'      => ['label' => 'Restored',          'icon' => 'feather-rotate-ccw',   'tone' => 'info'],
            'force_deleted' => ['label' => 'Permanently Deleted','icon' => 'feather-x-octagon',   'tone' => 'danger'],
        ],
        'Authentication' => [
            'login'                    => ['label' => 'Login',                  'icon' => 'feather-log-in',        'tone' => 'info'],
            'logout'                   => ['label' => 'Logout',                 'icon' => 'feather-log-out',       'tone' => 'secondary'],
            'login_failed'             => ['label' => 'Failed Login',           'icon' => 'feather-alert-circle',  'tone' => 'danger'],
            'password_changed'         => ['label' => 'Password Changed',       'icon' => 'feather-key',           'tone' => 'warning'],
            'password_reset'           => ['label' => 'Password Reset',         'icon' => 'feather-key',           'tone' => 'warning'],
            'password_reset_requested' => ['label' => 'Reset Requested',        'icon' => 'feather-help-circle',   'tone' => 'info'],
            'password_reset_approved'  => ['label' => 'Reset Approved',         'icon' => 'feather-check-circle',  'tone' => 'success'],
            'password_reset_rejected'  => ['label' => 'Reset Rejected',         'icon' => 'feather-x-circle',      'tone' => 'danger'],
            'activation_requested'     => ['label' => 'Activation Requested',   'icon' => 'feather-user-plus',     'tone' => 'info'],
            'activation_approved'      => ['label' => 'Activation Approved',    'icon' => 'feather-user-check',    'tone' => 'success'],
            'activation_rejected'      => ['label' => 'Activation Rejected',    'icon' => 'feather-user-x',        'tone' => 'danger'],
            'profile_updated'          => ['label' => 'Profile Updated',        'icon' => 'feather-user',          'tone' => 'warning'],
        ],
        'System' => [
            'role_assigned'      => ['label' => 'Role Assigned',       'icon' => 'feather-award',        'tone' => 'primary'],
            'permission_changed' => ['label' => 'Permissions Changed', 'icon' => 'feather-shield',       'tone' => 'primary'],
            'status_changed'     => ['label' => 'Status Changed',      'icon' => 'feather-toggle-right', 'tone' => 'warning'],
            'settings_changed'   => ['label' => 'Settings Changed',    'icon' => 'feather-settings',     'tone' => 'primary'],
            'user_activated'     => ['label' => 'User Activated',      'icon' => 'feather-user-check',   'tone' => 'success'],
            'user_deactivated'   => ['label' => 'User Deactivated',    'icon' => 'feather-user-x',       'tone' => 'danger'],
            'admin_created'      => ['label' => 'Admin Created',       'icon' => 'feather-user-plus',    'tone' => 'success'],
            'admin_removed'      => ['label' => 'Admin Removed',       'icon' => 'feather-user-minus',   'tone' => 'danger'],
            'customer_created'   => ['label' => 'Customer Created',    'icon' => 'feather-user-plus',    'tone' => 'success'],
            'customer_removed'   => ['label' => 'Customer Removed',    'icon' => 'feather-user-minus',   'tone' => 'danger'],
            'file_uploaded'      => ['label' => 'File Uploaded',       'icon' => 'feather-upload',       'tone' => 'info'],
            'file_deleted'       => ['label' => 'File Deleted',        'icon' => 'feather-trash',        'tone' => 'danger'],
            'email_sent'         => ['label' => 'Email Sent',          'icon' => 'feather-mail',         'tone' => 'info'],
            'otp_sent'           => ['label' => 'OTP Sent',            'icon' => 'feather-send',         'tone' => 'info'],
            'exported'           => ['label' => 'Exported',            'icon' => 'feather-download',     'tone' => 'info'],
            'custom'             => ['label' => 'Other',               'icon' => 'feather-zap',          'tone' => 'primary'],
        ],
    ];

    /**
     * Flat action => metadata map, as a constant.
     *
     * Kept because views and controllers written against the earlier shape read
     * `ActivityLog::EVENTS` directly; `events()` is the preferred accessor.
     */
    public const EVENTS = self::EVENT_GROUPS['Records']
        + self::EVENT_GROUPS['Authentication']
        + self::EVENT_GROUPS['System'];

    /**
     * Flat action => metadata map.
     *
     * @return array<string, array{label: string, icon: string, tone: string}>
     */
    public static function events(): array
    {
        static $flat = null;

        if ($flat === null) {
            $flat = [];

            foreach (self::EVENT_GROUPS as $actions) {
                $flat += $actions;
            }
        }

        return $flat;
    }

    /** Kept for call sites that read the old constant. */
    public static function eventList(): array
    {
        return self::events();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ------------------------------------------------------------ display */

    public function meta(): array
    {
        return self::events()[$this->event] ?? self::events()['custom'];
    }

    public function icon(): string
    {
        return $this->meta()['icon'];
    }

    public function tone(): string
    {
        return $this->meta()['tone'];
    }

    public function eventLabel(): string
    {
        return $this->meta()['label'];
    }

    /**
     * The old {field: [old, new]} shape, rebuilt from the two columns.
     *
     * Everything that renders a diff reads this, so splitting the storage did
     * not have to ripple into the views.
     *
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public function getChangesAttribute(): array
    {
        return $this->diff();
    }

    /**
     * Same data as the `changes` accessor, as a plain method.
     *
     * Prefer this in new code: `changes` collides with Eloquent's own protected
     * $changes property, so the accessor only fires from outside the class.
     *
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public function diff(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        $keys = array_unique(array_merge(array_keys($old), array_keys($new)));
        $out  = [];

        foreach ($keys as $key) {
            $out[$key] = [$old[$key] ?? null, $new[$key] ?? null];
        }

        return $out;
    }

    public function hasDiff(): bool
    {
        return !empty($this->old_values) || !empty($this->new_values);
    }

    /** "App\Models\Food" => "Food" */
    public function subjectName(): string
    {
        return $this->subject_type ? class_basename($this->subject_type) : '—';
    }

    public function moduleLabel(): string
    {
        return $this->module ?: $this->subjectName();
    }

    public function clientSummary(): string
    {
        return Agent::summary($this->browser, $this->platform, $this->device);
    }

    /**
     * The distinct modules present, for the filter dropdown. Falls back to the
     * full catalogue when the table is still empty.
     *
     * @return list<string>
     */
    public static function moduleOptions(): array
    {
        $used = static::query()
            ->whereNotNull('module')
            ->distinct()
            ->orderBy('module')
            ->pluck('module')
            ->all();

        return $used ?: \App\Support\AuditLogger::modules();
    }

    /* ------------------------------------------------------------- scopes */

    public function scopeEvent(Builder $query, ?string $event): Builder
    {
        return $event ? $query->where('event', $event) : $query;
    }

    public function scopeModule(Builder $query, ?string $module): Builder
    {
        return $module ? $query->where('module', $module) : $query;
    }

    public function scopeByUser(Builder $query, ?int $userId): Builder
    {
        return $userId ? $query->where('user_id', $userId) : $query;
    }

    public function scopeUserType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('user_type', $type) : $query;
    }

    public function scopeSubjectType(Builder $query, ?string $type): Builder
    {
        return $type ? $query->where('subject_type', $type) : $query;
    }

    /** Free text over the human-readable columns. */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (!$term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('subject_label', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    public function scopeBetweenDates(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    /**
     * The distinct model names present, for the filter dropdown.
     *
     * @return array<string, string> fully-qualified class => short name
     */
    public static function subjectTypes(): array
    {
        return static::query()
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type')
            ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
            ->all();
    }
}
