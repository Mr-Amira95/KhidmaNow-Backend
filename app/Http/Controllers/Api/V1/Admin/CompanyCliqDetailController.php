<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCompanyCliqDetailRequest;
use App\Http\Resources\CompanyCliqDetailResource;
use App\Http\Traits\ApiResponse;
use App\Models\CompanyCliqDetail;

class CompanyCliqDetailController extends Controller
{
    use ApiResponse;

    public function show()
    {
        $cliqDetail = CompanyCliqDetail::firstOrCreate([], ['alias' => '', 'bank_name' => '', 'holder_name' => '']);
        return $this->success(new CompanyCliqDetailResource($cliqDetail));
    }

    public function update(UpdateCompanyCliqDetailRequest $request)
    {
        $cliqDetail = CompanyCliqDetail::firstOrCreate([], ['alias' => '', 'bank_name' => '', 'holder_name' => '']);
        $cliqDetail->update($request->validated());
        return $this->success(new CompanyCliqDetailResource($cliqDetail), 'Company CliQ details updated successfully.');
    }
}
