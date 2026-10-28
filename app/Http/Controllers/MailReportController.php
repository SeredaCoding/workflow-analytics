<?php

namespace App\Http\Controllers;

use App\Mail\MonthlyReportMail;
use App\Models\Activity;
use App\Models\Setting;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
    ) {}

    public function send(Request $request)
    {
        $includeInProgress = $request->boolean('include_in_progress');
        $date = $this->parseMonth($request->input('month'));
        $userId = auth()->id();

        $settings = Setting::where('user_id', $userId)->pluck('value', 'key');

        $bossEmail = $settings->get('boss_email');
        if (!$bossEmail) {
            return response()->json(['error' => 'E-mail do destinatário não configurado.'], 422);
        }

        $template = $settings->get('report_template');
        if (!$template) {
            return response()->json(['error' => 'Template do relatório não configurado.'], 422);
        }

        $userName = $settings->get('user_name', '');
        $subject = $settings->get('report_subject', 'Relatório Mensal - {{month}}');

        $monthly = $this->reportService->monthlyData($userId, $includeInProgress, $date);
        $categoryDistribution = $this->reportService->categoryDistribution($userId, $includeInProgress, $date);
        $dailyBreakdown = $this->reportService->dailyBreakdown($userId, $includeInProgress, $date);
        $topActivities = $this->reportService->topActivities($userId, $includeInProgress, $date);

        $totalMinutes = $monthly['total_minutes'] ?: 1;

        $replacements = [
            'user_name' => $userName,
            'month' => $date->translatedFormat('F/Y'),
            'total_hours' => (string) $monthly['total_hours'],
            'interruptions' => (string) $monthly['interruptions'],
            'avg_focus' => (string) $monthly['avg_focus_minutes'],
            'meeting_hours' => (string) round($monthly['meeting_minutes'] / 60, 1),
            'category_distribution' => $this->buildCategoryHtml($categoryDistribution),
            'month_breakdown' => $this->reportService->buildDailyHtml($dailyBreakdown),
            'weekly_summary' => $this->reportService->buildWeeklyHtml($dailyBreakdown, false, $date),
            'top_activities' => $this->reportService->buildTopActivitiesHtml($topActivities, $totalMinutes),
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

        $csvContent = $this->generateCsv($includeInProgress, $date, $userId);

        try {
            $csvName = $userName ? 'relatorio-mensal-' . \Illuminate\Support\Str::slug($userName) . '-' . $date->format('m-Y') . '.csv' : 'relatorio-mensal.csv';
            Mail::to($bossEmail)->send(new MonthlyReportMail($emailSubject, $htmlBody, $csvContent, $csvName));
            return response()->json(['success' => 'Relatório enviado com sucesso para ' . $bossEmail]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao enviar: ' . $e->getMessage()], 500);
        }
    }

    private function parseMonth(?string $month): \Carbon\Carbon
    {
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return \Carbon\Carbon::parse($month . '-01');
        }
        return now();
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

    private function generateCsv(bool $includeInProgress = false, ?\Carbon\Carbon $date = null, ?int $userId = null): string
    {
        $d = $date ?? now();
        $query = Activity::where('user_id', $userId)
            ->where('started_at', '>=', $d->copy()->startOfMonth())
            ->where('started_at', '<=', $d->copy()->endOfMonth())
            ->with('category', 'project')
            ->orderBy('started_at', 'desc');

        $activities = $query->get();

        if ($includeInProgress) {
            $activities = $this->reportService->fillInProgressDuration($activities, true);
        }

        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, [
            'Título', 'Categoria', 'Projeto', 'Tipo', 'Status',
            'Início', 'Fim', 'Duração (min)', 'Descrição', 'Prioridade', 'Dificuldade',
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
                $this->getDifficultyLabel($a->energy_level),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return $content;
    }

    private function getDifficultyLabel(?int $level): string
    {
        return match ($level) {
            1 => 'Muito Fácil',
            2 => 'Fácil',
            3 => 'Normal',
            4 => 'Difícil',
            5 => 'Muito Difícil',
            default => '',
        };
    }
}
