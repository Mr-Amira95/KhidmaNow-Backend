<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Http\Traits\ApiResponse;
use App\Http\Traits\HandlesUploads;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse, HandlesUploads;

    public function show(Request $request)
    {
        $user = $request->user()->load('provider');

        $receiveNotifications = null;
        if ($request->filled('fcm_token')) {
            $receiveNotifications = DeviceToken::where('user_id', $user->id)
                ->where('token', $request->fcm_token)
                ->value('receive_notifications');
        }
        $user->receive_notifications = $receiveNotifications;

        return $this->success(new UserResource($user));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('profile_image')) {
            $data['profile_image'] = $this->storeUpload($request->file('profile_image'), 'profile-images');
        }

        $user->update($data);

        return $this->success(new UserResource($user->load('provider')), 'Profile updated successfully.');
    }
}
