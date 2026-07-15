<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\LinkPreviewService;
use Illuminate\Http\Request;

class ProjectLinkController extends Controller
{
    public function __construct(
        private LinkPreviewService $preview,
    ) {}

    public function store(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$this->canModifyProject($user, $project)) {
            abort(403);
        }

        $validated = $request->validate([
            'url' => 'required|url|max:2000',
        ]);

        $exists = $project->links()->where('url', $validated['url'])->exists();
        if ($exists) {
            return response()->json(['message' => 'Este link já foi adicionado.'], 409);
        }

        $preview = $this->preview->fetch($validated['url']);

        $link = $project->links()->create([
            'url' => $validated['url'],
            'title' => $preview['title'],
            'description' => $preview['description'],
            'image_url' => $preview['image_url'],
            'sort_order' => $project->links()->count() + 1,
        ]);

        return response()->json($link, 201);
    }

    public function destroy(Project $project, \App\Models\ProjectLink $link)
    {
        $user = auth()->user();
        if (!$this->canModifyProject($user, $project)) {
            abort(403);
        }

        if ($link->project_id !== $project->id) {
            abort(404);
        }

        $link->delete();

        return response()->noContent();
    }

    public function refresh(Request $request, Project $project, \App\Models\ProjectLink $link)
    {
        $user = auth()->user();
        if (!$this->canModifyProject($user, $project)) {
            abort(403);
        }

        if ($link->project_id !== $project->id) {
            abort(404);
        }

        $preview = $this->preview->fetch($link->url);

        $link->update([
            'title' => $preview['title'],
            'description' => $preview['description'],
            'image_url' => $preview['image_url'],
        ]);

        return response()->json($link);
    }

    private function canModifyProject($user, Project $project): bool
    {
        if ($user->isAdmin()) return true;
        if ($project->user_id === $user->id) return true;
        if ($project->users()->where('user_id', $user->id)->exists()) return true;
        if ($project->visibility === 'global') return true;
        if ($project->visibility === 'sector' && $project->sectors()->where('id', $user->sector_id)->exists()) return true;
        return false;
    }
}
