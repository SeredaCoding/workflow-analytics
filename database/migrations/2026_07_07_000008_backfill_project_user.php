<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $projects = DB::table('projects')->where('visibility', 'user')->whereNotNull('user_id')->get();
        foreach ($projects as $project) {
            DB::table('project_user')->insertOrIgnore([
                'project_id' => $project->id,
                'user_id' => $project->user_id,
            ]);
        }
    }

    public function down(): void
    {
        // no-op
    }
};
