<?php

namespace App\Http\Middleware;

use App\Models\Activity;
use App\Models\ActivityContext;
use App\Models\Category;
use App\Models\Faq;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()?->load(['sector', 'role']),
            ],
            'categories' => $request->user()
                ? Category::where('is_active', true)
                    ->where(function ($q) use ($request) {
                        $user = $request->user();
                        $q->where('visibility', 'global')
                          ->orWhere(function ($q) use ($user) {
                              $q->where('visibility', 'sector')
                                ->whereHas('sectors', fn($q) => $q->where('id', $user->sector_id));
                          })
                          ->orWhere(function ($q) use ($user) {
                              $q->where('visibility', 'user')
                                ->whereHas('users', fn($q) => $q->where('id', $user->id));
                          });
                    })
                    ->orderBy('sort_order')->get()
                : Category::where('is_active', true)->where('visibility', 'global')->orderBy('sort_order')->get(),
            'projects' => $request->user()
                ? Project::where('is_active', true)
                    ->where(function ($q) use ($request) {
                        $user = $request->user();
                        $q->where('visibility', 'global')
                          ->orWhere(function ($q) use ($user) {
                              $q->where('visibility', 'sector')
                                ->whereHas('sectors', fn($q) => $q->where('id', $user->sector_id));
                          })
                          ->orWhere(function ($q) use ($user) {
                              $q->where('visibility', 'user')
                                ->whereHas('users', fn($q) => $q->where('id', $user->id));
                          });
                    })
                    ->orderBy('name')->get()
                : Project::where('is_active', true)->where('visibility', 'global')->orderBy('name')->get(),
            'lunch_start' => $request->user()
                ? Setting::where('user_id', $request->user()->id)->where('key', 'lunch_start')->value('value')
                : null,
            'lunch_end' => $request->user()
                ? Setting::where('user_id', $request->user()->id)->where('key', 'lunch_end')->value('value')
                : null,
            'latestPaused' => $request->user()
                ? Activity::where('user_id', $request->user()->id)
                    ->where('status', 'paused')
                    ->where('type', 'activity')
                    ->latest('updated_at')
                    ->first()
                : null,
            'hasStats' => $request->user()
                ? Activity::where('user_id', $request->user()->id)->exists()
                : false,
            'modules' => ActivityContext::where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'faqs' => Faq::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get(),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }
}
