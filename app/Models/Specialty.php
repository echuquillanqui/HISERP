<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Una especialidad tiene muchos usuarios (médicos).
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
