<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProblemReport;
use Illuminate\Http\Request;

class ProblemReportController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:10000',
            'severity' => 'required|string|in:low,normal,medium,high,critical',
            'url' => 'nullable|string|max:500',
            'page_name' => 'nullable|string|max:255',
            'browser_info' => 'nullable|json',
        ]);

        $report = ProblemReport::create([
            'user_id' => auth()->id(),
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'url' => $validated['url'] ?? $request->header('Referer', '/'),
            'page_name' => $validated['page_name'] ?? '',
            'browser_info' => $validated['browser_info'] ?? null,
        ]);

        return response()->json($report, 201);
    }
}
