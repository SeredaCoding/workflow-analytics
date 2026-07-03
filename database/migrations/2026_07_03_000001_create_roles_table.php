<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->tinyInteger('id')->primary();
            $table->string('name')->unique();
        });

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'user'],
            ['id' => 2, 'name' => 'supervisor'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->tinyInteger('role_id')->default(1)->after('remember_token');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('roles');
    }
};