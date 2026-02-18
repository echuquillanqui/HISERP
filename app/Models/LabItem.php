<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function history() {
        return $this->belongsTo(History::class);
    }

    public function orderDetail() {
        return $this->belongsTo(OrderDetail::class);
    }

    // Relación con los resultados específicos (donde se escribe el valor)
    public function results() {
        return $this->hasMany(LabResult::class);
    }

    public function area()
    {
        // Asumiendo que area_id es la llave foránea en lab_items
        return $this->belongsTo(Area::class, 'area_id'); 
    }

    public function itemable()
    {
        return $this->morphTo();
    }

    public function getAreaNameAttribute()
    {
        if ($this->itemable && $this->itemable->area) {
            return $this->itemable->area->name;
        }
        return 'Área General';
    }
}
