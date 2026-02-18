<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function area() {
        return $this->belongsTo(Area::class);
    }

    public function catalogs() {
        return $this->belongsToMany(Catalog::class);
    }

    // Relación inversa polimórfica
    public function orderDetails() {
        return $this->morphMany(OrderDetail::class, 'itemable');
    }
}
