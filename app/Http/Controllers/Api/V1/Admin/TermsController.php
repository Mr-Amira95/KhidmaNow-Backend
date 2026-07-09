<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTermsRequest;
use App\Http\Resources\TermsAndConditionResource;
use App\Http\Traits\ApiResponse;
use App\Models\TermsAndCondition;

class TermsController extends Controller
{
    use ApiResponse;

    public function show()
    {
        $terms = TermsAndCondition::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        return $this->success(new TermsAndConditionResource($terms));
    }

    public function update(UpdateTermsRequest $request)
    {
        $terms = TermsAndCondition::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        $terms->update($request->validated());
        return $this->success(new TermsAndConditionResource($terms), 'Terms & conditions updated successfully.');
    }
}
