<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Radiography extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'contrast_type',
        'private_price',
        'plate_usage',
    ];

    protected $casts = [
        'private_price' => 'decimal:2',
    ];

    public function orderTomographies(): HasMany
    {
        return $this->hasMany(OrderTomography::class);
    }

    public function agreementPrices(): HasMany
    {
        return $this->hasMany(RadiographyAgreementPrice::class);
    }

    public function agreements(): BelongsToMany
    {
        return $this->belongsToMany(Agreement::class, 'radiography_agreement_prices')
            ->withPivot('price')
            ->withTimestamps();
    }
}
