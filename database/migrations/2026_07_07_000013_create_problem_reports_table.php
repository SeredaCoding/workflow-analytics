<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('problem_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('page_name', 255)->nullable();
            $table->text('description');
            $table->string('severity', 20)->default('normal');
            $table->json('browser_info')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problem_reports');
    }
};
