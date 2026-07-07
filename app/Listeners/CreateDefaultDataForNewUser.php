<?php

namespace App\Listeners;

use App\Models\Setting;
use Illuminate\Auth\Events\Registered;

class CreateDefaultDataForNewUser
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        Setting::firstOrCreate(
            ['user_id' => $user->id, 'key' => 'lunch_start'],
            ['value' => '12:00']
        );

        Setting::firstOrCreate(
            ['user_id' => $user->id, 'key' => 'lunch_end'],
            ['value' => '13:00']
        );
    }
}
