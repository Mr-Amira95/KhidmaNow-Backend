<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreQuotationBidRequest;
use App\Http\Resources\QuotationBidResource;
use App\Http\Traits\ApiResponse;
use App\Models\Quotation;
use App\Models\QuotationBid;

class QuotationController extends Controller
{
    use ApiResponse;

    public function storeBid(StoreQuotationBidRequest $request, Quotation $quotation)
    {
        $provider = $request->user()->provider;

        if ($quotation->status !== 'open') {
            return $this->error("This quotation is already '{$quotation->status}'.", 422);
        }

        $matchesSubCategory = $provider->subCategories()->where('sub_category_id', $quotation->sub_category_id)->exists();
        if (!$matchesSubCategory) {
            return $this->error('This quotation does not match your services.', 403);
        }

        if (QuotationBid::where('quotation_id', $quotation->id)->where('provider_id', $provider->id)->exists()) {
            return $this->error('You already placed a bid on this quotation.', 422);
        }

        $bid = QuotationBid::create([
            ...$request->validated(),
            'quotation_id' => $quotation->id,
            'provider_id'  => $provider->id,
            'status'       => 'pending',
        ]);

        $bid->load('provider.user');

        return $this->success(new QuotationBidResource($bid), 'Bid submitted successfully.', 201);
    }
}
