<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_pauses', function (Blueprint $table) {
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->dateTime('paused_at');
            $table->dateTime('resumed_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('activity_pauses', function (Blueprint $table) {
            $table->dropForeign(['activity_id']);
            $table->dropColumn(['activity_id', 'paused_at', 'resumed_at', 'duration_minutes']);
        });
    }
};
