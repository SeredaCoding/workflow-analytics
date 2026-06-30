<?php

namespace App\Http\Controllers;

use App\Mail\MonthlyReportMail;
use App\Models\Activity;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailReportController extends Controller
{
    public function send(Request $request)
    {
        $includeInProgress = $request->boolean('include_in_progress');

        $settings = Setting::pluck('value', 'key');

        $bossEmail = $settings->get('boss_email');
        if (!$bossEmail) {
            return response()->json(['error' => 'E-mail do chefe não configurado.'], 422);
        }

        $template = $settings->get('report_template');
        if (!$template) {
            return response()->json(['error' => 'Template do relatório não configurado.'], 422);
        }

        $subject = $settings->get('report_subject', 'Relatório Mensal - {{month}}');

        $monthly = $this->monthlyData($includeInProgress);
        $categoryDistribution = $this->categoryDistribution($includeInProgress);
        $dailyBreakdown = $this->dailyBreakdown($includeInProgress);
        $topActivities = $this->topActivities($includeInProgress);

        $totalMinutes = $monthly['total_minutes'] ?: 1;

        $replacements = [
            'month' => now()->translatedFormat('F/Y'),
            'total_hours' => (string) $monthly['total_hours'],
            'interruptions' => (string) $monthly['interruptions'],
            'avg_focus' => (string) $monthly['avg_focus_minutes'],
            'meeting_hours' => (string) round($monthly['meeting_minutes'] / 60, 1),
            'category_distribution' => $this->buildCategoryHtml($categoryDistribution),
            'daily_breakdown' => $this->buildDailyHtml($dailyBreakdown),
            'weekly_summary' => $this->buildWeeklyHtml($dailyBreakdown),
            'top_activities' => $this->buildTopActivitiesHtml($topActivities, $totalMinutes),
            'csv_note' => '<p style="font-size:13px;color:#555;margin:12px 0">📎 Segue em anexo a lista completa de atividades do mês em formato CSV.</p>',
        ];

        $htmlBody = $template;
        foreach ($replacements as $key => $value) {
            $htmlBody = str_replace('{{' . $key . '}}', $value, $htmlBody);
        }

        $emailSubject = $subject;
        foreach ($replacements as $key => $value) {
            $emailSubject = str_replace('{{' . $key . '}}', $value, $emailSubject);
        }

        $csvContent = $this->generateCsv($includeInProgress);

        try {
            Mail::to($bossEmail)->send(new MonthlyReportMail($emailSubject, $htmlBody, $csvContent));
            return response()->json(['success' => 'Relatório enviado com sucesso para ' . $bossEmail]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao enviar: ' . $e->getMessage()], 500);
        }
    }

    private function topActivities(bool $includeInProgress = false): array
    {
        $query = Activity::where('started_at', '>=', now()->startOfMonth())
            ->where('type', 'activity')
            ->with('category');

        if (!$includeInProgress) {
            $query->whereNotNull('duration_minutes');
        }

        $activities = $query->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return $activities->sortByDesc('duration_minutes')->take(5)->values()->toArray();
    }

    private function buildTopActivitiesHtml(array $activities, int $totalMinutes): string
    {
        if (empty($activities)) {
            return '<p style="font-size:13px;color:#999">Nenhuma atividade registrada no período.</p>';
        }

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px">';
        $html .= '<tr>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">#</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Atividade</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Categoria</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Duração</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">%</th>';
        $html .= '</tr>';
        foreach ($activities as $i => $a) {
            $bg = $i % 2 === 0 ? '#f9fafb' : '#ffffff';
            $pct = round(($a['duration_minutes'] / $totalMinutes) * 100);
            $hours = round($a['duration_minutes'] / 60, 1);
            $category = $a['category']['name'] ?? '-';
            $html .= '<tr>';
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#666;font-size:12px\">" . ($i + 1) . "°</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#333\">" . e($a['title']) . "</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#666;font-size:12px\">" . e($category) . "</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#333;text-align:right;font-weight:600\">" . e($hours) . "h</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#666;text-align:right;font-size:12px\">" . e($pct) . "%</td>";
            $html .= '</tr>';
        }
        return $html . '</table>';
    }

    private function generateCsv(bool $includeInProgress = false): string
    {
        $activities = Activity::where('started_at', '>=', now()->startOfMonth())
            ->with('category', 'project')
            ->orderBy('started_at', 'desc')
            ->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'Título', 'Categoria', 'Projeto', 'Tipo', 'Status',
            'Início', 'Fim', 'Duração (min)', 'Descrição', 'Prioridade', 'Energia',
        ]);

        foreach ($activities as $a) {
            $duration = $a->duration_minutes ?? '';

            fputcsv($handle, [
                $a->title,
                $a->category?->name ?? '',
                $a->project?->name ?? '',
                $a->type === 'interruption' ? 'Interrupção' : 'Atividade',
                match ($a->status) {
                    'in_progress' => 'Em andamento',
                    'paused' => 'Pausado',
                    'completed' => 'Concluído',
                    default => $a->status,
                },
                $a->started_at?->format('d/m/Y H:i') ?? '',
                $a->ended_at?->format('d/m/Y H:i') ?? '',
                $duration,
                $a->description ?? '',
                $a->priority ?? '',
                $a->energy_level ?? '',
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function fillInProgressDuration($activities, bool $includeInProgress)
    {
        if (!$includeInProgress) return $activities;

        return $activities->map(function ($a) {
            if ($a->duration_minutes === null && $a->status === 'in_progress' && $a->started_at) {
                $a->duration_minutes = max(0, now()->diffInMinutes($a->started_at));
            }
            return $a;
        });
    }

    private function monthlyData(bool $includeInProgress = false): array
    {
        $startOfMonth = now()->startOfMonth();
        $activities = Activity::where('started_at', '>=', $startOfMonth)->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return [
            'total_minutes' => $activities->sum('duration_minutes'),
            'total_hours' => round($activities->sum('duration_minutes') / 60, 1),
            'interruptions' => $activities->where('type', 'interruption')->count(),
            'development_minutes' => $activities->filter(fn($a) => $a->category?->type === 'development')->sum('duration_minutes'),
            'support_minutes' => $activities->filter(fn($a) => $a->category?->type === 'support')->sum('duration_minutes'),
            'meeting_minutes' => $activities->filter(fn($a) => $a->category?->type === 'meeting')->sum('duration_minutes'),
            'avg_focus_minutes' => round($activities->where('type', 'activity')->whereNotNull('duration_minutes')->avg('duration_minutes') ?? 0),
        ];
    }

    private function categoryDistribution(bool $includeInProgress = false): array
    {
        $startOfMonth = now()->startOfMonth();
        $activities = Activity::where('started_at', '>=', $startOfMonth)
            ->with('category')
            ->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return $activities
            ->groupBy('category.name')
            ->map(function ($items, $category) {
                return [
                    'name' => $category ?: 'Sem categoria',
                    'minutes' => $items->sum('duration_minutes'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function dailyBreakdown(bool $includeInProgress = false): array
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $weeks = [];
        $totalMinutes = 0;

        while ($cursor->lte($end)) {
            $weekDays = [];
            $weekTotal = 0;

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $isCurrentMonth = $date->month === $start->month && $date->year === $start->year;

                if ($isCurrentMonth) {
                    $activities = Activity::whereDate('started_at', $date)->get();
                    if ($includeInProgress) {
                        $activities = $this->fillInProgressDuration($activities, true);
                    }
                    $minutes = $activities->sum('duration_minutes');
                    $interruptions = $activities->where('type', 'interruption')->count();
                } else {
                    $minutes = null;
                    $interruptions = null;
                }

                $weekDays[] = [
                    'day' => (int) $date->format('j'),
                    'is_current_month' => $isCurrentMonth,
                    'total_minutes' => $minutes,
                    'interruptions' => $interruptions,
                ];

                if ($minutes) {
                    $weekTotal += $minutes;
                }

                $cursor->addDay();
            }

            $weeks[] = [
                'days' => $weekDays,
                'total_minutes' => $weekTotal,
            ];
            $totalMinutes += $weekTotal;
        }

        return [
            'weeks' => $weeks,
            'total_minutes' => $totalMinutes,
            'month_label' => now()->translatedFormat('F/Y'),
        ];
    }

    private function buildCategoryHtml(array $distribution): string
    {
        $total = array_sum(array_column($distribution, 'minutes')) ?: 1;
        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px">';
        foreach ($distribution as $cat) {
            $pct = round(($cat['minutes'] / $total) * 100);
            $hours = round($cat['minutes'] / 60, 1);
            $html .= '<tr>';
            $html .= '<td style="padding:5px 0;border-bottom:1px solid #f3f4f6;font-size:13px;color:#333">';
            $html .= e($cat['name']) . ': ' . e($hours) . 'h (' . e($pct) . '%)';
            $html .= '</td>';
            $html .= '</tr>';
        }
        return $html . '</table>';
    }

    private function buildDailyHtml(array $data): string
    {
        $dayNames = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse">';

        $html .= '<tr>';
        foreach ($dayNames as $name) {
            $html .= "<th style=\"padding:6px 4px;text-align:center;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600\">{$name}</th>";
        }
        $html .= '<th style="padding:6px 4px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:11px;font-weight:600">Total</th>';
        $html .= '</tr>';

        foreach ($data['weeks'] as $wi => $week) {
            $bg = $wi % 2 === 0 ? '#f9fafb' : '#ffffff';
            $html .= '<tr>';
            foreach ($week['days'] as $day) {
                if ($day['is_current_month']) {
                    $hours = $day['total_minutes'] ? round($day['total_minutes'] / 60, 1) . 'h' : '-';
                    $html .= "<td style=\"padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:{$bg};font-size:13px;color:#333;line-height:1.3\">{$day['day']}<br><span style=\"font-size:10px;color:#666\">{$hours}</span></td>";
                } else {
                    $html .= "<td style=\"padding:5px 3px;text-align:center;border-bottom:1px solid #f3f4f6;background:{$bg};font-size:13px;color:#ccc;line-height:1.3\">-</td>";
                }
            }
            $weekHours = round($week['total_minutes'] / 60, 1);
            $html .= "<td style=\"padding:5px 3px;text-align:right;border-bottom:1px solid #f3f4f6;background:{$bg};font-size:13px;color:#333;font-weight:600;vertical-align:middle\">{$weekHours}h</td>";
            $html .= '</tr>';
        }

        $totalHours = round($data['total_minutes'] / 60, 1);
        $html .= '<tr>';
        $html .= "<td colspan=\"7\" style=\"padding:8px 4px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:600\">Total do Mês</td>";
        $html .= "<td style=\"padding:8px 4px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:700\">{$totalHours}h</td>";
        $html .= '</tr>';

        return $html . '</table>';
    }

    private function buildWeeklyHtml(array $data): string
    {
        $weekNumber = 1;
        $totalInterruptions = 0;

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse">';
        $html .= '<tr>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Semana</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Dias</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Total</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid #e5e7eb;color:#111;font-size:12px">Interrupções</th>';
        $html .= '</tr>';

        foreach ($data['weeks'] as $wi => $week) {
            $daysInMonth = array_filter($week['days'], fn($d) => $d['is_current_month']);
            if (empty($daysInMonth)) continue;

            $first = $daysInMonth[array_key_first($daysInMonth)];
            $last = $daysInMonth[array_key_last($daysInMonth)];
            $range = $first['day'] . ' ' . now()->translatedFormat('M') . ' - ' . $last['day'] . ' ' . now()->translatedFormat('M');

            $weekInterruptions = array_sum(array_column($daysInMonth, 'interruptions'));
            $totalInterruptions += $weekInterruptions;

            $bg = $wi % 2 === 0 ? '#f9fafb' : '#ffffff';
            $weekHours = round($week['total_minutes'] / 60, 1);

            $html .= '<tr>';
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#333;font-weight:600\">Semana {$weekNumber}</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#666;font-size:12px\">{$range}</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#333;text-align:right\">{$weekHours}h</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid #f3f4f6;background:{$bg};color:#333;text-align:right\">{$weekInterruptions}</td>";
            $html .= '</tr>';

            $weekNumber++;
        }

        $totalHours = round($data['total_minutes'] / 60, 1);
        $html .= '<tr>';
        $html .= "<td colspan=\"2\" style=\"padding:8px;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:600\">Total do Mês</td>";
        $html .= "<td style=\"padding:8px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:700\">{$totalHours}h</td>";
        $html .= "<td style=\"padding:8px;text-align:right;border-top:2px solid #e5e7eb;font-size:13px;color:#111;font-weight:700\">{$totalInterruptions}</td>";
        $html .= '</tr>';

        return $html . '</table>';
    }

}
