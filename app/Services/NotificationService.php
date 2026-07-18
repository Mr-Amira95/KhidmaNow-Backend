<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
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
     * Push (without creating DB rows) to every active, opted-in device token
     * belonging to the given users. FCM caps a multicast at 500 tokens, so
     * tokens are sent in chunks.
     */
    public static function sendBulkPush(array $userIds, string $title, string $body, string $type, ?int $typeId = null): void
    {
        $tokens = DeviceToken::whereIn('user_id', $userIds)
            ->where('is_active', true)
            ->where('receive_notifications', true)
            ->pluck('token');

        foreach ($tokens->chunk(500) as $chunk) {
            self::sendPushNotification($chunk->toArray(), $title, $body, $type, $typeId);
        }
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

        foreach ($report->failures()->getItems() as $failure) {
            Log::warning('Push Notification failure detail:', [
                'target' => $failure->target()->value(),
                'error'  => $failure->error()?->getMessage(),
            ]);
        }

        foreach ($report->invalidTokens() as $invalidToken) {
            DeviceToken::where('token', $invalidToken)->update(['is_active' => false]);
        }
    }

    /**
     * Push a data-only, high-priority call signal (no display "notification" block) so the
     * receiving app renders its own ringing / in-call UI instead of a plain system tray item.
     * Pass $title/$body to also persist a row in the notification history (e.g. for the
     * initial "incoming_call" event); transient lifecycle events (accepted/declined/ended)
     * should omit them.
     */
    public static function sendCallEvent(int $userId, string $event, array $data, ?string $title = null, ?string $body = null): void
    {
        $tokens = DeviceToken::where('user_id', $userId)
            ->where('is_active', true)
            ->where('receive_notifications', true)
            ->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        if ($title !== null) {
            Notification::create([
                'user_id' => $userId,
                'title'   => $title,
                'body'    => $body ?? '',
                'type'    => 'call',
                'type_id' => $data['call_id'] ?? null,
                'is_read' => false,
            ]);
        }

        self::sendCallDataPush($tokens->toArray(), array_merge(['event' => $event], $data));
    }

    /**
     * Dispatch a data-only FCM message. Android/iOS are configured for high-priority,
     * background-waking delivery since these events must land even while the app is killed.
     */
    protected static function sendCallDataPush(array $tokens, array $data): void
    {
        $message = CloudMessage::new()
            ->withData(array_map('strval', $data))
            ->withAndroidConfig(AndroidConfig::fromArray([
                'priority' => 'high',
            ]))
            ->withApnsConfig(ApnsConfig::fromArray([
                'headers' => ['apns-priority' => '10'],
                'payload' => ['aps' => ['content-available' => 1]],
            ]));

        $report = app(Messaging::class)->sendMulticast($message, $tokens);

        Log::info('Call event push dispatched:', [
            'tokens_count' => count($tokens),
            'success'      => $report->successes()->count(),
            'failures'     => $report->failures()->count(),
            'event'        => $data['event'] ?? null,
        ]);

        foreach ($report->failures()->getItems() as $failure) {
            Log::warning('Call event push failure detail:', [
                'target' => $failure->target()->value(),
                'error'  => $failure->error()?->getMessage(),
            ]);
        }

        foreach ($report->invalidTokens() as $invalidToken) {
            DeviceToken::where('token', $invalidToken)->update(['is_active' => false]);
        }
    }
}
