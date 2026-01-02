<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'month', 'type', 'severity', 'message', 'value', 'threshold'
    ];

    protected $casts = [
        'value' => 'float',
        'threshold' => 'float',
    ];
}
