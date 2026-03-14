<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
            if (empty($order->code)) {
                $order->code = 'ORD-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
            }

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
