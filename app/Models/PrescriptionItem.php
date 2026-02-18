<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Esta es la relación que falta
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    
}
