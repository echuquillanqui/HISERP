<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'status',
    ];

    public function orderTomographies(): HasMany
    {
        return $this->hasMany(OrderTomography::class);
    }
}
