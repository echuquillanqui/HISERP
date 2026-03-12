<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'precio'];

    public function templates() 
    {
        return $this->hasMany(Template::class);
    }
}
