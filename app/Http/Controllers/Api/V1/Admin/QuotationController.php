<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuotationBidRequest;
use App\Http\Requests\Admin\StoreQuotationRequest;
use App\Http\Resources\QuotationBidResource;
use App\Http\Resources\QuotationResource;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\Quotation;
use App\Models\QuotationBid;
use App\Models\QuotationTrack;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class QuotationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Quotation::with(['user', 'category', 'subCategory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $this->paginated(QuotationResource::class, $query->latest());
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['user', 'category', 'subCategory', 'bids.provider.user', 'track.changedBy', 'serviceRequest']);
        return $this->success(new QuotationResource($quotation));
    }

    public function store(StoreQuotationRequest $request)
    {
        $quotation = Quotation::create([
            ...$request->validated(),
            'status' => 'open',
        ]);

        QuotationTrack::create([
            'quotation_id' => $quotation->id,
            'from_status'  => null,
            'to_status'    => 'open',
            'changed_by'   => $request->user()->id,
            'date_time'    => now(),
        ]);

        return $this->success(new QuotationResource($quotation), 'Quotation created successfully.', 201);
    }

    public function storeBid(StoreQuotationBidRequest $request, Quotation $quotation)
    {
        if ($quotation->status !== 'open') {
            return $this->error("This quotation is already '{$quotation->status}'.", 422);
        }

        $bid = QuotationBid::create([
            ...$request->validated(),
            'quotation_id' => $quotation->id,
            'status'       => 'pending',
        ]);

        $bid->load('provider.user');

        return $this->success(new QuotationBidResource($bid), 'Bid created successfully.', 201);
    }

    public function approveBid(Request $request, Quotation $quotation, QuotationBid $bid, QuotationService $quotationService)
    {
        try {
            $serviceRequest = $quotationService->approveBid($quotation, $bid, $request->user());
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(new ServiceRequestResource($serviceRequest), 'Bid approved. Request created successfully.');
    }
}
