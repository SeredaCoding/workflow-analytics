<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\ActivityContext;
use App\Models\Project;
use Illuminate\Console\Command;

class MigrateModules extends Command
{
    protected $signature = 'modules:migrate';
    protected $description = 'Migrate system-layer projects to activity contexts (modules)';

    public function handle()
    {
        $systemLayerNames = ['API', 'CRM', 'ERP', 'Expedição', 'Financeiro', 'Outros', 'Produção', 'WorkFlow Analytics'];

        $projects = Project::whereIn('name', $systemLayerNames)->get();

        if ($projects->isEmpty()) {
            $this->info('No system-layer projects found to migrate.');
            return 0;
        }

        $migrated = 0;

        foreach ($projects as $project) {
            $context = ActivityContext::create([
                'name' => $project->name,
                'slug' => $project->slug,
                'color' => $project->color ?? '#6366f1',
                'is_active' => $project->is_active,
                'sort_order' => 0,
            ]);

            $count = Activity::where('project_id', $project->id)
                ->update([
                    'context_id' => $context->id,
                    'project_id' => null,
                ]);

            $project->sectors()->sync([]);
            $project->users()->sync([]);
            $project->delete();

            $this->line("  Migrated '{$project->name}': {$count} activities → context #{$context->id}");
            $migrated++;
        }

        $this->info("Done. {$migrated} projects migrated to modules.");

        return 0;
    }
}
