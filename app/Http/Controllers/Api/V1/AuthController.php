<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\DeviceToken;
use App\Models\User;
use App\Models\VerificationCode;
use App\Services\SocialAuthService;
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
            'platform' => 'required_with:device_token|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $login = $request->login;
        $user = User::where('email', $login)->orWhere('phone', $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        $this->registerDeviceToken($user, $request);

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
     * Login (or register) via a Google/Apple id_token issued to the mobile app.
     */
    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'provider' => 'required|string|in:google,apple',
            'id_token' => 'required|string',
            'email' => 'nullable|email',
            'device_name' => 'required|string',
            'device_token' => 'nullable|string',
            'platform' => 'required_with:device_token|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = new SocialAuthService();

        try {
            $claims = $request->provider === 'google'
                ? $service->verifyGoogleToken($request->id_token)
                : $service->verifyAppleToken($request->id_token);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Invalid social login token.'], 401);
        }

        $providerIdField = $request->provider === 'google' ? 'google_id' : 'apple_id';
        $providerId = $claims['sub'] ?? null;
        $email = $request->email ?? $claims['email'] ?? null;

        if (!$providerId) {
            return response()->json(['message' => 'Invalid social login token.'], 401);
        }

        $user = User::where($providerIdField, $providerId)->first();

        if (!$user && $email) {
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([$providerIdField => $providerId]);
            }
        }

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        $this->registerDeviceToken($user, $request);

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
        $validator = Validator::make($request->all(), [
            'device_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $request->user()->currentAccessToken()->delete();

        if ($request->filled('device_token')) {
            DeviceToken::where('user_id', $request->user()->id)
                ->where('token', $request->device_token)
                ->delete();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        $currentTokenId = $user->currentAccessToken()->id;
        $user->tokens()->where('id', '!=', $currentTokenId)->delete();

        return response()->json(['message' => 'Password changed successfully']);
    }

    /**
     * Send a verification code to a phone number, for either registration
     * (verify a new phone before creating the account) or password reset.
     */
    public function checkPhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'purpose' => 'required|string|in:register,reset_password',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingUser = User::where('phone', $request->phone)->first();

        if ($request->purpose === 'register' && $existingUser) {
            return response()->json(['message' => 'This phone number is already registered.'], 422);
        }

        if ($request->purpose === 'reset_password' && !$existingUser) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $code = rand(1000, 9999);

        VerificationCode::updateOrCreate(
            ['identifier' => $request->phone, 'purpose' => $request->purpose],
            [
                'code' => $code,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // TODO: Send code via SMS

        return response()->json([
            'message' => 'Verification code sent',
            'code' => $code // REMOVE IN PRODUCTION
        ]);
    }

    /**
     * Step 1: Request verification code for forgot password (email or phone).
     * Kept as a distinct, unchanged method for backward compatibility with
     * existing admin-portal callers.
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
            ['identifier' => $request->login, 'purpose' => 'reset_password'],
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
     * Step 2: Verify the code. Shared by the admin (login+code) and mobile
     * (phone+purpose+code) flows.
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:phone|string',
            'phone' => 'required_without:login|string',
            'purpose' => 'nullable|string|in:register,reset_password',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $identifier = $request->phone ?? $request->login;
        $purpose = $request->purpose ?? 'reset_password';

        $record = VerificationCode::where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        return response()->json(['message' => 'Code verified successfully']);
    }

    /**
     * Step 3: Reset password using code (email or phone).
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required_without:phone|string',
            'phone' => 'required_without:login|string',
            'code' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $identifier = $request->phone ?? $request->login;

        $record = VerificationCode::where('identifier', $identifier)
            ->where('purpose', 'reset_password')
            ->where('code', $request->code)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        $user = User::where('email', $identifier)->orWhere('phone', $identifier)->first();
        $user->update(['password' => Hash::make($request->password)]);
        $user->tokens()->delete();

        $record->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }

    private function registerDeviceToken(User $user, Request $request): void
    {
        if (!$request->filled('device_token')) {
            return;
        }

        DeviceToken::updateOrCreate(
            ['user_id' => $user->id, 'token' => $request->device_token],
            ['platform' => $request->platform, 'is_active' => true]
        );
    }
}
