<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\StoreQuotationBidRequest;
use App\Http\Requests\Provider\UpdateQuotationBidRequest;
use App\Http\Resources\QuotationBidResource;
use App\Http\Traits\ApiResponse;
use App\Models\Quotation;
use App\Models\QuotationBid;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    use ApiResponse;

    public function storeBid(StoreQuotationBidRequest $request, Quotation $quotation)
    {
        $provider = $request->user()->provider;

        if ($provider->isSuspended()) {
            return $this->error('Your account is currently suspended and cannot place bids.', 403);
        }

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

        \App\Services\NotificationService::send(
            $quotation->user_id,
            'New Bid Received',
            'Provider ' . ($provider->business_name ?? $request->user()->name) . ' has submitted a bid of ' . $bid->price . ' on your quotation "' . $quotation->title . '".',
            'quotation',
            $quotation->id
        );

        $bid->load('provider.user');

        return $this->success(new QuotationBidResource($bid), 'Bid submitted successfully.', 201);
    }

    public function updateBid(UpdateQuotationBidRequest $request, Quotation $quotation, QuotationBid $bid)
    {
        $provider = $request->user()->provider;

        if ((int) $bid->quotation_id !== (int) $quotation->id) {
            return $this->error('This bid does not belong to this quotation.', 422);
        }

        if (!$provider || (int) $bid->provider_id !== (int) $provider->id) {
            return $this->error('You are not allowed to edit this bid.', 403);
        }

        if ($quotation->status !== 'open') {
            return $this->error("This quotation is already '{$quotation->status}'.", 422);
        }

        if ($bid->status !== 'pending') {
            return $this->error("This bid is already '{$bid->status}' and cannot be edited.", 422);
        }

        $bid->update($request->validated());

        $bid->load('provider.user');

        return $this->success(new QuotationBidResource($bid), 'Bid updated successfully.');
    }

    public function destroyBid(Request $request, Quotation $quotation, QuotationBid $bid)
    {
        $provider = $request->user()->provider;

        if ((int) $bid->quotation_id !== (int) $quotation->id) {
            return $this->error('This bid does not belong to this quotation.', 422);
        }

        if (!$provider || (int) $bid->provider_id !== (int) $provider->id) {
            return $this->error('You are not allowed to delete this bid.', 403);
        }

        if ($bid->status !== 'pending') {
            return $this->error("This bid is already '{$bid->status}' and cannot be deleted.", 422);
        }

        $bid->delete();

        return $this->success([], 'Bid deleted successfully.');
    }
}
