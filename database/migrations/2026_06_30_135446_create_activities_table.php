<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('activity');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('project_id')->nullable()->constrained();
            $table->foreignId('parent_id')->nullable()->constrained('activities');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority')->default('medium');
            $table->string('source')->nullable();
            $table->string('person')->nullable();
            $table->json('tags')->nullable();
            $table->string('status')->default('in_progress');
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->boolean('is_planned')->default(false);
            $table->unsignedTinyInteger('energy_level')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
