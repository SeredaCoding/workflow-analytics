<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(activities, function (Blueprint ) {
            ->foreignId(user_id)->nullable()->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table(activities, function (Blueprint ) {
            ->dropForeign([user_id]);
            ->dropColumn(user_id);
        });
    }
};
