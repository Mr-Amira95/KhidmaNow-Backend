<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send a notification to a specific user (saves in DB + handles push notification).
     */
    public static function send(int $userId, string $title, string $body, string $type, ?int $typeId = null, ?string $icon = null): Notification
    {
        // 1. Create DB notification
        $notification = Notification::create([
            'user_id' => $userId,
            'title'   => $title,
            'body'    => $body,
            'type'    => $type,
            'type_id' => $typeId,
            'icon'    => $icon,
            'is_read' => false,
        ]);

        // 2. Fetch user's notification preferences and tokens
        $user = User::find($userId);
        if ($user && $user->receive_notifications) {
            $tokens = DeviceToken::where('user_id', $userId)
                ->where('is_active', true)
                ->pluck('token');

            if ($tokens->isNotEmpty()) {
                self::sendPushNotification($tokens->toArray(), $title, $body, $type, $typeId);
            }
        }

        return $notification;
    }

    /**
     * Mock function representing real push notification dispatching.
     */
    protected static function sendPushNotification(array $tokens, string $title, string $body, string $type, ?int $typeId): void
    {
        Log::info("Push Notification Dispatched:", [
            'tokens_count' => count($tokens),
            'title'        => $title,
            'body'         => $body,
            'type'         => $type,
            'type_id'      => $typeId,
        ]);
    }
}
