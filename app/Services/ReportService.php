<?php

namespace App\Services;

use App\Models\Activity;
use App\Services\LunchBreakService;
use Carbon\Carbon;

class ReportService
{
    private function date(?Carbon $date = null): Carbon
    {
        return $date ?? now();
    }

    private function monthBounds(?Carbon $date = null): array
    {
        $d = $this->date($date);
        return [
            'start' => $d->copy()->startOfMonth(),
            'end' => $d->copy()->endOfMonth(),
        ];
    }

    public function monthlyData(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $activities = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $bounds['start'])
            ->where('started_at', '<=', $bounds['end'])
            ->get();

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

    public function categoryDistribution(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $activities = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $bounds['start'])
            ->where('started_at', '<=', $bounds['end'])
            ->with('category')
            ->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return $activities
            ->groupBy(fn($a) => $a->category_id ?? 'none')
            ->map(function ($items) {
                $first = $items->first();
                $cat = $first->category;
                return [
                    'name' => $cat?->name ?? 'Sem categoria',
                    'color' => $cat?->color ?? '#6366f1',
                    'minutes' => $items->sum('duration_minutes'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function projectDistribution(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $activities = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $bounds['start'])
            ->where('started_at', '<=', $bounds['end'])
            ->where('type', 'activity')
            ->with('project')
            ->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return $activities
            ->groupBy(fn($a) => $a->project_id ?? 'none')
            ->map(function ($items) {
                $first = $items->first();
                $project = $first->project;
                return [
                    'name' => $project?->name ?? 'Sem projeto',
                    'color' => $project?->color ?? '#6366f1',
                    'minutes' => $items->sum('duration_minutes'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function contextDistribution(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $activities = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $bounds['start'])
            ->where('started_at', '<=', $bounds['end'])
            ->where('type', 'activity')
            ->with('context')
            ->get();

        if ($includeInProgress) {
            $activities = $this->fillInProgressDuration($activities, true);
        }

        return $activities
            ->groupBy(fn($a) => $a->context_id ?? 'none')
            ->map(function ($items) {
                $first = $items->first();
                $context = $first->context;
                return [
                    'name' => $context?->name ?? 'Sem módulo',
                    'color' => $context?->color ?? '#6366f1',
                    'minutes' => $items->sum('duration_minutes'),
                    'count' => $items->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    public function dailyBreakdown(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $start = $bounds['start'];
        $end = $bounds['end'];

        $cursor = $start->copy()->startOfWeek(Carbon::MONDAY);
        $weeks = [];
        $totalMinutes = 0;

        while ($cursor->lte($end)) {
            $weekDays = [];
            $weekTotal = 0;

            for ($i = 0; $i < 7; $i++) {
                $dayDate = $cursor->copy();
                $isCurrentMonth = $dayDate->month === $start->month && $dayDate->year === $start->year;

                if ($isCurrentMonth) {
                    $activities = Activity::where('user_id', $userId)->whereDate('started_at', $dayDate)->get();
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
                    'day' => (int) $dayDate->format('j'),
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
            'month_label' => $this->date($date)->translatedFormat('F/Y'),
        ];
    }

    public function topActivities(int $userId, bool $includeInProgress = false, ?Carbon $date = null): array
    {
        $bounds = $this->monthBounds($date);
        $query = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $bounds['start'])
            ->where('started_at', '<=', $bounds['end'])
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

    public function fillInProgressDuration($activities, bool $includeInProgress)
    {
        if (!$includeInProgress) return $activities;

        return $activities->map(function ($a) {
            if ($a->duration_minutes === null && $a->status === 'in_progress' && $a->started_at) {
                $a->duration_minutes = app(LunchBreakService::class)->getEffectiveDuration($a->started_at, now(), $a->user_id);
            }
            return $a;
        });
    }

    public function buildDailyHtml(array $data, bool $darkMode = false): string
    {
        $dayNames = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];

        $border = $darkMode ? 'var(--report-border)' : '#e5e7eb';
        $borderBottom = $darkMode ? 'var(--report-border-bottom)' : '#f3f4f6';
        $textDark = $darkMode ? 'var(--report-text-dark)' : '#111';
        $textBody = $darkMode ? 'var(--report-text-body)' : '#333';
        $textSecondary = $darkMode ? 'var(--report-text-secondary)' : '#666';
        $textMuted = $darkMode ? 'var(--report-text-muted)' : '#9ca3af';
        $bgEven = $darkMode ? 'var(--report-bg-even)' : '#f9fafb';
        $bgOdd = $darkMode ? 'var(--report-bg-odd)' : '#ffffff';

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse">';

        $html .= '<tr>';
        foreach ($dayNames as $name) {
            $html .= "<th style=\"padding:6px 4px;text-align:center;border-bottom:2px solid {$border};color:{$textDark};font-size:11px;font-weight:600\">{$name}</th>";
        }
        $html .= '<th style="padding:6px 4px;text-align:right;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:11px;font-weight:600">Total</th>';
        $html .= '</tr>';

        foreach ($data['weeks'] as $wi => $week) {
            $bg = $wi % 2 === 0 ? $bgEven : $bgOdd;
            $html .= '<tr>';
            foreach ($week['days'] as $day) {
                if ($day['is_current_month']) {
                    $hours = $day['total_minutes'] ? round($day['total_minutes'] / 60, 1) . 'h' : '-';
                    $html .= "<td style=\"padding:5px 3px;text-align:center;border-bottom:1px solid {$borderBottom};background:{$bg};font-size:13px;color:{$textBody};line-height:1.3\">{$day['day']}<br><span style=\"font-size:10px;color:{$textSecondary}\">{$hours}</span></td>";
                } else {
                    $html .= "<td style=\"padding:5px 3px;text-align:center;border-bottom:1px solid {$borderBottom};background:{$bg};font-size:13px;color:{$textMuted};line-height:1.3\">-</td>";
                }
            }
            $weekHours = round($week['total_minutes'] / 60, 1);
            $html .= "<td style=\"padding:5px 3px;text-align:right;border-bottom:1px solid {$borderBottom};background:{$bg};font-size:13px;color:{$textBody};font-weight:600;vertical-align:middle\">{$weekHours}h</td>";
            $html .= '</tr>';
        }

        $totalHours = round($data['total_minutes'] / 60, 1);
        $html .= '<tr>';
        $html .= "<td colspan=\"7\" style=\"padding:8px 4px;text-align:right;border-top:2px solid {$border};font-size:13px;color:{$textDark};font-weight:600\">Total do Mês</td>";
        $html .= "<td style=\"padding:8px 4px;text-align:right;border-top:2px solid {$border};font-size:13px;color:{$textDark};font-weight:700\">{$totalHours}h</td>";
        $html .= '</tr>';

        return $html . '</table>';
    }

    public function buildWeeklyHtml(array $data, bool $darkMode = false, ?Carbon $date = null): string
    {
        $ref = $this->date($date);
        $weekNumber = 1;
        $totalInterruptions = 0;

        $border = $darkMode ? 'var(--report-border)' : '#e5e7eb';
        $borderBottom = $darkMode ? 'var(--report-border-bottom)' : '#f3f4f6';
        $textDark = $darkMode ? 'var(--report-text-dark)' : '#111';
        $textBody = $darkMode ? 'var(--report-text-body)' : '#333';
        $textSecondary = $darkMode ? 'var(--report-text-secondary)' : '#666';
        $bgEven = $darkMode ? 'var(--report-bg-even)' : '#f9fafb';
        $bgOdd = $darkMode ? 'var(--report-bg-odd)' : '#ffffff';

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px;border-collapse:collapse">';
        $html .= '<tr>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Semana</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Dias</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Total</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Interrupções</th>';
        $html .= '</tr>';

        foreach ($data['weeks'] as $wi => $week) {
            $daysInMonth = array_filter($week['days'], fn($d) => $d['is_current_month']);
            if (empty($daysInMonth)) continue;

            $first = $daysInMonth[array_key_first($daysInMonth)];
            $last = $daysInMonth[array_key_last($daysInMonth)];
            $range = $first['day'] . ' ' . $ref->translatedFormat('M') . ' - ' . $last['day'] . ' ' . $ref->translatedFormat('M');

            $weekInterruptions = array_sum(array_column($daysInMonth, 'interruptions'));
            $totalInterruptions += $weekInterruptions;

            $bg = $wi % 2 === 0 ? $bgEven : $bgOdd;
            $weekHours = round($week['total_minutes'] / 60, 1);

            $html .= '<tr>';
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textBody};font-weight:600\">Semana {$weekNumber}</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textSecondary};font-size:12px\">{$range}</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textBody};text-align:right\">{$weekHours}h</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textBody};text-align:right\">{$weekInterruptions}</td>";
            $html .= '</tr>';

            $weekNumber++;
        }

        $totalHours = round($data['total_minutes'] / 60, 1);
        $html .= '<tr>';
        $html .= "<td colspan=\"2\" style=\"padding:8px;border-top:2px solid {$border};font-size:13px;color:{$textDark};font-weight:600\">Total do Mês</td>";
        $html .= "<td style=\"padding:8px;text-align:right;border-top:2px solid {$border};font-size:13px;color:{$textDark};font-weight:700\">{$totalHours}h</td>";
        $html .= "<td style=\"padding:8px;text-align:right;border-top:2px solid {$border};font-size:13px;color:{$textDark};font-weight:700\">{$totalInterruptions}</td>";
        $html .= '</tr>';

        return $html . '</table>';
    }

    public function buildTopActivitiesHtml(array $activities, int $totalMinutes, bool $darkMode = false): string
    {
        if (empty($activities)) {
            $muted = $darkMode ? 'var(--report-text-muted)' : '#6b7280';
            return '<p style="font-size:13px;color:' . $muted . '">Nenhuma atividade registrada no período.</p>';
        }

        $border = $darkMode ? 'var(--report-border)' : '#e5e7eb';
        $borderBottom = $darkMode ? 'var(--report-border-bottom)' : '#f3f4f6';
        $textDark = $darkMode ? 'var(--report-text-dark)' : '#111';
        $textBody = $darkMode ? 'var(--report-text-body)' : '#333';
        $textSecondary = $darkMode ? 'var(--report-text-secondary)' : '#666';
        $bgEven = $darkMode ? 'var(--report-bg-even)' : '#f9fafb';
        $bgOdd = $darkMode ? 'var(--report-bg-odd)' : '#ffffff';

        $html = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px">';
        $html .= '<tr>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">#</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Atividade</th>';
        $html .= '<th style="padding:6px 8px;text-align:left;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Categoria</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">Duração</th>';
        $html .= '<th style="padding:6px 8px;text-align:right;border-bottom:2px solid ' . $border . ';color:' . $textDark . ';font-size:12px">%</th>';
        $html .= '</tr>';
        foreach ($activities as $i => $a) {
            $bg = $i % 2 === 0 ? $bgEven : $bgOdd;
            $pct = round(($a['duration_minutes'] / $totalMinutes) * 100);
            $hours = round($a['duration_minutes'] / 60, 1);
            $category = $a['category']['name'] ?? '-';
            $html .= '<tr>';
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textSecondary};font-size:12px\">" . ($i + 1) . "°</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textBody}\">" . e($a['title']) . "</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textSecondary};font-size:12px\">" . e($category) . "</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textBody};text-align:right;font-weight:600\">" . e($hours) . "h</td>";
            $html .= "<td style=\"padding:6px 8px;border-bottom:1px solid {$borderBottom};background:{$bg};color:{$textSecondary};text-align:right;font-size:12px\">" . e($pct) . "%</td>";
            $html .= '</tr>';
        }
        return $html . '</table>';
    }
}
