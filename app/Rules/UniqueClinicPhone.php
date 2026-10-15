<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniqueClinicPhone implements ValidationRule
{
    public function __construct(
        private readonly ?string $ignoreTable = null,
        private readonly int|string|null $ignoreId = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        foreach (['users', 'patients', 'doctor_profiles'] as $table) {
            $query = DB::table($table)->where('phone', $value);

            if ($this->ignoreTable === $table && $this->ignoreId !== null) {
                $query->where('id', '!=', $this->ignoreId);
            }

            if ($query->exists()) {
                $fail('Este teléfono ya está registrado en otro usuario, paciente o médico.');

                return;
            }
        }
    }
}