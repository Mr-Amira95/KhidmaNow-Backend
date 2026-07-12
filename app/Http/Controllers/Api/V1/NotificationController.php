<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\ApiResponse;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->paginated(NotificationResource::class, $request->user()->notifications()->latest());
    }

    public function enable(Request $request)
    {
        return $this->setReceiveNotifications($request, true, 'Notifications enabled.');
    }

    public function disable(Request $request)
    {
        return $this->setReceiveNotifications($request, false, 'Notifications disabled.');
    }

    private function setReceiveNotifications(Request $request, bool $value, string $message)
    {
        $request->validate(['fcm_token' => 'required|string']);

        $deviceToken = DeviceToken::where('user_id', $request->user()->id)
            ->where('token', $request->fcm_token)
            ->first();

        if (!$deviceToken) {
            return $this->error('Device token not found.', 404);
        }

        $deviceToken->update(['receive_notifications' => $value]);

        return $this->success([], $message);
    }
}
