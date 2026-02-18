<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'first_name',
        'last_name',
        'birth_date',
        'gender',
        'phone',
        'email',
        'address',
    ];

    public function histories()
    {
        return $this->hasMany(History::class);
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->birth_date)->age;
    }

    /**
     * Retorna la edad didáctica (Ej: 25 años y 4 meses)
     */
    public function getAgeDetailAttribute()
    {
        $nacimiento = Carbon::parse($this->birth_date);
        $diff = $nacimiento->diff(now());
        
        if ($diff->y == 0) {
            return "{$diff->m} meses y {$diff->d} días";
        }
        
        return "{$diff->y} años y {$diff->m} meses";
    }
}
