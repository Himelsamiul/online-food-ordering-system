<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class PosSale extends Model
{
    use LogsActivity;

    protected $fillable = [
        'invoice_no',
        'order_type',
        'table_no',
        'customer_name',
        'phone',
        'address',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_rate',
        'tax_amount',
        'service_charge_rate',
        'service_charge_amount',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'payment_status',
        'note',
        'created_by',
    ];

    protected $casts = [
        'subtotal'              => 'decimal:2',
        'discount_value'        => 'decimal:2',
        'discount_amount'       => 'decimal:2',
        'tax_rate'              => 'decimal:2',
        'tax_amount'            => 'decimal:2',
        'service_charge_rate'   => 'decimal:2',
        'service_charge_amount' => 'decimal:2',
        'total'                 => 'decimal:2',
        'paid_amount'           => 'decimal:2',
        'change_amount'         => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(PosSaleItem::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
