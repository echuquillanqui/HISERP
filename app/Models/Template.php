<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'nombre_plantilla', 'html_content'];

    public function service() 
    {
        return $this->belongsTo(Service::class);
    }
}
