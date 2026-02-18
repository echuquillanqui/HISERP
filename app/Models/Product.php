<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'concentration', 'presentation', 'stock', 'min_stock', 'purchase_price', 'selling_price', 'expiration_date', 'is_active'];
        
    public function prescriptionItems() {
        return $this->hasMany(PrescriptionItem::class);
    }
}
