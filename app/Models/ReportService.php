<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportService extends Model
{
    use HasFactory;

    protected $fillable = ['order_detail_id', 'template_id', 'resultados_json', 'html_final', 'user_id'];

    protected $casts = [
        'resultados_json' => 'array',
    ];


    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
