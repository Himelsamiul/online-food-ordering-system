<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'areas',
        'charge',
        'min_order',
        'free_above',
        'eta_minutes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'charge'      => 'decimal:2',
        'min_order'   => 'decimal:2',
        'free_above'  => 'decimal:2',
        'eta_minutes' => 'integer',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * What this zone charges for a given cart subtotal.
     *
     * free_above is inclusive — a threshold advertised as "free over 1000"
     * that still charged at exactly 1000 would read as a bug to the customer.
     */
    public function chargeFor(float $subtotal): float
    {
        if ($this->free_above !== null && $subtotal >= (float) $this->free_above) {
            return 0.0;
        }

        return round((float) $this->charge, 2);
    }

    public function meetsMinimum(float $subtotal): bool
    {
        return $this->min_order === null || $subtotal >= (float) $this->min_order;
    }

    /** How much more the customer must add to unlock free delivery, if close. */
    public function amountToFreeDelivery(float $subtotal): ?float
    {
        if ($this->free_above === null || $subtotal >= (float) $this->free_above) {
            return null;
        }

        return round((float) $this->free_above - $subtotal, 2);
    }

    public function shortLabel(): string
    {
        return $this->name . ' — ' . number_format((float) $this->charge, 2) . ' Tk';
    }
}
