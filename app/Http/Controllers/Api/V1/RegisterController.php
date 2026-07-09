<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesUploads;
use App\Models\DeviceToken;
use App\Models\Provider;
use App\Models\ProviderDocument;
use App\Models\ProviderSubCategory;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RegisterController extends Controller
{
    use HandlesUploads;

    /**
     * Register a client (customer) account.
     */
    public function client(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'code' => 'required|string',
            'device_name' => 'required|string',
            'device_token' => 'nullable|string',
            'platform' => 'required_with:device_token|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = $this->consumeRegistrationCode($request->phone, $request->code);
        if (!$verification) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'customer',
            'status' => 'active',
        ]);

        $verification->delete();
        $this->registerDeviceToken($user, $request);

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => ['user' => $user, 'token' => $token],
        ], 201);
    }

    /**
     * Register a service provider account (profile + subcategories + documents in one call).
     */
    public function provider(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'code' => 'required|string',
            'city_id' => 'required|integer|exists:cities,id',
            'business_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'experience_years' => 'nullable|integer|min:0',
            'sub_category_ids' => 'nullable|array',
            'sub_category_ids.*' => 'integer|exists:sub_categories,id',
            'documents' => 'nullable|array',
            'documents.*.type' => 'required_with:documents|string|in:id,license,commercial_register',
            'documents.*.file' => 'required_with:documents|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'device_name' => 'required|string',
            'device_token' => 'nullable|string',
            'platform' => 'required_with:device_token|in:ios,android',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $verification = $this->consumeRegistrationCode($request->phone, $request->code);
        if (!$verification) {
            return response()->json(['message' => 'Invalid or expired code'], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'user_type' => 'provider',
                'status' => 'active',
            ]);

            $provider = Provider::create([
                'user_id' => $user->id,
                'city_id' => $request->city_id,
                'business_name' => $request->business_name,
                'description' => $request->description,
                'experience_years' => $request->experience_years,
                'availability_status' => 'offline',
                'is_verified' => false,
            ]);

            foreach ($request->input('sub_category_ids', []) as $subCategoryId) {
                ProviderSubCategory::create([
                    'provider_id' => $provider->id,
                    'sub_category_id' => $subCategoryId,
                ]);
            }

            foreach ($request->input('documents', []) as $index => $document) {
                $file = $request->file("documents.{$index}.file");
                if (!$file) {
                    continue;
                }

                ProviderDocument::create([
                    'provider_id' => $provider->id,
                    'type' => $document['type'],
                    'document_url' => $this->storeUpload($file, 'provider-documents'),
                    'status' => 'pending',
                ]);
            }

            return $user;
        });

        $verification->delete();
        $this->registerDeviceToken($user, $request);
        $user->load('provider.documents', 'provider.subCategories');

        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'status' => 'success',
            'data' => ['user' => $user, 'token' => $token],
        ], 201);
    }

    private function consumeRegistrationCode(string $phone, string $code): ?VerificationCode
    {
        return VerificationCode::where('identifier', $phone)
            ->where('purpose', 'register')
            ->where('code', $code)
            ->where('expires_at', '>', Carbon::now())
            ->first();
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
