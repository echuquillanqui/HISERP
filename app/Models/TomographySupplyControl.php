<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TomographySupplyControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_tomography_id',
        'radiography_id',
        'plates_in',
        'plates_out',
        'iopamidol_in',
        'iopamidol_out',
        'plates_balance',
        'iopamidol_balance',
        'notes',
    ];

    protected $casts = [
        'iopamidol_in' => 'decimal:2',
        'iopamidol_out' => 'decimal:2',
        'iopamidol_balance' => 'decimal:2',
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
