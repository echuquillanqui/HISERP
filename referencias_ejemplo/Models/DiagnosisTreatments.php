<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BelongsTo;

class DiagnosisTreatments extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * El diagnóstico pertenece a una hoja de referencia.
     */
    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }
}
