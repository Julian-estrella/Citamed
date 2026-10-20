<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('reschedule_reason', 500)->nullable()->after('reason');
            $table->string('cancellation_reason', 500)->nullable()->after('reschedule_reason');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['reschedule_reason', 'cancellation_reason']);
        });
    }
};