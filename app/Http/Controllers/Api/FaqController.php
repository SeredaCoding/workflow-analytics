<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\FaqTopic;

class FaqController extends Controller
{
    public function index()
    {
        $topics = FaqTopic::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->with(['faqs' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
            }])
            ->get()
            ->filter(fn($t) => $t->faqs->isNotEmpty())
            ->values();

        return response()->json($topics);
    }
}
