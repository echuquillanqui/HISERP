<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReferralNumerations extends Model
{
    use HasFactory;

    // Esta tabla solo necesita controlar el año y el número actual
    protected $fillable = ['year', 'current_number'];

    /**
     * Referencias asociadas a la numeración del año.
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'numeration_id');
    }
}
