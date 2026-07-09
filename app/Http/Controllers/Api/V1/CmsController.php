<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PrivacyPolicyResource;
use App\Http\Resources\TermsAndConditionResource;
use App\Http\Traits\ApiResponse;
use App\Models\PrivacyPolicy;
use App\Models\TermsAndCondition;

class CmsController extends Controller
{
    use ApiResponse;

    public function terms()
    {
        $terms = TermsAndCondition::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        return $this->success(new TermsAndConditionResource($terms));
    }

    public function privacy()
    {
        $privacyPolicy = PrivacyPolicy::firstOrCreate([], ['content_ar' => '', 'content_en' => '']);
        return $this->success(new PrivacyPolicyResource($privacyPolicy));
    }
}
