<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;

class LunchBreakService
{
    public function getUserLunchStart(int $userId): ?string
    {
        return Setting::where('user_id', $userId)->where('key', 'lunch_start')->value('value');
    }

    public function getUserLunchEnd(int $userId): ?string
    {
        return Setting::where('user_id', $userId)->where('key', 'lunch_end')->value('value');
    }

    public function getEffectiveDuration(Carbon $start, Carbon $end, int $userId): int
    {
        $rawDuration = (int) $start->diffInMinutes($end);
        $lunchStart = $this->getUserLunchStart($userId);
        $lunchEnd = $this->getUserLunchEnd($userId);

        if (!$lunchStart || !$lunchEnd) {
            return $rawDuration;
        }

        $lunchStartCarbon = $start->copy()->setTimeFromTimeString($lunchStart);
        $lunchEndCarbon = $start->copy()->setTimeFromTimeString($lunchEnd);

        $overlapStart = max($start, $lunchStartCarbon);
        $overlapEnd = min($end, $lunchEndCarbon);

        if ($overlapStart < $overlapEnd) {
            $overlapMinutes = (int) $overlapStart->diffInMinutes($overlapEnd);
            return max(0, $rawDuration - $overlapMinutes);
        }

        return $rawDuration;
    }
}
