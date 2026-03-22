<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_code',
        'patient_id',
        'origin_facility',
        'destination_facility',
        'destination_specialty',
        'anamnesis',
        'general_state',
        'temperature',
        'blood_pressure',
        'respiratory_rate',
        'heart_rate',
        'oxygen_saturation',
        'skin_subcutaneous',
        'lungs',
        'cardiovascular',
        'neurological',
        'auxiliary_exams',
        'others',
        'referral_type',
        'appointment_date',
        'appointment_time',
        'attending_physician_name',
        'coordination_name',
        'patient_condition',
        'referral_responsible_id',
        'facility_responsible_id',
        'escort_staff_id',
        'receiving_staff_id',
        'arrival_condition',
        'treatments',
        'numeration_id',
    ];


    protected $casts = [
        'treatments' => 'array',
    ];

    /**
     * Lógica automática para generar la numeración al crear el registro.
     */
    protected static function booted()
    {
        static::creating(function ($referral) {
            $year = now()->year;

            // 1. Obtenemos el intermediario para el año actual
            $numeration = ReferralNumerations::firstOrCreate(
                ['year' => $year],
                ['current_number' => 0]
            );

            // 2. Incrementamos el contador global
            $numeration->increment('current_number');

            // 3. ASOCIAMOS la referencia al intermediario mediante el nuevo campo
            $referral->numeration_id = $numeration->id;

            // 4. Generamos el código para esta referencia específica
            $referral->referral_code = $year . '-' . str_pad($numeration->current_number, 3, '0', STR_PAD_LEFT);
        });
    }

    /**
     * Relación con el Paciente
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relación con la tabla asociada de Diagnósticos y Tratamientos
     */
    public function diagnosisTreatments(): HasMany
    {
        return $this->hasMany(DiagnosisTreatments::class);
    }

    /**
     * RELACIONES CON LOS 4 USUARIOS (Personal de Salud)
     * Apuntan a la tabla 'users' usando las llaves foráneas específicas.
     */

    public function referralResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referral_responsible_id');
    }

    public function facilityResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'facility_responsible_id');
    }

    public function escortStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'escort_staff_id');
    }

    public function receivingStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiving_staff_id');
    }
}
