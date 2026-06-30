<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('categories')->insert([
            ['name' => 'Desenvolvimento', 'slug' => 'development', 'color' => '#22c55e', 'icon' => 'code', 'type' => 'development', 'sort_order' => 1],
            ['name' => 'Bug', 'slug' => 'bug', 'color' => '#ef4444', 'icon' => 'bug', 'type' => 'development', 'sort_order' => 2],
            ['name' => 'Suporte', 'slug' => 'support', 'color' => '#f59e0b', 'icon' => 'headset', 'type' => 'support', 'sort_order' => 3],
            ['name' => 'Investigação', 'slug' => 'investigation', 'color' => '#8b5cf6', 'icon' => 'search', 'type' => 'investigation', 'sort_order' => 4],
            ['name' => 'Banco de Dados', 'slug' => 'database', 'color' => '#06b6d4', 'icon' => 'database', 'type' => 'investigation', 'sort_order' => 5],
            ['name' => 'API', 'slug' => 'api', 'color' => '#3b82f6', 'icon' => 'globe', 'type' => 'development', 'sort_order' => 6],
            ['name' => 'Reunião', 'slug' => 'meeting', 'color' => '#ec4899', 'icon' => 'users', 'type' => 'meeting', 'sort_order' => 7],
            ['name' => 'Documentação', 'slug' => 'documentation', 'color' => '#14b8a6', 'icon' => 'file-text', 'type' => 'documentation', 'sort_order' => 8],
            ['name' => 'Estudo', 'slug' => 'study', 'color' => '#a855f7', 'icon' => 'book', 'type' => 'study', 'sort_order' => 9],
            ['name' => 'Deploy', 'slug' => 'deploy', 'color' => '#f97316', 'icon' => 'rocket', 'type' => 'deploy', 'sort_order' => 10],
            ['name' => 'Infraestrutura', 'slug' => 'infrastructure', 'color' => '#64748b', 'icon' => 'server', 'type' => 'other', 'sort_order' => 11],
            ['name' => 'Interrupção', 'slug' => 'interruption', 'color' => '#dc2626', 'icon' => 'alert-circle', 'type' => 'interruption', 'sort_order' => 12],
            ['name' => 'Outro', 'slug' => 'other', 'color' => '#94a3b8', 'icon' => 'more-horizontal', 'type' => 'other', 'sort_order' => 13],
        ]);
    }
}
