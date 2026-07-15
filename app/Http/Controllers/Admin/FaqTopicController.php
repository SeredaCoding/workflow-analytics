<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FaqTopic;
use Illuminate\Http\Request;

class FaqTopicController extends Controller
{
    public function index()
    {
        $topics = FaqTopic::orderBy('sort_order')->orderBy('name')->get();

        return response()->json($topics);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (FaqTopic::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $topic = FaqTopic::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($topic, 201);
    }

    public function update(Request $request, FaqTopic $topic)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = \Illuminate\Support\Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (FaqTopic::where('slug', $slug)->where('id', '!=', $topic->id)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $topic->update([
            'name' => $validated['name'],
            'slug' => $slug,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($topic);
    }

    public function destroy(FaqTopic $topic)
    {
        $topic->faqs()->update(['faq_topic_id' => null]);
        $topic->delete();

        return response()->noContent();
    }
}
