<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('projects')->insert([
            ['name' => 'ERP', 'slug' => 'erp', 'color' => '#6366f1'],
            ['name' => 'CRM', 'slug' => 'crm', 'color' => '#06b6d4'],
            ['name' => 'API', 'slug' => 'api', 'color' => '#3b82f6'],
            ['name' => 'Financeiro', 'slug' => 'financial', 'color' => '#22c55e'],
            ['name' => 'Expedição', 'slug' => 'shipping', 'color' => '#f59e0b'],
            ['name' => 'Produção', 'slug' => 'production', 'color' => '#ef4444'],
            ['name' => 'WorkFlow Analytics', 'slug' => 'workflow-analytics', 'color' => '#8b5cf6'],
            ['name' => 'Outros', 'slug' => 'others', 'color' => '#94a3b8'],
        ]);
    }
}
