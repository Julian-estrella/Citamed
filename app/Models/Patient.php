<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'address',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'blood_type',
        'allergies_conditions',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'birth_date' => 'date',
        ];
    }

    protected function age(): Attribute
    {
        return Attribute::get(fn () => $this->birth_date?->age);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}