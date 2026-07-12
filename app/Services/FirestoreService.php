<?php

namespace App\Services;

use App\Models\ChatRoom;
use App\Models\Message;
use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Mirrors chat data into Firestore over its REST API so mobile clients can
 * listen for changes in real time, while MySQL remains the source of truth.
 * Uses REST rather than google/cloud-firestore because that package hard-requires
 * the PHP grpc extension, which isn't available on every deployment target.
 */
class FirestoreService
{
    public static function upsertChatRoom(ChatRoom $chatRoom): void
    {
        $chatRoom->loadMissing('provider');

        self::patch("chats/{$chatRoom->id}", [
            'customerId' => self::stringValue((string) $chatRoom->user_id),
            'providerUserId' => self::stringValue((string) $chatRoom->provider?->user_id),
            'lastMessageAt' => self::timestampValue($chatRoom->last_message_at),
        ]);
    }

    public static function writeMessage(Message $message): void
    {
        $fields = [
            'senderId' => self::stringValue((string) $message->sender_id),
            'message' => self::stringValue($message->message),
            'mediaType' => self::stringValue($message->media_type),
            'mediaUrl' => self::stringValue($message->media_url),
            'isRead' => self::boolValue((bool) $message->is_read),
            'createdAt' => self::timestampValue($message->created_at),
        ];

        if ($message->call_id) {
            $message->loadMissing('call');
            $fields['callId'] = self::stringValue((string) $message->call_id);
            $fields['callType'] = self::stringValue($message->call?->call_type);
            $fields['callDurationSeconds'] = self::stringValue((string) $message->call?->duration_seconds);
        }

        self::patch("chats/{$message->chat_id}/messages/{$message->id}", $fields);
    }

    public static function markMessagesRead(ChatRoom $chatRoom, array $messageIds): void
    {
        foreach ($messageIds as $messageId) {
            self::patch(
                "chats/{$chatRoom->id}/messages/{$messageId}",
                ['isRead' => self::boolValue(true)],
                ['isRead']
            );
        }
    }

    protected static function patch(string $path, array $fields, ?array $updateMask = null): void
    {
        $url = self::baseUrl() . '/' . $path;

        if ($updateMask) {
            $url .= '?' . collect($updateMask)
                ->map(fn ($field) => 'updateMask.fieldPaths=' . urlencode($field))
                ->implode('&');
        }

        $response = Http::withToken(self::accessToken())->patch($url, [
            'fields' => $fields,
        ]);

        if ($response->failed()) {
            Log::warning('Firestore mirror write failed', [
                'path' => $path,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }

    protected static function baseUrl(): string
    {
        return 'https://firestore.googleapis.com/v1/projects/' . self::projectId() . '/databases/(default)/documents';
    }

    protected static function projectId(): string
    {
        return self::credentialsPayload()['project_id'];
    }

    protected static function accessToken(): string
    {
        return Cache::remember('firestore_access_token', 3000, function () {
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/datastore'],
                self::credentialsPath()
            );

            return $credentials->fetchAuthToken()['access_token'];
        });
    }

    protected static function credentialsPath(): string
    {
        return config('firebase.projects.' . config('firebase.default') . '.credentials');
    }

    protected static function credentialsPayload(): array
    {
        return Cache::rememberForever('firestore_credentials_payload', function () {
            return json_decode(file_get_contents(self::credentialsPath()), true);
        });
    }

    protected static function stringValue(?string $value): array
    {
        return ['stringValue' => $value ?? ''];
    }

    protected static function boolValue(bool $value): array
    {
        return ['booleanValue' => $value];
    }

    protected static function timestampValue(?string $datetime): array
    {
        return ['timestampValue' => ($datetime ? Carbon::parse($datetime) : now())->toISOString()];
    }
}
