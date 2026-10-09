<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    public const STATUS_SCHEDULED = 'programada';

    public const STATUS_CONFIRMED = 'confirmada';

    public const STATUS_CANCELLED = 'cancelada';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'scheduled_at',
        'reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}