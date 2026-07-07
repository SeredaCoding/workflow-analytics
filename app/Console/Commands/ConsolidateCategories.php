<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Category;
use Illuminate\Console\Command;

class ConsolidateCategories extends Command
{
    protected $signature = 'categories:consolidate';
    protected $description = 'Consolidate duplicate categories by name into global categories';

    public function handle()
    {
        $all = Category::all();
        $groups = $all->groupBy(fn($c) => mb_strtolower(trim($c->name)));

        $duplicatesFound = false;

        foreach ($groups as $name => $group) {
            if ($group->count() <= 1) continue;

            $duplicatesFound = true;
            $keep = $group->shift();
            $this->info("Keeping '{$keep->name}' (ID {$keep->id}), merging {$group->count()} duplicates.");

            foreach ($group as $dupe) {
                Activity::where('category_id', $dupe->id)->update(['category_id' => $keep->id]);
                $dupe->delete();
                $this->line("  Merged ID {$dupe->id}");
            }
        }

        if (!$duplicatesFound) {
            $this->info('No duplicate categories found.');
        } else {
            $this->info('Consolidation complete.');
        }

        return 0;
    }
}
