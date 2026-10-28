<?php

namespace App\Http\Controllers;

use App\Models\Version;
use Inertia\Inertia;

class VersionController extends Controller
{
    public function index()
    {
        $versions = Version::where('is_active', true)
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn($v) => [
                'version' => $v->version,
                'release_date' => $v->release_date?->format('Y-m-d'),
                'description' => $v->description,
                'changes' => $v->changes,
            ]);

        return Inertia::render('Changelog', [
            'versions' => $versions,
        ]);
    }
}
