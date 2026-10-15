<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsultationRoom extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'room_number',
        'specialty',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'room_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function getLabelAttribute(): string
    {
        return 'Consultorio '.$this->room_number;
    }
}