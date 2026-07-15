<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTopic;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::with('topic')->orderBy('sort_order')->orderBy('id');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        $faqs = $query->paginate(20)->withQueryString();

        $topClickFaqs = Faq::where('clicks', '>', 0)->orderBy('clicks', 'desc')->take(10)->get();

        return Inertia::render('Admin/Faqs', [
            'faqs' => $faqs,
            'filters' => $request->only(['search']),
            'topics' => FaqTopic::orderBy('sort_order')->orderBy('name')->get(),
            'topClickFaqs' => $topClickFaqs,
            'maxClicks' => $topClickFaqs->max('clicks') ?: 1,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:10000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'faq_topic_id' => 'nullable|exists:faq_topics,id',
        ]);

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ criada com sucesso!');
    }

    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:1000',
            'answer' => 'required|string|max:10000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'faq_topic_id' => 'nullable|exists:faq_topics,id',
        ]);

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ atualizada com sucesso!');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ excluída com sucesso!');
    }
}
