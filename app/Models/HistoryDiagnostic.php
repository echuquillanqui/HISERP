<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryDiagnostic extends Model
{
    use HasFactory;

    protected $guarded = [];

    // App\Models\HistoryDiagnostic.php
    public function history() {
        return $this->belongsTo(History::class);
    }

    // App\Models\PrescriptionItem.php
    public function prescription() {
        return $this->belongsTo(Prescription::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }

    // Relación con el catálogo CIE10
    public function cie10() {
        return $this->belongsTo(Cie10::class, 'cie10_id'); 
    }
}
