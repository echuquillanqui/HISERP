<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = [];

    // Relación con el Paciente
    public function patient() {
        return $this->belongsTo(Patient::class);
    }

    // Relación con el Usuario que creó la orden (Recepcionista/Bioquímico)
    public function user() {
        return $this->belongsTo(User::class);
    }

    // Relación con los ítems (Detalle)
    public function details() {
        return $this->hasMany(OrderDetail::class);
    }

    // Seguridad: Generar código correlativo único al crear
    protected static function booted() {
        static::creating(function ($order) {
            $lastId = static::max('id') ?? 0;
            $order->code = 'ORD-' . date('Ym') . '-' . str_pad($lastId + 1, 5, '0', STR_PAD_LEFT);
            $order->user_id = auth()->id();
            $order->ip_address = request()->ip();
        });
    }

    public function history()
    {
        return $this->hasOne(History::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
