<?php

namespace App\Models\Concerns;

use App\Support\AuditLogger;

/**
 * Add to any model whose lifecycle should land in the audit trail.
 *
 * A model can customise two things:
 *   activityLabel()             — the human name shown in the trail
 *   activityIgnoredAttributes() — extra columns to leave out of the diff
 *
 * Both are optional; sensible defaults are worked out from the columns.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait LogsActivity
{
    /**
     * Declared as a real property on purpose — assigning to an undeclared one
     * would go through Eloquent's __set and end up as a phantom column on save.
     */
    public bool $activityLogPaused = false;

    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            if ($model->shouldLogActivity()) {
                AuditLogger::created($model);
            }
        });

        static::updated(function ($model) {
            if ($model->shouldLogActivity()) {
                AuditLogger::updated($model);
            }
        });

        static::deleted(function ($model) {
            if (!$model->shouldLogActivity()) {
                return;
            }

            // Soft-deleting models fire `deleted` for both cases; only a real
            // force delete is unrecoverable and deserves the louder event.
            if (method_exists($model, 'isForceDeleting') && $model->isForceDeleting()) {
                AuditLogger::forceDeleted($model);

                return;
            }

            AuditLogger::deleted($model);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                if ($model->shouldLogActivity()) {
                    AuditLogger::restored($model);
                }
            });
        }
    }

    /**
     * Escape hatch — a model can switch itself off for a single operation:
     *
     *     $food->withoutActivityLog(fn () => $food->decrement('quantity'));
     */
    public function shouldLogActivity(): bool
    {
        return !$this->activityLogPaused;
    }

    public function withoutActivityLog(callable $callback): mixed
    {
        $this->activityLogPaused = true;

        try {
            return $callback();
        } finally {
            $this->activityLogPaused = false;
        }
    }

    /**
     * Columns that change on their own and would drown the trail in noise.
     *
     * @return list<string>
     */
    public function activityIgnoredAttributes(): array
    {
        return [];
    }
}
