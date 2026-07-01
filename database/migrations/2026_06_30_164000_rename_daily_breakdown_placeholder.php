<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $template = Setting::where('key', 'report_template')->first();

        if ($template && str_contains($template->value, '{{daily_breakdown}}')) {
            $template->update([
                'value' => str_replace('{{daily_breakdown}}', '{{month_breakdown}}', $template->value),
            ]);
        }
    }

    public function down(): void
    {
        $template = Setting::where('key', 'report_template')->first();

        if ($template && str_contains($template->value, '{{month_breakdown}}')) {
            $template->update([
                'value' => str_replace('{{month_breakdown}}', '{{daily_breakdown}}', $template->value),
            ]);
        }
    }
};
