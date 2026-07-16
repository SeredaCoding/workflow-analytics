<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class BackfillProjectSectors extends Command
{
    protected $signature = 'projects:backfill-sectors';
    protected $description = 'Atribui setores a projetos existentes que não possuem setor definido';

    public function handle(): int
    {
        $projects = Project::doesntHave('sectors')->get();

        if ($projects->isEmpty()) {
            $this->info('Nenhum projeto sem setor encontrado.');
            return Command::SUCCESS;
        }

        $this->info("Encontrados {$projects->count()} projetos sem setor.");

        $assigned = 0;
        $skipped = 0;

        foreach ($projects as $project) {
            $sectorIds = [];

            // 1. Setor do criador (user_id)
            if ($project->user_id && $user = $project->user()->with('sector')->first()) {
                if ($user->sector_id) {
                    $sectorIds[] = $user->sector_id;
                }
            }

            // 2. Fallback: setores dos usuários atribuídos
            if (empty($sectorIds)) {
                $sectorIds = $project->users()
                    ->whereNotNull('sector_id')
                    ->pluck('sector_id')
                    ->unique()
                    ->values()
                    ->toArray();
            }

            if (!empty($sectorIds)) {
                $project->sectors()->sync($sectorIds);
                $this->info("  ✓ #{$project->id} «{$project->name}» → setores: " . implode(', ', $sectorIds));
                $assigned++;
            } else {
                $this->warn("  ✗ #{$project->id} «{$project->name}» — sem setor definido (ignorado)");
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Concluído: {$assigned} atribuídos, {$skipped} ignorados.");
        return Command::SUCCESS;
    }
}
