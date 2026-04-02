<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTomographyItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_tomography_id',
        'radiography_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function orderTomography(): BelongsTo
    {
        return $this->belongsTo(OrderTomography::class);
    }

    public function radiography(): BelongsTo
    {
        return $this->belongsTo(Radiography::class);
    }
}
