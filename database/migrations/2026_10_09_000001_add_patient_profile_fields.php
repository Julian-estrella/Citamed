<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->string('gender', 40)->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->string('blood_type', 3)->nullable();
            $table->text('allergies_conditions')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'birth_date',
                'gender',
                'address',
                'emergency_contact_name',
                'emergency_contact_relationship',
                'emergency_contact_phone',
                'blood_type',
                'allergies_conditions',
            ]);
        });
    }
};