<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth as FirebaseAuth;

/**
 * Bridges Sanctum-authenticated users into Firebase so mobile clients can read/write
 * their own chat data directly in Firestore under security rules keyed on this uid.
 */
class FirebaseTokenController extends Controller
{
    use ApiResponse;

    public function issue(Request $request, FirebaseAuth $firebaseAuth)
    {
        $user = $request->user();

        $token = $firebaseAuth->createCustomToken((string) $user->id, [
            'user_type' => $user->user_type,
        ]);

        return $this->success([
            'firebase_token' => $token->toString(),
        ], 'Firebase token generated.');
    }
}
