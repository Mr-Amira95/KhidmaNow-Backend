<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectDocumentRequest;
use App\Http\Resources\ProviderDocumentResource;
use App\Http\Traits\ApiResponse;
use App\Models\ProviderDocument;
use Illuminate\Http\Request;

class ProviderDocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = ProviderDocument::with(['provider.user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }

        return $this->paginated(ProviderDocumentResource::class, $query->latest());
    }

    public function show(ProviderDocument $providerDocument)
    {
        $providerDocument->load(['provider.user']);
        return $this->success(new ProviderDocumentResource($providerDocument));
    }

    public function approve(ProviderDocument $providerDocument)
    {
        $providerDocument->update([
            'status'           => 'approved',
            'rejection_reason' => null,
        ]);
        return $this->success(new ProviderDocumentResource($providerDocument), 'Document approved.');
    }

    public function reject(RejectDocumentRequest $request, ProviderDocument $providerDocument)
    {
        $providerDocument->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->rejection_reason,
        ]);
        return $this->success(new ProviderDocumentResource($providerDocument), 'Document rejected.');
    }
}
