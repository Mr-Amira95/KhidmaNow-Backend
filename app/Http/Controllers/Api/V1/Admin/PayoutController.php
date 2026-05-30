<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePayoutStatusRequest;
use App\Http\Resources\PayoutResource;
use App\Http\Traits\ApiResponse;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Payout::with(['provider.user', 'serviceRequest']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return $this->paginated(PayoutResource::class, $query->latest());
    }

    public function show(Payout $payout)
    {
        $payout->load(['provider.user', 'serviceRequest']);
        return $this->success(new PayoutResource($payout));
    }

    public function updateStatus(UpdatePayoutStatusRequest $request, Payout $payout)
    {
        $data = ['status' => $request->status];
        if ($request->status === 'paid') {
            $data['paid_at'] = now();
        }
        $payout->update($data);
        return $this->success(new PayoutResource($payout), 'Payout status updated.');
    }
}
