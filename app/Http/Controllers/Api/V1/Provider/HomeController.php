<?php

namespace App\Http\Controllers\Api\V1\Provider;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProviderResource;
use App\Http\Resources\QuotationResource;
use App\Http\Resources\ServiceRequestResource;
use App\Http\Traits\ApiResponse;
use App\Models\Quotation;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    use ApiResponse;

    protected const ACTIVE_JOB_STATUSES = ['approved', 'in_progress', 'confirmed'];

    public function index(Request $request)
    {
        $user = $request->user();
        $provider = $user->provider;

        if (!$provider) {
            return $this->error('Provider profile not found.', 404);
        }

        $provider->load(['user', 'city', 'subCategories.subCategory']);

        $incomingServiceRequests = $provider->serviceRequests()
            ->where('status', 'pending')
            ->with(['user', 'subCategory'])
            ->latest()
            ->take(5)
            ->get();

        $activeJobs = $provider->serviceRequests()
            ->whereIn('status', self::ACTIVE_JOB_STATUSES)
            ->with(['user', 'subCategory'])
            ->latest()
            ->take(5)
            ->get();

        $subCategoryIds = $provider->subCategories()->pluck('sub_category_id');

        $openQuotations = Quotation::query()
            ->where('status', 'open')
            ->whereIn('sub_category_id', $subCategoryIds)
            ->whereDoesntHave('bids', fn ($q) => $q->where('provider_id', $provider->id))
            ->with(['user', 'category', 'subCategory'])
            ->latest()
            ->take(5)
            ->get();

        return $this->success([
            'provider'                          => new ProviderResource($provider),
            'wallet_balance'                     => $user->wallet?->balance ?? 0,
            'pending_payouts_total'              => $provider->payouts()->where('status', 'pending')->sum('amount'),
            'unread_notifications_count'         => $user->notifications()->where('is_read', false)->count(),
            'incoming_service_requests_count'    => $provider->serviceRequests()->where('status', 'pending')->count(),
            'incoming_service_requests'          => ServiceRequestResource::collection($incomingServiceRequests),
            'active_jobs_count'                  => $provider->serviceRequests()->whereIn('status', self::ACTIVE_JOB_STATUSES)->count(),
            'active_jobs'                        => ServiceRequestResource::collection($activeJobs),
            'open_quotations_count'               => Quotation::query()
                ->where('status', 'open')
                ->whereIn('sub_category_id', $subCategoryIds)
                ->whereDoesntHave('bids', fn ($q) => $q->where('provider_id', $provider->id))
                ->count(),
            'open_quotations'                    => QuotationResource::collection($openQuotations),
        ]);
    }
}
