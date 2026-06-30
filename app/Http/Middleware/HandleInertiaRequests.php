<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Project;
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
                'user' => $request->user(),
            ],
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'projects' => Project::where('is_active', true)->get(),
        ];
    }
}
