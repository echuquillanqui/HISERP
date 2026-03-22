<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'nombre_plantilla', 'html_content', 'fields_schema'];

    protected $casts = [
        'fields_schema' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
