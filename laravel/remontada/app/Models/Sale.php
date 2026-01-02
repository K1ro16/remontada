<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'invoice_number',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'notes',
        'sale_date',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    // Customer removed from the flow

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }
}
