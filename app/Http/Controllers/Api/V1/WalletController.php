<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Http\Traits\ApiResponse;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    use ApiResponse;

    public function show(Request $request)
    {
        $wallet = Wallet::firstOrCreate(['user_id' => $request->user()->id]);
        $wallet->load('transactions');

        return $this->success(new WalletResource($wallet));
    }
}
