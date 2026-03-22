<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiagnosisTreatments extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'icd_10_code',
        'diagnosis',
        'treatment',
        'P',
        'D',
        'R',
    ];

    /**
     * El diagnóstico pertenece a una hoja de referencia.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }
}
