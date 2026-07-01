<?php

namespace App\Listeners;

use App\Models\Category;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Auth\Events\Registered;

class CreateDefaultDataForNewUser
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        $orphanCategories = Category::whereNull('user_id')->count();
        $orphanProjects = Project::whereNull('user_id')->count();
        $orphanSettings = Setting::whereNull('user_id')->count();

        if ($orphanCategories > 0 || $orphanProjects > 0 || $orphanSettings > 0) {
            Category::whereNull('user_id')->update(['user_id' => $user->id]);
            Project::whereNull('user_id')->update(['user_id' => $user->id]);
            Setting::whereNull('user_id')->update(['user_id' => $user->id]);
            return;
        }

        $defaultCategories = [
            ['name' => 'Desenvolvimento', 'slug' => 'development', 'color' => '#22c55e', 'type' => 'development', 'sort_order' => 1],
            ['name' => 'Bug', 'slug' => 'bug', 'color' => '#ef4444', 'type' => 'development', 'sort_order' => 2],
            ['name' => 'Suporte', 'slug' => 'support', 'color' => '#f59e0b', 'type' => 'support', 'sort_order' => 3],
            ['name' => 'Investigação', 'slug' => 'investigation', 'color' => '#8b5cf6', 'type' => 'investigation', 'sort_order' => 4],
            ['name' => 'Banco de Dados', 'slug' => 'database', 'color' => '#06b6d4', 'type' => 'investigation', 'sort_order' => 5],
            ['name' => 'API', 'slug' => 'api', 'color' => '#3b82f6', 'type' => 'development', 'sort_order' => 6],
            ['name' => 'Reunião', 'slug' => 'meeting', 'color' => '#ec4899', 'type' => 'meeting', 'sort_order' => 7],
            ['name' => 'Documentação', 'slug' => 'documentation', 'color' => '#14b8a6', 'type' => 'documentation', 'sort_order' => 8],
            ['name' => 'Estudo', 'slug' => 'study', 'color' => '#a855f7', 'type' => 'study', 'sort_order' => 9],
            ['name' => 'Deploy', 'slug' => 'deploy', 'color' => '#f97316', 'type' => 'deploy', 'sort_order' => 10],
            ['name' => 'Infraestrutura', 'slug' => 'infrastructure', 'color' => '#64748b', 'type' => 'other', 'sort_order' => 11],
            ['name' => 'Interrupção', 'slug' => 'interruption', 'color' => '#dc2626', 'type' => 'interruption', 'sort_order' => 12],
            ['name' => 'Outro', 'slug' => 'other', 'color' => '#94a3b8', 'type' => 'other', 'sort_order' => 13],
        ];

        $defaultProjects = [
            ['name' => 'ERP', 'slug' => 'erp', 'color' => '#6366f1'],
            ['name' => 'CRM', 'slug' => 'crm', 'color' => '#06b6d4'],
            ['name' => 'API', 'slug' => 'api', 'color' => '#3b82f6'],
            ['name' => 'Financeiro', 'slug' => 'financial', 'color' => '#22c55e'],
            ['name' => 'Expedição', 'slug' => 'shipping', 'color' => '#f59e0b'],
            ['name' => 'Produção', 'slug' => 'production', 'color' => '#ef4444'],
            ['name' => 'WorkFlow Analytics', 'slug' => 'workflow-analytics', 'color' => '#8b5cf6'],
            ['name' => 'Outros', 'slug' => 'others', 'color' => '#94a3b8'],
        ];

        $now = now();
        $userId = $user->id;

        foreach ($defaultCategories as $cat) {
            $cat['user_id'] = $userId;
            $cat['is_active'] = true;
            $cat['created_at'] = $now;
            $cat['updated_at'] = $now;
            Category::create($cat);
        }

        foreach ($defaultProjects as $proj) {
            $proj['user_id'] = $userId;
            $proj['is_active'] = true;
            $proj['created_at'] = $now;
            $proj['updated_at'] = $now;
            Project::create($proj);
        }
    }
}
