<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DoctorProfile extends Model
{
    protected $fillable = [
        'user_id',
        'professional_title',
        'license_number',
        'specialty',
        'phone',
        'appointment_duration',
        'office',
    ];

    protected function casts(): array
    {
        return [
            'appointment_duration' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class)->orderBy('day_of_week')->orderBy('start_time');
    }
}
