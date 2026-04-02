<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TomographyResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_tomography_id',
        'patient_id',
        'requesting_doctor_id',
        'report_signer_id',
        'result_date',
        'plates_used',
        'iopamidol_used',
        'general_description',
        'result_description',
        'conclusion',
        'result_text',
    ];

    protected $casts = [
        'result_date' => 'date',
        'iopamidol_used' => 'decimal:2',
    ];

    public function orderTomography(): BelongsTo
    {
        return $this->belongsTo(OrderTomography::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function requestingDoctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_doctor_id');
    }

    public function reportSigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'report_signer_id');
    }
}

