<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalog extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function area() {
        return $this->belongsTo(Area::class);
    }

    public function profiles() {
        // Es importante añadir withTimestamps() porque tu migración de pivote los incluye
        return $this->belongsToMany(Profile::class)->withTimestamps();
    }

    // Relación inversa polimórfica: permite saber en qué órdenes se ha vendido este examen
    public function orderDetails() {
        return $this->morphMany(OrderDetail::class, 'itemable');
    }

    public function results() {
        return $this->hasMany(LabResult::class, 'catalog_id');
    }
}
