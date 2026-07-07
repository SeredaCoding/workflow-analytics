<?php

namespace App\Console\Commands;

use App\Models\Activity;
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

        $count = Activity::whereNull('user_id')->update(['user_id' => $user->id]);

        if ($count === 0) {
            $this->info('No orphan data found.');
        } else {
            $this->info("Claimed $count orphan activities.");
        }

        return self::SUCCESS;
    }
}
