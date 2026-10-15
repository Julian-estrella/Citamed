<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_rooms', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('room_number')->unique();
            $table->string('specialty', 150);
            $table->timestamps();
        });

        $defaultSpecialty = config('clinic.specialties')[4] ?? 'Medicina general';
        foreach (config('clinic.offices', []) as $office) {
            $roomNumber = (int) preg_replace('/\D+/', '', $office);
            if ($roomNumber < 1) {
                continue;
            }

            $specialty = DB::table('doctor_profiles')->where('office', $office)->value('specialty');
            if (! in_array($specialty, config('clinic.specialties', []), true)) {
                $specialty = $defaultSpecialty;
            }

            DB::table('consultation_rooms')->insert([
                'room_number' => $roomNumber,
                'specialty' => $specialty,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_rooms');
    }
};