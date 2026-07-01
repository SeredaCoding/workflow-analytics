<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $subject = Setting::where('key', 'report_subject')->first();

        if ($subject && $subject->value === 'Relatório Mensal - {{month}}') {
            $subject->update(['value' => 'Relatório Mensal - {{user_name}} - {{month}}']);
        }

        $template = Setting::where('key', 'report_template')->first();

        if ($template && str_contains($template->value, 'Relatório de {{month}}')) {
            $template->update([
                'value' => str_replace(
                    'Relatório de {{month}}',
                    'Relatório de {{user_name}} - {{month}}',
                    $template->value
                ),
            ]);
        }
    }

    public function down(): void
    {
        $subject = Setting::where('key', 'report_subject')->first();

        if ($subject && $subject->value === 'Relatório Mensal - {{user_name}} - {{month}}') {
            $subject->update(['value' => 'Relatório Mensal - {{month}}']);
        }

        $template = Setting::where('key', 'report_template')->first();

        if ($template && str_contains($template->value, 'Relatório de {{user_name}} - {{month}}')) {
            $template->update([
                'value' => str_replace(
                    'Relatório de {{user_name}} - {{month}}',
                    'Relatório de {{month}}',
                    $template->value
                ),
            ]);
        }
    }
};
