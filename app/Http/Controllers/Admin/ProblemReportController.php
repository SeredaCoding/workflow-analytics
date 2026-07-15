<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProblemReport;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProblemReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $query = ProblemReport::with("user")->orderBy("created_at", "desc");

        if ($status = $request->input("status")) {
            $query->where("status", $status);
        }

        if ($severity = $request->input("severity")) {
            $query->where("severity", $severity);
        }

        $reports = $query->paginate(20)->withQueryString();

        return Inertia::render("Admin/ProblemReports", [
            "reports" => $reports,
            "filters" => $request->only(["status", "severity"]),
        ]);
    }

    public function update(Request $request, ProblemReport $report)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $rules = [
            "status" => "required|string|in:pending,analyzing,resolved",
        ];

        if ($request->input("status") === "resolved") {
            $rules["resolution_notes"] = "required|string|max:10000";
        }

        $validated = $request->validate($rules);

        $data = ["status" => $validated["status"]];

        if ($validated["status"] === "resolved") {
            $data["resolution_notes"] = $validated["resolution_notes"];
            $data["resolved_at"] = now();
        }

        $report->update($data);

        return redirect()->back()->with("success", "Status atualizado com sucesso!");
    }

    public function showImage(ProblemReport $report, int $index, ImageService $imageService)
    {
        $user = auth()->user();

        $isAdmin = in_array($user->role_id, [3, 4]);
        $isOwner = $report->user_id === $user->id;

        if (!$isAdmin && !$isOwner) {
            abort(403);
        }

        $path = $report->getImagePath($index);

        if (!$path || !\Illuminate\Support\Facades\Storage::disk("local")->exists($path)) {
            abort(404);
        }

        return $imageService->serve($path);
    }

    public function destroy(ProblemReport $report)
    {
        abort(404);
    }
}
