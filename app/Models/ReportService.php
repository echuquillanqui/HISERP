<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportService extends Model
{
    use HasFactory;

    protected $fillable = ['order_detail_id', 'template_id', 'resultados_json', 'html_final'];

    protected $casts = [
        'resultados_json' => 'array',
    ];


    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'order_detail_id');
    }
}
