<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_key_unique');
            $table->unique(['user_id', 'key']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'started_at']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique('settings_user_id_key_unique');
            $table->unique('key', 'settings_key_unique');
            $table->dropIndex(['user_id']);
        });

        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'started_at']);
        });
    }
};
