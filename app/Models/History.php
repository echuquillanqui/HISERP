<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    public function orderDetail() {
        return $this->belongsTo(OrderDetail::class, 'order_item_id');
    }

    public function diagnostics() {
        return $this->hasMany(HistoryDiagnostic::class);
    }


    public function prescription() {
        return $this->hasOne(Prescription::class);
    }

    public function labItems()
    {
        // En lugar de buscar en la tabla lab_items, 
        // buscamos en los detalles de la orden que generó esta historia
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function user() {
        return $this->belongsTo(User::class); // Médico
    }

    public function order() {
        return $this->belongsTo(Order::class); // Relación con la venta (ORD-xxx)
    }

    public function prescriptionItems()
    {
        return $this->hasManyThrough(
            \App\Models\PrescriptionItem::class,
            \App\Models\Prescription::class,
            'history_id',      // Llave foránea en prescriptions
            'prescription_id', // Llave foránea en prescription_items
            'id',              // Llave local en histories
            'id'               // Llave local en prescriptions
        );
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function catalog() {
        return $this->belongsTo(Catalog::class);
    }
}
