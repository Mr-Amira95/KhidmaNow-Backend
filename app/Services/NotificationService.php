<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

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

        // 2. Fetch this user's device tokens that have notifications enabled
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->where('receive_notifications', true)
            ->pluck('token');

        if ($tokens->isNotEmpty()) {
            self::sendPushNotification($tokens->toArray(), $title, $body, $type, $typeId);
        }

        return $notification;
    }

    /**
     * Dispatch a push notification to the given device tokens via Firebase Cloud Messaging.
     */
    protected static function sendPushNotification(array $tokens, string $title, string $body, string $type, ?int $typeId): void
    {
        $message = CloudMessage::new()
            ->withNotification(FirebaseNotification::create($title, $body))
            ->withData([
                'type'    => $type,
                'type_id' => (string) $typeId,
            ]);

        $report = app(Messaging::class)->sendMulticast($message, $tokens);

        Log::info('Push Notification Dispatched:', [
            'tokens_count' => count($tokens),
            'success'      => $report->successes()->count(),
            'failures'     => $report->failures()->count(),
            'title'        => $title,
            'type'         => $type,
            'type_id'      => $typeId,
        ]);

        foreach ($report->invalidTokens() as $invalidToken) {
            DeviceToken::where('token', $invalidToken)->update(['is_active' => false]);
        }
    }
}
