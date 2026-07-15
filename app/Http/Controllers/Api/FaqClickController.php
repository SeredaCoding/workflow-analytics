<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Faq;

class FaqClickController extends Controller
{
    public function __invoke(Faq $faq)
    {
        $faq->increment('clicks');

        return response()->noContent();
    }
}
