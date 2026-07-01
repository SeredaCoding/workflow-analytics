<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with(['category', 'project', 'parent', 'children']);

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($projectId = $request->input('project_id')) {
            $query->where('project_id', $projectId);
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

        $inProgress = Activity::inProgress()->latest('started_at')->with(['category', 'project'])->first();

        return Inertia::render('Activities', [
            'activities' => $activities,
            'filters' => $request->only(['search', 'category_id', 'project_id', 'status', 'date_from', 'date_to', 'description', 'priority', 'energy_level', 'per_page']),
            'inProgress' => $inProgress ? [
                'id' => $inProgress->id,
                'title' => $inProgress->title,
                'type' => $inProgress->type,
                'parent_id' => $inProgress->parent_id,
                'category' => $inProgress->category?->name,
                'category_color' => $inProgress->category?->color,
                'project' => $inProgress->project?->name,
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
            'priority' => 'nullable|string|in:low,medium,high,critical',
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

        if (isset($validated['tags']) && is_string($validated['tags'])) {
            $validated['tags'] = json_decode($validated['tags'], true);
        }

        $activity = Activity::create($validated);

        return redirect()->back();
    }

    public function start(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string|max:1000',
            'started_at' => 'nullable|date|before_or_equal:now',
        ]);

        $startedAt = $validated['started_at'] ?? null;
        if ($startedAt) {
            // Re-parse so we get a Carbon instance with the timezone
            $startedAt = Carbon::parse($startedAt);
        }

        // Pause any current in-progress activity
        Activity::inProgress()->each(function ($a) {
            $a->update([
                'status' => 'paused',
                'ended_at' => now(),
            ]);
        });

        $activity = Activity::create([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'project_id' => $validated['project_id'],
            'description' => $validated['description'] ?? null,
            'started_at' => $startedAt ?? now(),
            'status' => 'in_progress',
            'type' => 'activity',
        ]);

        return redirect()->back();
    }

    public function pause(Activity $activity)
    {
        $now = now();
        $start = Carbon::parse($activity->started_at);
        $duration = $start->diffInMinutes($now);

        $activity->update([
            'status' => 'paused',
            'ended_at' => $now,
            'duration_minutes' => ($activity->duration_minutes ?? 0) + $duration,
        ]);

        return redirect()->back();
    }

    public function resume(Activity $activity)
    {
        $activity->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'ended_at' => null,
        ]);

        return redirect()->back();
    }

    public function stop(Activity $activity)
    {
        $now = now();
        $start = Carbon::parse($activity->started_at);
        $duration = $start->diffInMinutes($now);

        $activity->update([
            'status' => 'completed',
            'ended_at' => $now,
            'duration_minutes' => ($activity->duration_minutes ?? 0) + $duration,
        ]);

        return redirect()->back();
    }

    public function interrupt(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'nullable|string',
            'person' => 'nullable|string',
        ]);

        // Pause current in-progress activity
        $currentActivity = Activity::inProgress()->first();

        if ($currentActivity) {
            $now = now();
            $start = Carbon::parse($currentActivity->started_at);
            $duration = $start->diffInMinutes($now);

            $currentActivity->update([
                'status' => 'paused',
                'ended_at' => $now,
                'duration_minutes' => ($currentActivity->duration_minutes ?? 0) + $duration,
            ]);

            // Create interruption linked to paused activity
            $interruptionCategoryId = \App\Models\Category::where('slug', 'interruption')->value('id');

            Activity::create([
                'title' => $validated['title'],
                'category_id' => $interruptionCategoryId ?? 12,
                'type' => 'interruption',
                'parent_id' => $currentActivity->id,
                'source' => $validated['source'],
                'person' => $validated['person'],
                'started_at' => now(),
                'status' => 'in_progress',
            ]);
        }

        return redirect()->back();
    }

    public function resolveInterruption(Activity $activity)
    {
        $now = now();
        $start = Carbon::parse($activity->started_at);
        $duration = $start->diffInMinutes($now);

        $activity->update([
            'status' => 'completed',
            'ended_at' => $now,
            'duration_minutes' => $duration,
        ]);

        // Resume parent activity
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
            'started_at' => 'required|date',
            'ended_at' => 'required|date|after:started_at',
            'description' => 'nullable|string|max:1000',
        ]);

        $start = Carbon::parse($validated['started_at']);
        $end = Carbon::parse($validated['ended_at']);

        $activity = Activity::create([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'project_id' => $validated['project_id'],
            'description' => $validated['description'] ?? null,
            'type' => 'activity',
            'status' => 'completed',
            'started_at' => $start,
            'ended_at' => $end,
            'duration_minutes' => $start->diffInMinutes($end),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Activity $activity)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'category_id' => 'exists:categories,id',
            'project_id' => 'nullable|exists:projects,id',
            'description' => 'nullable|string',
            'priority' => 'nullable|string|in:low,medium,high,critical',
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
                $validated['duration_minutes'] = $start->diffInMinutes($end);
            }
        }

        $activity->update($validated);

        return redirect()->back();
    }

    public function destroy(Request $request, Activity $activity)
    {
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
