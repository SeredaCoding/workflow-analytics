<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::where('user_id', auth()->id())->pluck('value', 'key');

        $inProgress = Activity::where('user_id', auth()->id())->inProgress()->latest('started_at')->with(['category', 'project'])->first();

        return Inertia::render('Settings', [
            'settings' => $settings,
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

    public function update(Request $request)
    {
        $validated = $request->validate([
            'user_name' => 'nullable|string|max:255',
            'boss_email' => 'nullable|email|max:255',
            'report_subject' => 'nullable|string|max:255',
            'report_template' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['user_id' => auth()->id(), 'key' => $key],
                ['value' => $value ?? ''],
            );
        }

        return redirect()->back()->with('success', 'Configurações salvas.');
    }
}
