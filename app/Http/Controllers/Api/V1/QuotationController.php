<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\Quotation;
use App\Models\QuotationAttachment;
use App\Models\QuotationBid;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use InvalidArgumentException;

class QuotationController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function index(Request $request)
    {
        $user = $request->user();

        $query = Quotation::with(['user', 'category', 'subCategory'])
            ->when($user->user_type === 'provider', function ($q) use ($user) {
                $subCategoryIds = $user->provider
                    ? $user->provider->subCategories()->pluck('sub_category_id')
                    : collect();

                $q->where(function ($inner) use ($subCategoryIds, $user) {
                    $inner->where(function ($open) use ($subCategoryIds) {
                        $open->where('status', 'open')->whereIn('sub_category_id', $subCategoryIds);
                    });

                    if ($user->provider) {
                        $inner->orWhereHas('bids', fn ($b) => $b->where('provider_id', $user->provider->id));
                    }
                });
            })
            ->when($user->user_type === 'customer', fn ($q) => $q->where('user_id', $user->id))
            ->with(['bids' => $this->bidsConstraint($user)]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->paginated(QuotationResource::class, $query->latest());
    }

    public function show(Request $request, Quotation $quotation)
    {
        $user = $request->user();

        if ($user->user_type === 'customer' && (int) $quotation->user_id !== (int) $user->id) {
            return $this->error('You are not part of this quotation.', 403);
        }

        if ($user->user_type === 'provider') {
            $isMatch = $user->provider && $user->provider->subCategories()->where('sub_category_id', $quotation->sub_category_id)->exists();
            $hasBid = $user->provider && $quotation->bids()->where('provider_id', $user->provider->id)->exists();
            if (!$isMatch && !$hasBid) {
                return $this->error('You are not part of this quotation.', 403);
            }
        }

        $quotation->load([
            'user', 'category', 'subCategory',
            'bids' => $this->bidsConstraint($user),
            'track.changedBy', 'serviceRequest', 'attachments',
        ]);

        return $this->success(new QuotationResource($quotation));
    }

    /**
     * A provider must only ever see their own bid on a quotation, never competing providers' bids.
     */
    private function bidsConstraint($user)
    {
        return function ($q) use ($user) {
            $q->with('provider.user');

            if ($user->user_type === 'provider') {
                $q->where('provider_id', $user->provider?->id);
            }
        };
    }

    public function store(StoreQuotationRequest $request, QuotationService $quotationService)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer') {
            return $this->error('Only clients can create a quotation.', 403);
        }

        $data = $request->validated();
        unset($data['attachments']);

        $quotation = $quotationService->create($user, $data);

        foreach ($request->file('attachments', []) as $file) {
            QuotationAttachment::create([
                'quotation_id' => $quotation->id,
                'url'          => $this->storeUpload($file, 'quotations'),
                'type'         => $this->attachmentType($file),
            ]);
        }

        $quotation->load('attachments');

        return $this->success(new QuotationResource($quotation), 'Quotation created successfully.', 201);
    }

    public function approveBid(Request $request, Quotation $quotation, QuotationBid $bid, QuotationService $quotationService)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || (int) $quotation->user_id !== (int) $user->id) {
            return $this->error('You are not allowed to approve bids on this quotation.', 403);
        }

        try {
            $serviceRequest = $quotationService->approveBid($quotation, $bid, $user);
        } catch (InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            new ServiceRequestResource($serviceRequest),
            'Bid approved. Request created successfully.'
        );
    }
}
