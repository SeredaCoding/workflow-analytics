<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("problem_reports", function (Blueprint $table) {
            $table->string("app_version", 20)->nullable()->after("severity");
            $table->json("images")->nullable()->after("browser_info");
        });

        DB::table("problem_reports")
            ->whereIn("status", ["open", "in_progress", "resolved"])
            ->update(["status" => DB::raw("CASE WHEN status = 'open' THEN 'pending' WHEN status = 'in_progress' THEN 'analyzing' ELSE 'resolved' END")]);
    }

    public function down(): void
    {
        DB::table("problem_reports")
            ->whereIn("status", ["pending", "analyzing"])
            ->update(["status" => DB::raw("CASE WHEN status = 'pending' THEN 'open' WHEN status = 'analyzing' THEN 'in_progress' ELSE 'resolved' END")]);

        Schema::table("problem_reports", function (Blueprint $table) {
            $table->dropColumn(["app_version", "images"]);
        });
    }
};
