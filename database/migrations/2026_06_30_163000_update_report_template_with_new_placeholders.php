<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $template = Setting::where('key', 'report_template')->first();

        if ($template && !str_contains($template->value, '{{weekly_summary}}')) {
            $search = '{{top_activities}}';
            $replace = '<h3 style="margin:20px 0 10px;font-size:16px;color:#111">Resumo Semanal</h3>' . "\n" . '{{weekly_summary}}' . "\n\n" . '{{top_activities}}';
            $template->update(['value' => str_replace($search, $replace, $template->value)]);
        }
    }

    public function down(): void
    {
        $template = Setting::where('key', 'report_template')->first();

        if ($template && str_contains($template->value, '{{weekly_summary}}')) {
            $search = '<h3 style="margin:20px 0 10px;font-size:16px;color:#111">Resumo Semanal</h3>' . "\n" . '{{weekly_summary}}' . "\n\n";
            $template->update(['value' => str_replace($search, '', $template->value)]);
        }
    }
};
