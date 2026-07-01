<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Category;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;

class ClaimOrphanData extends Command
{
    protected $signature = 'data:claim {user : The user ID or email to assign orphan data to}';

    protected $description = 'Assign orphan records (user_id IS NULL) to a specific user';

    public function handle(): int
    {
        $identifier = $this->argument('user');

        $user = is_numeric($identifier)
            ? User::find((int) $identifier)
            : User::where('email', $identifier)->first();

        if (!$user) {
            $this->error("User not found: $identifier");
            return self::FAILURE;
        }

        $counts = [];

        $counts['activities'] = Activity::whereNull('user_id')->update(['user_id' => $user->id]);
        $counts['categories'] = Category::whereNull('user_id')->update(['user_id' => $user->id]);
        $counts['projects'] = Project::whereNull('user_id')->update(['user_id' => $user->id]);
        $counts['settings'] = Setting::whereNull('user_id')->update(['user_id' => $user->id]);

        $total = array_sum($counts);
        if ($total === 0) {
            $this->info('No orphan data found.');
            return self::SUCCESS;
        }

        foreach ($counts as $type => $count) {
            if ($count > 0) {
                $this->line("  $type: $count");
            }
        }
        $this->info("Total records claimed: $total");

        return self::SUCCESS;
    }
}
