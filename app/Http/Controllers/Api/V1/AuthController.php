<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AuthController extends Controller
{
    /**
     * Login user with email or phone.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // email or phone
            'password' => 'required|string',
            'device_name' => 'required|string',
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $login = $request->login;
        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        // Update device token if provided
        if ($request->device_token) {
            $user->update(['device_token' => $request->device_token]);
        }

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'token' => $token,
            ]
        ]);
    }

    /**
     * Logout user and revoke token.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Step 1: Request verification code for forgot password.
     */
    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string', // email or phone
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->login)->orWhere('phone', $request->login)->first();

        if (!$user) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $code = rand(1000, 9999);
        
        VerificationCode::updateOrCreate(
            ['identifier' => $request->login],
            [
                'code' => $code, 
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // TODO: Send code via Email or SMS
        
        return response()->json([
            'message' => 'Verification code sent',
            'code' => $code // REMOVE IN PRODUCTION
        ]);
    }

    /**
     * Step 2: Verify the code.
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = VerificationCode::where('identifier', $request->login)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        return response()->json(['message' => 'Code verified successfully']);
    }

    /**
     * Step 3: Reset password using code.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $record = VerificationCode::where('identifier', $request->login)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        $user = User::where('email', $request->login)->orWhere('phone', $request->login)->first();
        $user->update(['password' => Hash::make($request->password)]);

        // Delete the code
        $record->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
