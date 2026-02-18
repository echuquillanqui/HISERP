<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function history() {
        return $this->belongsTo(History::class);
    }

    public function items() {
        return $this->hasMany(PrescriptionItem::class);
    }

    // App\Models\PrescriptionItem.php
    public function prescription() {
        return $this->belongsTo(Prescription::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
