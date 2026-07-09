<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Http\Traits\ApiResponse;
use App\Models\Faq;

class FaqController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $faqs = Faq::where('is_active', true)->orderBy('order')->get();
        return $this->success(FaqResource::collection($faqs));
    }
}
