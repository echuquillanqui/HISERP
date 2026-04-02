<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTomography extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'patient_id',
        'radiography_id',
        'agreement_id',
        'user_id',
        'service_type',
        'total',
        'payment_type',
        'care_medium',
        'document_type',
        'document_number',
        'ip_address',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function radiography(): BelongsTo
    {
        return $this->belongsTo(Radiography::class);
    }

    public function agreement(): BelongsTo
    {
        return $this->belongsTo(Agreement::class);
    }
}
