<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function details() {
        return $this->hasMany(OrderDetail::class);
    }

    // App\Models\OrderDetail.php
    public function order() {
        return $this->belongsTo(Order::class);
    }

    public function labResults() {
        return $this->hasMany(LabResult::class, 'lab_item_id'); // Relación corregida
    }

    // Esta relación permite que el detalle sepa si apunta a Catalog o Profile
    public function itemable() {
        return $this->morphTo();
    }

    // Elimina o corrige la relación history (la historia depende de Order, no del detalle)
    public function history() {
        return $this->hasOne(History::class, 'order_id', 'order_id');
    }

    public function labItem() {
        return $this->hasOne(LabItem::class);
    }

    // App\Models\OrderDetail.php
    protected static function boot() {
        parent::boot();
        
        static::deleting(function($detail) {
            // Borra los resultados vinculados antes de eliminar el detalle
            $detail->labResults()->delete();
        });
    }
    
}
