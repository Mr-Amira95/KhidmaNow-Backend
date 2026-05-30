<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Http\Traits\ApiResponse;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Wallet::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        return $this->paginated(WalletResource::class, $query->latest());
    }

    public function show(Wallet $wallet)
    {
        $wallet->load(['user', 'transactions']);
        return $this->success(new WalletResource($wallet));
    }
}
