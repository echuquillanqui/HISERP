<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiographyAgreementPrice extends Model
{
    use HasFactory;

    protected $table = 'radiography_agreement_prices';

    protected $fillable = [
        'radiography_id',
        'agreement_id',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function radiography(): BelongsTo
    {
        return $this->belongsTo(Radiography::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
