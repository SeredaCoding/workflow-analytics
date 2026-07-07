<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Project;
use Illuminate\Console\Command;

class ConsolidateProjects extends Command
{
    protected $signature = 'projects:consolidate';
    protected $description = 'Consolidate duplicate projects by slug into single projects';

    public function handle()
    {
        $all = Project::withCount('activities')->get();
        $groups = $all->groupBy(fn($p) => mb_strtolower(trim($p->slug)));

        $duplicatesFound = false;

        foreach ($groups as $slug => $group) {
            if ($group->count() <= 1) continue;

            $duplicatesFound = true;

            $sorted = $group->sortByDesc('activities_count');
            $keep = $sorted->shift();

            $this->info("Keeping '{$keep->name}' (ID {$keep->id}, {$keep->activities_count} activities), merging {$group->count()} duplicates.");

            foreach ($group as $dupe) {
                if ($dupe->id === $keep->id) continue;

                Activity::where('project_id', $dupe->id)->update(['project_id' => $keep->id]);

                $pivotSectors = $dupe->sectors()->pluck('project_sector.sector_id')->toArray();
                if (!empty($pivotSectors)) {
                    $keep->sectors()->syncWithoutDetaching($pivotSectors);
                }

                $pivotUsers = $dupe->users()->pluck('project_user.user_id')->toArray();
                if (!empty($pivotUsers)) {
                    $keep->users()->syncWithoutDetaching($pivotUsers);
                }

                $dupe->sectors()->sync([]);
                $dupe->users()->sync([]);
                $dupe->delete();

                $this->line("  Merged ID {$dupe->id}");
            }
        }

        if (!$duplicatesFound) {
            $this->info('No duplicate projects found.');
        } else {
            $this->info('Consolidation complete.');
        }

        return 0;
    }
}
