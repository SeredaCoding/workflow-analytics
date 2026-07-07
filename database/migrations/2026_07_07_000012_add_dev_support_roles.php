<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insert([
            ['id' => 4, 'name' => 'dev'],
            ['id' => 5, 'name' => 'suporte'],
        ]);
    }

    public function down(): void
    {
        DB::table('roles')->whereIn('id', [4, 5])->delete();
    }
};
