<?php

namespace App\Http\Middleware;

use App\Models\Category;
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
                ? Category::where('user_id', $request->user()->id)->where('is_active', true)->orderBy('sort_order')->get()
                : Category::where('is_active', true)->orderBy('sort_order')->get(),
            'projects' => $request->user()
                ? Project::where('user_id', $request->user()->id)->where('is_active', true)->get()
                : Project::where('is_active', true)->get(),
            'lunch_start' => $request->user()
                ? Setting::where('user_id', $request->user()->id)->where('key', 'lunch_start')->value('value')
                : null,
            'lunch_end' => $request->user()
                ? Setting::where('user_id', $request->user()->id)->where('key', 'lunch_end')->value('value')
                : null,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ];
    }
}
