<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
