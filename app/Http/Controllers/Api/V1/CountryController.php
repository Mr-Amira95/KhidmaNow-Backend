<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Traits\ApiResponse;
use App\Models\Country;

class CountryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $countries = Country::where('is_active', true)->orderBy('name_en')->get();
        return $this->success(CountryResource::collection($countries));
    }
}
