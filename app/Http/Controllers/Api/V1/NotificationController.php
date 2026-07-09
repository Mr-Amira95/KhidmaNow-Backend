<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\ApiResponse;
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
        $request->user()->update(['receive_notifications' => true]);
        return $this->success([], 'Notifications enabled.');
    }

    public function disable(Request $request)
    {
        $request->user()->update(['receive_notifications' => false]);
        return $this->success([], 'Notifications disabled.');
    }
}
