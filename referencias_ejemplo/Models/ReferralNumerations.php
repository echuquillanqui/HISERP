<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralNumerations extends Model
{
    use HasFactory;

    // Esta tabla solo necesita controlar el año y el número actual
    protected $fillable = ['year', 'current_number'];
}
