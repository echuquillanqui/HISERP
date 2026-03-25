<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'order_id',
        'order_detail_id',
        'user_id',
        'movement_type',
        'source',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_cost',
        'unit_price',
        'notes',
        'movement_at',
    ];

    protected $casts = [
        'movement_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
