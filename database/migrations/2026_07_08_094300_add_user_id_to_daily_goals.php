<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_goals', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
        });

        Schema::table('daily_goals', function (Blueprint $table) {
            $table->dropUnique('daily_goals_date_unique');
        });

        Schema::table('daily_goals', function (Blueprint $table) {
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('daily_goals', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('daily_goals', function (Blueprint $table) {
            $table->dropUnique('daily_goals_user_id_date_unique');
            $table->unique('date', 'daily_goals_date_unique');
        });
    }
};
