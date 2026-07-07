<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Category;
use App\Services\LunchBreakService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    private function userActivities()
    {
        return Activity::where('user_id', auth()->id());
    }

    public function index(Request $request)
    {
        $query = $this->userActivities()->with(['category', 'project', 'context', 'parent', 'children']);

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
        }

        if ($contextId = $request->input('context_id')) {
            $query->where('context_id', $contextId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('started_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('started_at', '<=', $dateTo);
        }

        if ($description = $request->input('description')) {
            $query->where('description', 'like', "%{$description}%");
        }

        if ($priority = $request->input('priority')) {
            $query->where('priority', $priority);
        }

        if ($energyLevel = $request->input('energy_level')) {
            $query->where('energy_level', $energyLevel);
        }

        $perPage = $request->input('per_page', 50);
        if (!in_array((int) $perPage, [5, 10, 20, 50, 100])) {
            $perPage = 50;
        }

        $activities = $query->orderBy('started_at', 'desc')
            ->paginate((int) $perPage)
            ->withQueryString();

        $inProgress = $this->userActivities()->inProgress()->latest('started_at')->with(['category', 'project', 'context'])->first();

        return Inertia::render('Activities', [
            'activities' => $activities,
            'filters' => $request->only(['search', 'category_id', 'project_id', 'context_id', 'status', 'date_from', 'date_to', 'description', 'priority', 'energy_level', 'per_page']),
            'inProgress' => $inProgress ? [
                'id' => $inProgress->id,
                'title' => $inProgress->title,
                'type' => $inProgress->type,
                'parent_id' => $inProgress->parent_id,
                'category' => $inProgress->category?->name,
                'category_color' => $inProgress->category?->color,
                'project' => $inProgress->project?->name,
                'context' => $inProgress->context?->name,
                'started_at' => $inProgress->started_at->toIso8601String(),
            ] : null,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,normal,medium,high,critical',
            'source' => 'nullable|string',
            'person' => 'nullable|string',
            'type' => 'nullable|string|in:activity,interruption',
            'parent_id' => 'nullable|exists:activities,id',
            'is_planned' => 'boolean',
            'energy_level' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string',
            'tags' => 'nullable|json',
        ]);

        $validated['started_at'] = now();
        $validated['status'] = 'in_progress';
        $validated['type'] ??= 'activity';
        $validated['user_id'] = auth()->id();

        if (isset($validated['tags']) && is_string($validated['tags'])) {
            $validated['tags'] = json_decode($validated['tags'], true);
        }

        Activity::create($validated);

        return redirect()->back();
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:activity_contexts,id',
            'description' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date|before_or_equal:now',
        ]);

        $startedAt = $validated['started_at'] ?? null;
        if ($startedAt) {
            $startedAt = Carbon::parse($startedAt);
        }

        $this->userActivities()->inProgress()->each(function ($a) use ($startedAt) {
            $start = Carbon::parse($a->started_at);
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $startedAt ?? now(), auth()->id());

            $a->pauses()->create(['paused_at' => $startedAt ?? now()]);
            $a->update([
                'status' => 'paused',
                'ended_at' => $startedAt ?? now(),
                'duration_minutes' => ($a->duration_minutes ?? 0) + $duration,
            ]);
        });

        Activity::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'project_id' => $validated['project_id'] ?? null,
            'context_id' => $validated['context_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'started_at' => $startedAt ?? now(),
            'status' => 'in_progress',
            'type' => 'activity',
        ]);

        return redirect()->back();
    }

    public function pause(Request $request, Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $pausedAt = $request->input('paused_at') ? Carbon::parse($request->input('paused_at')) : now();
        $start = Carbon::parse($activity->started_at);

        $activity->pauses()->create([
            'paused_at' => $pausedAt,
        ]);

        $duration = 0;
        if ($activity->status === 'in_progress') {
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $pausedAt, auth()->id());
        }

        $activity->update([
            'status' => 'paused',
            'ended_at' => $pausedAt,
            'duration_minutes' => ($activity->duration_minutes ?? 0) + $duration,
        ]);

        return redirect()->back();
    }

    public function reopen(Request $request, Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $now = now();

        $this->userActivities()->inProgress()->each(function ($a) use ($now) {
            $start = Carbon::parse($a->started_at);
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $now, auth()->id());
            $a->pauses()->create(['paused_at' => $now]);
            $a->update([
                'status' => 'paused',
                'ended_at' => $now,
                'duration_minutes' => ($a->duration_minutes ?? 0) + $duration,
            ]);
        });

        Activity::create([
            'user_id' => auth()->id(),
            'title' => $activity->title,
            'description' => $activity->description,
            'category_id' => $activity->category_id,
            'project_id' => $activity->project_id,
            'priority' => $activity->priority ?? 'normal',
            'energy_level' => $activity->energy_level,
            'type' => 'activity',
            'status' => 'in_progress',
            'started_at' => $now,
            'parent_id' => $activity->id,
        ]);

        return redirect()->back();
    }

    public function detail(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $root = $activity->parent ?? $activity;

        $sessions = $this->userActivities()
            ->where(function ($q) use ($root, $activity) {
                $q->where('id', $root->id)
                  ->orWhere('parent_id', $root->id);
            })
            ->with(['category', 'project', 'context', 'pauses'])
            ->orderBy('started_at')
            ->get();

        $totalDuration = $sessions->sum('duration_minutes');
        $totalPauseDuration = $sessions->flatMap->pauses->sum('duration_minutes');
        $sessionCount = $sessions->count();

        $activity->load(['category', 'project', 'context', 'pauses']);

        $events = [];
        foreach ($sessions as $s) {
            $events[] = [
                'type' => 'started', 'at' => $s->started_at,
                'activity_id' => $s->id,
                'description' => 'Sessão iniciada',
            ];

            foreach ($s->pauses as $pause) {
                $events[] = [
                    'type' => 'paused', 'at' => $pause->paused_at,
                    'activity_id' => $s->id,
                    'description' => 'Pausada',
                ];
                if ($pause->resumed_at) {
                    $events[] = [
                        'type' => 'resumed', 'at' => $pause->resumed_at,
                        'activity_id' => $s->id,
                        'description' => 'Retomada',
                    ];
                }
            }

            if ($s->ended_at) {
                $events[] = [
                    'type' => 'completed', 'at' => $s->ended_at,
                    'activity_id' => $s->id,
                    'description' => 'Concluída',
                ];
            }
        }

        for ($i = 1; $i < $sessions->count(); $i++) {
            $child = $sessions[$i];
            if ($child->parent_id) {
                $events[] = [
                    'type' => 'reopened', 'at' => $child->started_at,
                    'activity_id' => $child->parent_id,
                    'description' => 'Reaberta',
                ];
            }
        }

        usort($events, fn($a, $b) => $a['at'] <=> $b['at']);

        return response()->json([
            'activity' => $activity,
            'sessions' => $sessions->map(fn($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'started_at' => $s->started_at,
                'ended_at' => $s->ended_at,
                'duration_minutes' => $s->duration_minutes,
                'status' => $s->status,
                'category' => $s->category,
                'project' => $s->project,
            ]),
            'events' => $events,
            'stats' => [
                'total_duration' => $totalDuration,
                'total_pause_duration' => $totalPauseDuration,
                'session_count' => $sessionCount,
                'average_session' => $sessionCount > 0 ? round($totalDuration / $sessionCount) : 0,
            ],
        ]);
    }

    public function resume(Request $request, Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        if ($activity->status !== 'paused') {
            abort(422, 'Apenas atividades pausadas podem ser retomadas.');
        }

        $resumedAt = $request->input('resumed_at') ? Carbon::parse($request->input('resumed_at')) : now();

        $openPause = $activity->pauses()->whereNull('resumed_at')->latest('paused_at')->first();
        if ($openPause) {
            $pauseStart = Carbon::parse($openPause->paused_at);
            $openPause->update([
                'resumed_at' => $resumedAt,
                'duration_minutes' => $pauseStart->diffInMinutes($resumedAt),
            ]);
        }

        $this->userActivities()->inProgress()->where('id', '!=', $activity->id)->each(function ($a) use ($resumedAt) {
            $start = Carbon::parse($a->started_at);
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $resumedAt, auth()->id());

            $a->pauses()->create(['paused_at' => $resumedAt]);
            $a->update([
                'status' => 'paused',
                'ended_at' => $resumedAt,
                'duration_minutes' => ($a->duration_minutes ?? 0) + $duration,
            ]);
        });

        $activity->update([
            'status' => 'in_progress',
            'ended_at' => null,
        ]);

        return redirect()->back();
    }

    public function stop(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $now = now();

        $activity->pauses()->whereNull('resumed_at')->each(function ($pause) use ($now) {
            $pauseStart = Carbon::parse($pause->paused_at);
            $pause->update([
                'resumed_at' => $now,
                'duration_minutes' => $pauseStart->diffInMinutes($now),
            ]);
        });

        $updates = [
            'status' => 'completed',
            'ended_at' => $now,
        ];

        if ($activity->status === 'in_progress') {
            $start = Carbon::parse($activity->started_at);
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $now, auth()->id());
            $updates['duration_minutes'] = max(1, $duration);
        }

        $activity->update($updates);

        return redirect()->back();
    }

    public function interrupt(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'nullable|string',
            'person' => 'nullable|string',
        ]);

        $currentActivity = $this->userActivities()->inProgress()->first();

        if ($currentActivity) {
            $now = now();
            $start = Carbon::parse($currentActivity->started_at);
            $duration = app(LunchBreakService::class)->getEffectiveDuration($start, $now, auth()->id());

            $currentActivity->pauses()->create(['paused_at' => $now]);
            $currentActivity->update([
                'status' => 'paused',
                'ended_at' => $now,
                'duration_minutes' => ($currentActivity->duration_minutes ?? 0) + $duration,
            ]);

            $interruptionCategoryId = Category::where('slug', 'interruption')->value('id');

            Activity::create([
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'category_id' => $interruptionCategoryId ?? 12,
                'type' => 'interruption',
                'parent_id' => $currentActivity->id,
                'source' => $validated['source'] ?? null,
                'person' => $validated['person'] ?? null,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        return redirect()->back();
    }

    public function resolveInterruption(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $now = now();
        $start = Carbon::parse($activity->started_at);
        $duration = $start->diffInMinutes($now);

        $activity->update([
            'status' => 'completed',
            'ended_at' => $now,
            'duration_minutes' => $duration,
        ]);

        if ($activity->parent_id) {
            $parent = Activity::find($activity->parent_id);
            if ($parent) {
                $parent->update([
                    'status' => 'in_progress',
                    'started_at' => now(),
                    'ended_at' => null,
                ]);
            }
        }

        return redirect()->back();
    }

    public function manualEntry(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:activity_contexts,id',
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'description' => 'nullable|string|max:1000',
        ]);

        $start = Carbon::parse($validated['started_at']);
        $end = Carbon::parse($validated['ended_at']);

        Activity::create([
            'user_id' => auth()->id(),
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'project_id' => $validated['project_id'] ?? null,
            'context_id' => $validated['context_id'] ?? null,
            'description' => $validated['description'] ?? null,
            'type' => 'activity',
            'status' => 'completed',
            'started_at' => $start,
            'ended_at' => $end,
            'duration_minutes' => app(LunchBreakService::class)->getEffectiveDuration($start, $end, auth()->id()),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'category_id' => 'exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'context_id' => 'nullable|exists:activity_contexts,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,normal,medium,high,critical',
            'source' => 'nullable|string',
            'person' => 'nullable|string',
            'is_planned' => 'boolean',
            'energy_level' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string',
            'started_at' => 'nullable|date',
            'ended_at' => 'nullable|date',
        ]);

        if ($request->has('started_at')) {
            $start = Carbon::parse($validated['started_at']);
            $validated['started_at'] = $start;

            if ($request->has('ended_at')) {
                $end = Carbon::parse($validated['ended_at']);
                $validated['ended_at'] = $end;
                $validated['duration_minutes'] = app(LunchBreakService::class)->getEffectiveDuration($start, $end, auth()->id());
            }
        }

        foreach (['priority', 'title', 'status', 'type', 'user_id'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] === null) {
                unset($validated[$field]);
            }
        }

        $activity->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        $mode = $request->input('mode', 'cascade');
        $affected = 0;

        if ($activity->children()->exists()) {
            if ($mode === 'convert') {
                $affected = $activity->children()->count();
                $activity->children()->update([
                    'type' => 'activity',
                    'parent_id' => null,
                ]);
            } else {
                $affected = $activity->children()->count();
                $activity->children()->delete();
            }
        }

        $activity->delete();

        return redirect()->back();
    }
}
