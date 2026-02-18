<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function labItem() {
        return $this->belongsTo(LabItem::class);
    }

    /**
     * Relación corregida: lab_item_id apunta a la tabla order_details.
     * Esto permite que cada resultado de laboratorio esté vinculado a un ítem de la venta.
     */
    public function orderDetail() {
        return $this->belongsTo(OrderDetail::class, 'lab_item_id');
    }

    /**
     * Relación con el catálogo para obtener los parámetros del examen (nombre, unidades, etc.)
     */
    public function catalog() {
        return $this->belongsTo(Catalog::class);
    }

}
