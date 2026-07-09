<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePrivacyPolicyRequest;
use App\Http\Resources\PrivacyPolicyResource;
use App\Http\Traits\ApiResponse;
use App\Models\PrivacyPolicy;

class PrivacyPolicyController extends Controller
{
    use ApiResponse;

    public function show()
    {
        $privacyPolicy = PrivacyPolicy::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        return $this->success(new PrivacyPolicyResource($privacyPolicy));
    }

    public function update(UpdatePrivacyPolicyRequest $request)
    {
        $privacyPolicy = PrivacyPolicy::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        $privacyPolicy->update($request->validated());
        return $this->success(new PrivacyPolicyResource($privacyPolicy), 'Privacy policy updated successfully.');
    }
}
