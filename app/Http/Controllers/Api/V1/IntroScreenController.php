<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\IntroScreenResource;
use App\Http\Traits\ApiResponse;
use App\Models\IntroScreen;

class IntroScreenController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $introScreens = IntroScreen::where('is_active', true)->orderBy('order')->get();
        return $this->success(IntroScreenResource::collection($introScreens));
    }
}
