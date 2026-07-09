<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SendNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Traits\ApiResponse;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Notification::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('is_read')) {
            $query->where('is_read', filter_var($request->is_read, FILTER_VALIDATE_BOOLEAN));
        }

        return $this->paginated(NotificationResource::class, $query->latest());
    }

    public function send(SendNotificationRequest $request)
    {
        $payload = $request->only('title', 'body', 'icon', 'type', 'type_id');

        if ($request->filled('user_ids')) {
            $users = User::whereIn('id', $request->user_ids)->get();
        } else {
            $users = User::all();
        }

        $notifications = $users->map(fn(User $user) => array_merge($payload, [
            'user_id'    => $user->id,
            'is_read'    => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]))->toArray();

        Notification::insert($notifications);

        return $this->success([
            'sent_to' => $users->count(),
        ], 'Notification sent successfully.');
    }
}
