<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function catalogs() {
        return $this->hasMany(Catalog::class);
    }

    public function profiles() {
        return $this->hasMany(Profile::class);
    }

}