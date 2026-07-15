<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessReportImages;
use App\Models\ProblemReport;
use App\Services\ImageService;
use Illuminate\Http\Request;

class ProblemReportController extends Controller
{
    public function store(Request $request, ImageService $imageService)
    {
        $validated = $request->validate([
            "description" => "required|string|max:10000",
            "severity" => "required|string|in:low,normal,medium,high,critical",
            "url" => "nullable|string|max:500",
            "page_name" => "nullable|string|max:255",
            "app_version" => "nullable|string|max:20",
            "browser_info" => "nullable|json",
            "primary_index" => "nullable|integer|min:0",
            "images" => "nullable|array|max:5",
            "images.*" => "image|mimes:jpeg,png,webp,gif|max:5120",
        ]);

        $imagePaths = [];

        if ($request->hasFile("images")) {
            foreach ($request->file("images") as $i => $file) {
                $path = $imageService->store($file, "problem-reports");
                $imagePaths[] = [
                    "path" => $path,
                    "primary" => ($request->input("primary_index") == $i) || (empty($imagePaths) && !$request->has("primary_index")),
                ];
            }
        }

        $report = ProblemReport::create([
            "user_id" => auth()->id(),
            "description" => $validated["description"],
            "severity" => $validated["severity"],
            "url" => $validated["url"] ?? $request->header("Referer", "/"),
            "page_name" => $validated["page_name"] ?? "",
            "app_version" => $validated["app_version"] ?? null,
            "browser_info" => $validated["browser_info"] ?? null,
            "images" => !empty($imagePaths) ? $imagePaths : null,
            "status" => "pending",
        ]);

        if (!empty($imagePaths)) {
            ProcessReportImages::dispatch($report->id);
        }

        return response()->json($report, 201);
    }
}
