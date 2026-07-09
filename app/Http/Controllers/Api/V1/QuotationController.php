<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Resources\QuotationResource;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\Notification;
use App\Models\Provider;
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
            ->when($user->user_type === 'customer', fn ($q) => $q->where('user_id', $user->id));

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $this->paginated(QuotationResource::class, $query->latest());
    }

    public function show(Request $request, Quotation $quotation)
    {
        $user = $request->user();

        if ($user->user_type === 'customer' && $quotation->user_id !== $user->id) {
            return $this->error('You are not part of this quotation.', 403);
        }

        if ($user->user_type === 'provider') {
            $isMatch = $user->provider && $user->provider->subCategories()->where('sub_category_id', $quotation->sub_category_id)->exists();
            $hasBid = $user->provider && $quotation->bids()->where('provider_id', $user->provider->id)->exists();
            if (!$isMatch && !$hasBid) {
                return $this->error('You are not part of this quotation.', 403);
            }
        }

        $quotation->load(['user', 'category', 'subCategory', 'bids.provider.user', 'track.changedBy', 'serviceRequest']);
        return $this->success(new QuotationResource($quotation));
    }

    public function store(StoreQuotationRequest $request)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer') {
            return $this->error('Only clients can create a quotation.', 403);
        }

        $quotation = Quotation::create([
            ...$request->validated(),
            'user_id' => $user->id,
            'status'  => 'open',
        ]);

        QuotationTrack::create([
            'quotation_id' => $quotation->id,
            'from_status'  => null,
            'to_status'    => 'open',
            'changed_by'   => $user->id,
            'date_time'    => now(),
        ]);

        $this->notifyMatchingProviders($quotation);

        return $this->success(new QuotationResource($quotation), 'Quotation created successfully.', 201);
    }

    public function approveBid(Request $request, Quotation $quotation, QuotationBid $bid, QuotationService $quotationService)
    {
        $user = $request->user();
        if ($user->user_type !== 'customer' || $quotation->user_id !== $user->id) {
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

    private function notifyMatchingProviders(Quotation $quotation): void
    {
        $providers = Provider::whereHas('subCategories', function ($q) use ($quotation) {
            $q->where('sub_category_id', $quotation->sub_category_id);
        })->get();

        if ($providers->isEmpty()) {
            return;
        }

        $notifications = $providers->map(fn (Provider $provider) => [
            'user_id'    => $provider->user_id,
            'title'      => 'New quotation request',
            'body'       => $quotation->title ?: 'A new quotation matching your services is available.',
            'icon'       => null,
            'type'       => 'service_request',
            'type_id'    => $quotation->id,
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ])->toArray();

        Notification::insert($notifications);
    }
}
