<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProblemReport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProblemReportController extends Controller
{
    public function index(Request $request)
    {
        $query = ProblemReport::with('user')->orderBy('created_at', 'desc');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($severity = $request->input('severity')) {
            $query->where('severity', $severity);
        }

        $reports = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/ProblemReports', [
            'reports' => $reports,
            'filters' => $request->only(['status', 'severity']),
            'canManage' => in_array(auth()->user()->role_id, [3, 4]),
        ]);
    }

    public function update(Request $request, ProblemReport $report)
    {
        if (!in_array(auth()->user()->role_id, [3, 4])) {
            abort(403, 'Apenas administradores e devs podem alterar o status.');
        }

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,resolved',
        ]);

        $report->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status atualizado com sucesso!');
    }

    public function destroy(ProblemReport $report)
    {
        if (!in_array(auth()->user()->role_id, [3, 4])) {
            abort(403);
        }

        $report->delete();

        return redirect()->back()->with('success', 'Relatório excluído com sucesso!');
    }
}
